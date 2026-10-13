<?php

namespace App\Services;

use App\Events\NovaListaNoGrupo;
use App\Events\ParticipanteAdicionadoALista;
use App\Exceptions\RegraDeNegocioException;
use App\Models\Grupo;
use App\Models\Lista;
use App\Models\ListaParticipante;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListaService
{
    public const CAPA_PADRAO = '/img/default_cover.png';

    public function __construct(private FileUploadService $uploader) {}

    public function criar(User $user, array $dados): Lista
    {
        $this->exigirMembroDoGrupo($user, $dados['grupo_id'] ?? null);

        $dados['image_url'] = ($dados['image_url'] ?? null) instanceof UploadedFile
            ? $this->uploader->upload($dados['image_url'], 'listas', $this->uploader->extensoesImagem)
            : self::CAPA_PADRAO;
        $dados['cadastrado_por_id'] = $user->id;

        $lista = DB::transaction(function () use ($dados, $user) {
            $lista = Lista::create($dados);
            $lista->adicionarParticipante($user->id, 'admin');

            return $lista;
        });

        if ($lista->grupo_id) {
            event(new NovaListaNoGrupo($lista, Grupo::find($lista->grupo_id), $user));
        }

        return $lista;
    }

    public function atualizar(Lista $lista, User $user, array $dados): Lista
    {
        $this->exigirMembroDoGrupo($user, $dados['grupo_id'] ?? null);

        if (($dados['image_url'] ?? null) instanceof UploadedFile) {
            $dados['image_url'] = $this->uploader->upload($dados['image_url'], 'listas', $this->uploader->extensoesImagem);
        } elseif (array_key_exists('image_url', $dados) && ! $dados['image_url']) {
            $dados['image_url'] = self::CAPA_PADRAO;
        } else {
            unset($dados['image_url']);
        }

        unset($dados['cadastrado_por_id'], $dados['token_convite']);
        $lista->update($dados);

        return $lista;
    }

    public function excluir(Lista $lista): void
    {
        DB::transaction(function () use ($lista) {
            $lista->presentes()->each(fn ($p) => $p->categorias()->detach());
            $lista->presentes()->delete();
            $lista->participantes()->delete();
            $lista->delete();
        });
    }

    public function adicionarParticipante(Lista $lista, string $usuarioId, string $papel, ?User $por = null): void
    {
        if ($lista->participantes()->where('usuario_id', $usuarioId)->exists()) {
            throw new RegraDeNegocioException('Usuário já é participante desta lista.');
        }

        $lista->adicionarParticipante($usuarioId, $papel);

        if ($por) {
            ParticipanteAdicionadoALista::dispatch($lista, User::findOrFail($usuarioId), $por);
        }
    }

    public function removerParticipante(Lista $lista, string $participanteId): void
    {
        $participante = $lista->participantes()->findOrFail($participanteId);

        if ($participante->papel === 'admin' && $lista->participantes()->where('papel', 'admin')->count() === 1) {
            throw new RegraDeNegocioException('Não é possível remover o último administrador.');
        }

        $participante->delete();
    }

    public function alterarPapel(Lista $lista, string $participanteId, string $papel): ListaParticipante
    {
        $participante = $lista->participantes()->findOrFail($participanteId);

        if ($participante->papel === 'admin' && $papel !== 'admin'
            && $lista->participantes()->where('papel', 'admin')->where('id', '!=', $participanteId)->doesntExist()) {
            throw new RegraDeNegocioException('A lista deve ter pelo menos um administrador.');
        }

        $participante->update(['papel' => $papel]);

        return $participante;
    }

    /** @return array{token:string} */
    public function gerarConvite(Lista $lista): array
    {
        $token = Str::random(48);
        $lista->update(['token_convite' => $token, 'token_expira_em' => now()->addDays(30)]);

        return ['token' => $token];
    }

    /** Quem entra por convite sempre entra como visualizador. */
    public function entrarPorConvite(Lista $lista, User $user, string $token): void
    {
        $valido = $lista->token_convite
            && hash_equals($lista->token_convite, $token)
            && (! $lista->token_expira_em || $lista->token_expira_em->isFuture());

        if (! $valido) {
            throw new RegraDeNegocioException('Link de convite inválido ou expirado.', 403);
        }

        $lista->adicionarParticipante($user->id, 'visualizador');
    }

    private function exigirMembroDoGrupo(User $user, ?string $grupoId): void
    {
        if ($grupoId && ! $user->grupos()->where('grupos.id', $grupoId)->exists()) {
            throw new RegraDeNegocioException('Você não participa deste grupo.', 403);
        }
    }
}
