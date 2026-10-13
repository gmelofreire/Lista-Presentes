<?php

namespace App\Services;

use App\Events\MembroAdicionadoAoGrupo;
use App\Exceptions\RegraDeNegocioException;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrupoService
{
    public const CAPA_PADRAO = '/img/default_cover.png';

    public function __construct(private FileUploadService $uploader) {}

    public function criar(User $dono, array $dados): Grupo
    {
        $integrantes = $dados['integrante_ids'] ?? [];
        unset($dados['integrante_ids']);

        $dados['image_url'] = ($dados['image_url'] ?? null) instanceof UploadedFile
            ? $this->uploadImagem($dados['image_url'])
            : self::CAPA_PADRAO;
        $this->processarBanner($dados);
        $dados['cadastrado_por'] = $dono->id;

        return DB::transaction(function () use ($dados, $dono, $integrantes) {
            $grupo = Grupo::create($dados);
            $grupo->integrantes()->attach($dono->id, ['role' => 'proprietario']);

            $amigos = $dono->amigoIds();
            foreach (array_unique($integrantes) as $id) {
                if ($id !== $dono->id && $amigos->contains($id)) {
                    $grupo->integrantes()->attach($id, ['role' => 'visualizador']);
                }
            }

            return $grupo;
        });
    }

    /** Preserva papéis existentes; só amigos novos entram (visualizador). O proprietário nunca sai. */
    public function atualizar(Grupo $grupo, User $ator, array $dados): Grupo
    {
        $integrantes = $dados['integrante_ids'] ?? [];
        unset($dados['integrante_ids']);

        if (($dados['image_url'] ?? null) instanceof UploadedFile) {
            $dados['image_url'] = $this->uploadImagem($dados['image_url']);
        } elseif (empty($dados['image_url'])) {
            $dados['image_url'] = self::CAPA_PADRAO;
        }
        $this->processarBanner($dados);
        unset($dados['cadastrado_por'], $dados['token_convite']);

        return DB::transaction(function () use ($grupo, $ator, $dados, $integrantes) {
            $grupo->update($dados);

            $amigos = $ator->amigoIds();
            $atuais = $grupo->integrantes()->pluck('user_id');
            $manter = collect($integrantes)
                ->filter(fn ($id) => $atuais->contains($id) || $amigos->contains($id))
                ->push($grupo->cadastrado_por)
                ->unique();

            $sync = [];
            foreach ($manter as $id) {
                if ($id === $grupo->cadastrado_por) {
                    $sync[$id] = ['role' => 'proprietario'];
                } elseif (! $atuais->contains($id)) {
                    $sync[$id] = ['role' => 'visualizador'];
                } else {
                    $sync[$id] = [];
                }
            }
            $grupo->integrantes()->sync($sync);

            return $grupo;
        });
    }

    public function excluir(Grupo $grupo): void
    {
        DB::transaction(function () use ($grupo) {
            $grupo->integrantes()->detach();
            $grupo->listas()->update(['grupo_id' => null]);
            $grupo->delete();
        });
    }

    public function sair(Grupo $grupo, User $user): void
    {
        if (! $grupo->integrantes()->where('user_id', $user->id)->exists()) {
            throw new RegraDeNegocioException('Você não é membro deste grupo', 400);
        }

        if ($grupo->cadastrado_por === $user->id) {
            throw new RegraDeNegocioException('O proprietário não pode sair do grupo. Exclua o grupo ou transfira a propriedade.', 400);
        }

        $grupo->integrantes()->detach($user->id);
    }

    public function removerMembro(Grupo $grupo, User $ator, string $membroId): void
    {
        if ($grupo->cadastrado_por === $membroId) {
            throw new RegraDeNegocioException('Não é possível remover o proprietário', 400);
        }

        if ($membroId === $ator->id) {
            throw new RegraDeNegocioException('Use a opção Sair do Grupo para si mesmo', 400);
        }

        $alvo = $grupo->integrantes()->where('user_id', $membroId)->first();
        if (! $alvo) {
            throw new RegraDeNegocioException('Membro não encontrado', 404);
        }

        // Admin não remove outro admin; só o proprietário.
        if ($alvo->pivot->role === 'admin' && $grupo->cadastrado_por !== $ator->id) {
            throw new RegraDeNegocioException('Apenas o proprietário pode remover um administrador', 403);
        }

        $grupo->integrantes()->detach($membroId);
    }

    public function promoverAdmin(Grupo $grupo, string $membroId): void
    {
        if (! $grupo->integrantes()->where('user_id', $membroId)->exists()) {
            throw new RegraDeNegocioException('Membro não encontrado', 404);
        }

        if ($grupo->cadastrado_por === $membroId) {
            throw new RegraDeNegocioException('O proprietário já tem todas as permissões', 400);
        }

        $grupo->integrantes()->updateExistingPivot($membroId, ['role' => 'admin']);
    }

    /** @return array{adicionados:int,ja_membros:int} */
    public function convidarAmigos(Grupo $grupo, User $ator, array $amigoIds): array
    {
        $amigos = $ator->amigoIds();
        $membros = $grupo->integrantes()->pluck('user_id');
        $adicionados = 0;
        $jaMembros = 0;

        foreach (array_unique($amigoIds) as $id) {
            if (! $amigos->contains($id)) {
                continue;
            }
            if ($membros->contains($id)) {
                $jaMembros++;

                continue;
            }

            $grupo->integrantes()->attach($id, ['role' => 'visualizador']);
            MembroAdicionadoAoGrupo::dispatch($grupo, User::find($id), $ator);
            $adicionados++;
        }

        return ['adicionados' => $adicionados, 'ja_membros' => $jaMembros];
    }

    public function gerarConvite(Grupo $grupo): string
    {
        if (! $grupo->token_convite || ($grupo->token_expira_em && $grupo->token_expira_em->isPast())) {
            $grupo->update(['token_convite' => Str::random(64), 'token_expira_em' => now()->addDays(30)]);
            $grupo->refresh();
        }

        return $grupo->token_convite;
    }

    /** @return array{0:Grupo,1:'ja_membro'|'pendente'|'entrou'} */
    public function entrarPorConvite(string $token, User $user): array
    {
        $grupo = Grupo::where('token_convite', $token)->first();

        if (! $grupo || ($grupo->token_expira_em && $grupo->token_expira_em->isPast())) {
            throw new RegraDeNegocioException('Link de convite inválido ou expirado', 404);
        }

        if ($grupo->integrantes()->where('user_id', $user->id)->exists()) {
            return [$grupo, 'ja_membro'];
        }

        if ($grupo->visibilidade === 'privada') {
            return [$grupo, 'pendente'];
        }

        $grupo->integrantes()->attach($user->id, ['role' => 'visualizador']);
        MembroAdicionadoAoGrupo::dispatch($grupo, $user);

        return [$grupo, 'entrou'];
    }

    private function processarBanner(array &$dados): void
    {
        if (($dados['banner_url'] ?? null) instanceof UploadedFile) {
            $dados['banner_url'] = $this->uploadImagem($dados['banner_url']);
        } else {
            unset($dados['banner_url']);
        }
    }

    private function uploadImagem(UploadedFile $arquivo): string
    {
        return $this->uploader->upload($arquivo, 'grupos', $this->uploader->extensoesImagem);
    }
}
