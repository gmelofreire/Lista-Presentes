<?php

namespace App\Services;

use App\Exceptions\RegraDeNegocioException;
use App\Models\Presente;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class PresenteService
{
    public function __construct(private FileUploadService $uploader) {}

    public function criar(User $user, array $dados): Presente
    {
        $dados = $this->normalizar($dados);
        $dados['cadastrado_por'] = $user->id;

        $presente = Presente::create($dados);
        $this->sincronizarCategorias($presente, $dados);

        return $presente;
    }

    public function atualizar(Presente $presente, array $dados): Presente
    {
        $dados = $this->normalizar($dados);
        unset($dados['lista_id'], $dados['cadastrado_por']);

        $presente->update($dados);
        $this->sincronizarCategorias($presente, $dados);

        return $presente;
    }

    /** UPDATE condicional: com duas pessoas ao mesmo tempo, só uma ganha. */
    public function reservar(Presente $presente, User $user): void
    {
        $ganhou = Presente::where('id', $presente->id)->whereNull('reservado_por')->update([
            'reservado_por' => $user->id,
            'reservado_em' => now(),
            'comprado' => true,
        ]);

        if (! $ganhou) {
            throw new RegraDeNegocioException('Este presente já foi reservado por outra pessoa.', 409);
        }
    }

    public function liberar(Presente $presente, User $user): void
    {
        $liberou = Presente::where('id', $presente->id)->where('reservado_por', $user->id)->update([
            'reservado_por' => null,
            'reservado_em' => null,
            'comprado' => false,
        ]);

        if (! $liberou) {
            throw new RegraDeNegocioException('Você não reservou este presente.', 409);
        }
    }

    private function normalizar(array $dados): array
    {
        foreach (['descricao', 'preco', 'link', 'image_url', 'anotacoes'] as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] === '') {
                $dados[$campo] = null;
            }
        }

        if (isset($dados['image_url'])) {
            if ($dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $this->uploader->upload($dados['image_url'], 'presente', $this->uploader->extensoesImagem);
            } elseif (! (is_string($dados['image_url']) && preg_match('#^https?://#i', $dados['image_url']) && filter_var($dados['image_url'], FILTER_VALIDATE_URL))) {
                unset($dados['image_url']);
            }
        }

        return $dados;
    }

    private function sincronizarCategorias(Presente $presente, array $dados): void
    {
        if (isset($dados['categoria_ids'])) {
            $presente->categorias()->sync($dados['categoria_ids']);
        }
    }
}
