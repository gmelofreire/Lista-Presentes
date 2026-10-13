<?php

namespace App\Services;

use App\Events\AmizadeAceita;
use App\Events\AmizadeSolicitada;
use App\Exceptions\RegraDeNegocioException;
use App\Models\Amizade;
use App\Models\User;

class AmizadeService
{
    public function solicitar(User $de, User $para): Amizade
    {
        if ($de->id === $para->id) {
            throw new RegraDeNegocioException('Você não pode adicionar a si mesmo.');
        }

        if (Amizade::entre($para->id, $de->id)->exists()) {
            throw new RegraDeNegocioException('Já existe uma solicitação ou amizade com este usuário.', 409);
        }

        $amizade = Amizade::create(['usuario_id' => $de->id, 'amigo_id' => $para->id, 'status' => 'pendente']);

        AmizadeSolicitada::dispatch($de, $para);

        return $amizade;
    }

    public function aceitar(User $aceitante, string $solicitanteId): void
    {
        $atualizadas = Amizade::where('usuario_id', $solicitanteId)
            ->where('amigo_id', $aceitante->id)
            ->where('status', 'pendente')
            ->update(['status' => 'aceito']);

        if (! $atualizadas) {
            throw new RegraDeNegocioException('Solicitação não encontrada', 404);
        }

        AmizadeAceita::dispatch($aceitante, $solicitanteId);
    }

    public function recusar(User $destinatario, string $solicitanteId): void
    {
        $this->removerPendente($solicitanteId, $destinatario->id);
    }

    public function cancelar(User $solicitante, string $destinoId): void
    {
        $this->removerPendente($solicitante->id, $destinoId);
    }

    public function desfazer(User $user, string $outroId): void
    {
        if (! Amizade::entre($outroId, $user->id)->exists()) {
            throw new RegraDeNegocioException('Amizade não encontrada', 404);
        }

        Amizade::entre($outroId, $user->id)->delete();
    }

    /** Bloqueio substitui qualquer relação existente; quem bloqueia fica como solicitante. */
    public function bloquear(User $user, string $alvoId): void
    {
        if ($alvoId === $user->id || ! User::whereKey($alvoId)->exists()) {
            throw new RegraDeNegocioException('Usuário inválido');
        }

        Amizade::entre($alvoId, $user->id)->delete();
        Amizade::create(['usuario_id' => $user->id, 'amigo_id' => $alvoId, 'status' => 'bloqueado']);
    }

    public function desbloquear(User $user, string $alvoId): void
    {
        $bloqueio = Amizade::entre($alvoId, $user->id)->first();

        if (! $bloqueio || $bloqueio->status !== 'bloqueado' || $bloqueio->usuario_id !== $user->id) {
            throw new RegraDeNegocioException('Usuário não está bloqueado por você', 404);
        }

        Amizade::entre($alvoId, $user->id)->delete();
    }

    private function removerPendente(string $solicitanteId, string $destinatarioId): void
    {
        $removidas = Amizade::where('usuario_id', $solicitanteId)
            ->where('amigo_id', $destinatarioId)
            ->where('status', 'pendente')
            ->delete();

        if (! $removidas) {
            throw new RegraDeNegocioException('Solicitação não encontrada', 404);
        }
    }
}
