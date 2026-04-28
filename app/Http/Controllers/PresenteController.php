<?php

namespace App\Http\Controllers;

use App\Http\Requests\PresenteUpdateValidator;
use App\Http\Requests\PresenteValidator;
use App\Models\Categoria;
use App\Models\Presente;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresenteController extends Controller
{
    protected $title = 'Presentes';

    public function create($lista_id)
    {
        $categorias = Categoria::where('cadastrado_por', auth()->user()->id)->get();

        return Inertia::render('Presente/Create', [
            'title' => $this->title,
            'categorias' => $categorias,
            'lista_id' => $lista_id,
        ]);
    }

    public function store(PresenteValidator $request, FileUploadService $uploader)
    {
        $dados = $request->validated();

        // Convert empty strings to null for nullable fields
        $nullableFields = ['descricao', 'preco', 'link', 'image_url', 'anotacoes'];
        foreach ($nullableFields as $field) {
            if (isset($dados[$field]) && $dados[$field] === '') {
                $dados[$field] = null;
            }
        }

        if (isset($dados['image_url'])) {
            if ($dados['image_url'] instanceof \Illuminate\Http\UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'presente', $uploader->extensoesImagem);
            } elseif (is_string($dados['image_url']) && filter_var($dados['image_url'], FILTER_VALIDATE_URL)) {
                // Keep the URL as-is (from URL scraping)
            } else {
                // Otherwise, remove the field to keep existing value
                unset($dados['image_url']);
            }
        }
        $presente = Presente::create($dados);

        if (isset($dados['categoria_ids'])) {
            $presente->categorias()->sync($dados['categoria_ids']);
        }

        return redirect()->route('listas.show', $dados['lista_id']);
    }

    public function edit($id)
    {
        $presente = Presente::with('categorias')->find($id);
        $categorias = Categoria::where('cadastrado_por', auth()->user()->id)->get();
        $presente->categoria_ids = $presente->categorias->pluck('id')->toArray();

        return Inertia::render('Presente/Edit', [
            'title' => $this->title,
            'presente' => $presente,
            'categorias' => $categorias,
        ]);
    }

    public function update(PresenteUpdateValidator $request, FileUploadService $uploader, $id)
    {
        $dados = $request->validated();

        // Convert empty strings to null for nullable fields
        $nullableFields = ['descricao', 'preco', 'link', 'image_url', 'anotacoes'];
        foreach ($nullableFields as $field) {
            if (isset($dados[$field]) && $dados[$field] === '') {
                $dados[$field] = null;
            }
        }

        if (isset($dados['image_url'])) {
            if ($dados['image_url'] instanceof \Illuminate\Http\UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'presente', $uploader->extensoesImagem);
            } elseif (is_string($dados['image_url']) && filter_var($dados['image_url'], FILTER_VALIDATE_URL)) {
                // Keep the URL as-is (from URL scraping)
            } else {
                unset($dados['image_url']);
            }
        }
        $presente = Presente::find($id);
        $presente->update($dados);

        if (isset($dados['categoria_ids'])) {
            $presente->categorias()->sync($dados['categoria_ids']);
        }

        return redirect()->route('listas.show', $presente->lista_id);
    }

    public function destroy($id)
    {
        $presente = Presente::find($id);
        $lista_id = $presente->lista_id;
        $presente->delete();

        return redirect()->route('listas.show', $lista_id);
    }

    public function buscarDadosUrl(Request $request)
    {
        $url = $request->get('url');

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'URL inválida'], 400);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                ],
            ]);

            $response = $client->get($url);
            $html = (string) $response->getBody();

            $result = [
                'nome' => '',
                'descricao' => '',
                'preco' => '',
                'imagem' => '',
            ];

            libxml_use_internal_errors(true);
            $doc = new \DOMDocument;
            $doc->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

            $xpath = new \DOMXPath($doc);

            $titleSelectors = [
                '//meta[@property="og:title"]/@content',
                '//meta[@name="title"]/@content',
                '//title',
                '//h1[@class="product-title"]',
                '//h1[@class="product-name"]',
                '//h1[contains(@class, "title")]',
                '//div[contains(@class, "product-name")]/h1',
            ];
            foreach ($titleSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $result['nome'] = trim($nodes->item(0)->nodeValue);
                    break;
                }
            }

            $descSelectors = [
                '//meta[@property="og:description"]/@content',
                '//meta[@name="description"]/@content',
                '//div[contains(@class, "product-description")]',
                '//div[contains(@class, "description")]',
            ];
            foreach ($descSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $result['descricao'] = trim($nodes->item(0)->nodeValue);
                    break;
                }
            }

            $priceSelectors = [
                '//meta[@property="product:price:amount"]/@content',
                '//meta[@itemprop="price"]/@content',
                '//span[@itemprop="price"]',
                '//span[contains(@class, "price")]',
                '//div[contains(@class, "price")]',
                '//span[contains(@class, "product-price")]',
                // Amazon
                '//span[@id="priceblock_ourprice"]',
                '//span[@id="priceblock_dealprice"]',
                '//span[@class="a-price-whole"]',
                '//div[@id="apex_desktop_ingressprice"]',
                // AliExpress
                '//span[contains(@class, "price-default--current")]',
                '//span[contains(@class, "price-value")]',
                '//div[contains(@class, "price-sale")]',
                '//div[contains(@class, "price-default--defaultPriceWrap")]',
                // Magazine Luiza
                '//p[contains(@data-testid, "price-value")]',
                // Generic
                '//span[contains(@data-testid, "price-value-integer")]',
            ];
            foreach ($priceSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $priceText = $nodes->item(0)->nodeValue;
                    // Remove currency symbols and spaces
                    $priceText = preg_replace('/[^0-9,.]/', '', str_replace(',', '.', $priceText));
                    $priceText = trim($priceText, '.');
                    if (is_numeric($priceText) && (float) $priceText > 0) {
                        $result['preco'] = number_format((float) $priceText, 2, '.', '');
                        break;
                    }
                }
            }

            $imageSelectors = [
                '//meta[@property="og:image"]/@content',
                '//meta[@itemprop="image"]/@content',
                '//img[@itemprop="image"]/@src',
                '//img[contains(@class, "product-image")]/@src',
                '//img[contains(@class, "gallery")]/@src',
                '//div[contains(@class, "product-image")]/img/@src',
            ];
            foreach ($imageSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $result['imagem'] = $nodes->item(0)->nodeValue;
                    break;
                }
            }

            if (empty($result['nome'])) {
                return response()->json(['error' => 'Não foi possível extrair informações do produto'], 422);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar dados: '.$e->getMessage()], 500);
        }
    }
}
