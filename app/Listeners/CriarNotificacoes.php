<?php

namespace App\Listeners;

use App\Events\AmizadeAceita;
use App\Events\AmizadeSolicitada;
use App\Events\MembroAdicionadoAoGrupo;
use App\Events\NovaListaNoGrupo;
use App\Events\ParticipanteAdicionadoALista;
use App\Services\NotificacaoService;
use Illuminate\Events\Dispatcher;

/**
 * Traduz fatos de domínio em notificações dentro do app. Roda síncrono de propósito:
 * é uma escrita barata e a interface precisa vê-la na hora, sem depender de worker.
 * O envio externo (push/e-mail) é enfileirado por outros listeners.
 */
class CriarNotificacoes
{
    public function __construct(private NotificacaoService $notificacoes) {}

    public function amizadeSolicitada(AmizadeSolicitada $e): void
    {
        $this->notificacoes->notificar($e->para->id, 'solicitacao_amizade', 'Nova solicitação de amizade',
            $e->de->name.' enviou uma solicitação de amizade', route('amizades.index'));
    }

    public function amizadeAceita(AmizadeAceita $e): void
    {
        $this->notificacoes->notificar($e->solicitanteId, 'amizade_aceita', 'Amizade aceita',
            $e->aceitante->name.' aceitou sua solicitação de amizade', route('amizades.show', $e->aceitante->id));
    }

    public function membroAdicionado(MembroAdicionadoAoGrupo $e): void
    {
        if ($e->por) {
            $this->notificacoes->notificar($e->membro->id, 'grupo_convite', 'Você entrou em um grupo',
                $e->por->name.' adicionou você ao grupo '.$e->grupo->nome, route('grupos.show', $e->grupo->id));

            return;
        }

        if ($e->grupo->cadastrado_por !== $e->membro->id) {
            $this->notificacoes->notificar($e->grupo->cadastrado_por, 'grupo_novo_membro', 'Novo membro no grupo',
                $e->membro->name.' entrou no grupo '.$e->grupo->nome, route('grupos.show', $e->grupo->id));
        }
    }

    public function participanteAdicionado(ParticipanteAdicionadoALista $e): void
    {
        if ($e->participante->id === $e->por->id) {
            return;
        }

        $this->notificacoes->notificar($e->participante->id, 'lista_convite', 'Você foi adicionado a uma lista',
            $e->por->name.' adicionou você à lista '.$e->lista->nome, route('listas.show', $e->lista->id));
    }

    public function novaListaNoGrupo(NovaListaNoGrupo $e): void
    {
        $e->grupo->integrantes()->where('user_id', '!=', $e->criador->id)->get()->each(
            fn ($membro) => $this->notificacoes->notificar($membro->id, 'nova_lista_grupo', 'Nova lista no grupo',
                $e->criador->name.' criou a lista '.$e->lista->nome.' em '.$e->grupo->nome, route('listas.show', $e->lista->id))
        );
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            AmizadeSolicitada::class => 'amizadeSolicitada',
            AmizadeAceita::class => 'amizadeAceita',
            MembroAdicionadoAoGrupo::class => 'membroAdicionado',
            ParticipanteAdicionadoALista::class => 'participanteAdicionado',
            NovaListaNoGrupo::class => 'novaListaNoGrupo',
        ];
    }
}
