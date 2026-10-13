<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

/**
 * Lê metadados de uma página de produto. Bloqueia destinos internos (SSRF)
 * validando o IP resolvido e fixando-o na conexão, inclusive em redirects.
 */
class ProdutoUrlScraper
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    private const MAX_REDIRECTS = 3;

    public function buscar(string $url): ?array
    {
        $html = $this->baixar($url);

        $result = ['nome' => '', 'descricao' => '', 'preco' => '', 'imagem' => ''];

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

        return $result['nome'] === '' ? null : $result;
    }

    /** @throws InvalidArgumentException quando a URL não é pública/permitida */
    public function baixar(string $url): string
    {
        for ($i = 0; $i <= self::MAX_REDIRECTS; $i++) {
            [$host, $ip] = $this->validar($url);
            $porta = parse_url($url, PHP_URL_PORT);

            $client = new Client([
                RequestOptions::TIMEOUT => 10,
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::STREAM => true,
                RequestOptions::CURL => [
                    CURLOPT_RESOLVE => [$host.':'.($porta ?: (parse_url($url, PHP_URL_SCHEME) === 'https' ? 443 : 80)).':'.$ip],
                ],
                RequestOptions::HEADERS => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                ],
            ]);

            $response = $client->get($url);

            if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400 && $response->hasHeader('Location')) {
                $url = (string) \GuzzleHttp\Psr7\UriResolver::resolve(
                    new \GuzzleHttp\Psr7\Uri($url),
                    new \GuzzleHttp\Psr7\Uri($response->getHeaderLine('Location'))
                );

                continue;
            }

            return $response->getBody()->read(self::MAX_BYTES);
        }

        throw new InvalidArgumentException('Redirecionamentos demais.');
    }

    /** @return array{0:string,1:string} host e IP público resolvido */
    private function validar(string $url): array
    {
        $partes = parse_url($url);
        $scheme = strtolower($partes['scheme'] ?? '');
        $host = $partes['host'] ?? '';

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($partes['user']) || isset($partes['pass'])) {
            throw new InvalidArgumentException('URL inválida.');
        }

        $porta = $partes['port'] ?? null;
        if ($porta !== null && ! in_array($porta, [80, 443], true)) {
            throw new InvalidArgumentException('Porta não permitida.');
        }

        $host = trim($host, '[]');
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : array_column(dns_get_record($host, DNS_A) ?: [], 'ip');

        if (! $ips) {
            throw new InvalidArgumentException('Host não resolvido.');
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new InvalidArgumentException('Destino não permitido.');
            }
        }

        return [$host, $ips[0]];
    }
}
