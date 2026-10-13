<?php

namespace Tests\Feature;

use App\Events\AmizadeAceita;
use App\Events\AmizadeSolicitada;
use App\Events\NotificacaoCriada;
use App\Listeners\EnviarPushDaNotificacao;
use App\Mail\NovaListaGrupoMailable;
use App\Models\Dispositivo;
use App\Models\Notificacao;
use App\Models\User;
use App\Push\PushSender;
use App\Services\AmizadeService;
use App\Services\GrupoService;
use App\Services\ListaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Seguranca\CriaDominio;
use Tests\TestCase;

class EventosDeDominioTest extends TestCase
{
    use CriaDominio, RefreshDatabase;

    public function test_servico_emite_fatos_de_dominio(): void
    {
        Event::fake([AmizadeSolicitada::class, AmizadeAceita::class]);
        $a = User::factory()->create();
        $b = User::factory()->create();

        app(AmizadeService::class)->solicitar($a, $b);
        app(AmizadeService::class)->aceitar($b, $a->id);

        Event::assertDispatched(AmizadeSolicitada::class, fn ($e) => $e->de->is($a) && $e->para->is($b));
        Event::assertDispatched(AmizadeAceita::class, fn ($e) => $e->solicitanteId === $a->id);
    }

    public function test_eventos_geram_notificacoes_no_app(): void
    {
        $dono = User::factory()->create();
        $amigo = User::factory()->create();
        $this->amigos($dono, $amigo);
        $grupo = $this->grupo($dono);

        app(GrupoService::class)->convidarAmigos($grupo, $dono, [$amigo->id]);
        $this->assertDatabaseHas('notificacoes', ['user_id' => $amigo->id, 'tipo' => 'grupo_convite']);

        $lista = $this->lista($dono);
        app(ListaService::class)->adicionarParticipante($lista, $amigo->id, 'editor', $dono);
        $this->assertDatabaseHas('notificacoes', ['user_id' => $amigo->id, 'tipo' => 'lista_convite']);

        // entrar por link avisa o proprietário
        $novo = User::factory()->create();
        $token = app(GrupoService::class)->gerarConvite($grupo);
        app(GrupoService::class)->entrarPorConvite($token, $novo);
        $this->assertDatabaseHas('notificacoes', ['user_id' => $dono->id, 'tipo' => 'grupo_novo_membro']);
    }

    public function test_nova_lista_no_grupo_notifica_e_enfileira_emails_so_dos_outros_membros(): void
    {
        Mail::fake();
        $dono = User::factory()->create();
        $m1 = User::factory()->create();
        $grupo = $this->grupo($dono);
        $grupo->integrantes()->attach($m1->id, ['role' => 'visualizador']);

        app(ListaService::class)->criar($dono, ['nome' => 'Churrasco', 'visibilidade' => 'privada', 'grupo_id' => $grupo->id]);

        $this->assertDatabaseHas('notificacoes', ['user_id' => $m1->id, 'tipo' => 'nova_lista_grupo']);
        $this->assertDatabaseMissing('notificacoes', ['user_id' => $dono->id, 'tipo' => 'nova_lista_grupo']);
        Mail::assertQueued(NovaListaGrupoMailable::class, 1);
        Mail::assertQueued(NovaListaGrupoMailable::class, fn ($m) => $m->hasTo($m1->email));
    }

    public function test_push_e_idempotente_e_remove_tokens_invalidos(): void
    {
        $user = User::factory()->create();
        $bom = Dispositivo::create(['user_id' => $user->id, 'token' => 'bom', 'plataforma' => 'ios']);
        Dispositivo::create(['user_id' => $user->id, 'token' => 'morto', 'plataforma' => 'android']);
        $n = Notificacao::create(['user_id' => $user->id, 'tipo' => 't', 'titulo' => 'Oi', 'mensagem' => 'm']);

        $envios = 0;
        $sender = new class($envios) implements PushSender
        {
            public function __construct(public int &$envios) {}

            public function enviar(Collection $d, string $t, string $m, array $x = []): array
            {
                $this->envios++;

                return ['morto'];
            }
        };

        $listener = new EnviarPushDaNotificacao($sender);
        $listener->handle(new NotificacaoCriada($n));
        $listener->handle(new NotificacaoCriada($n)); // retry/duplicado

        $this->assertSame(1, $envios);
        $this->assertNotNull($n->fresh()->push_enviado_em);
        $this->assertSame(['bom'], $user->dispositivos()->pluck('token')->all());
    }

    public function test_notificacao_criada_aciona_o_listener_de_push(): void
    {
        $user = User::factory()->create();
        Dispositivo::create(['user_id' => $user->id, 'token' => 't1', 'plataforma' => 'ios']);
        $n = Notificacao::create(['user_id' => $user->id, 'tipo' => 't', 'titulo' => 'Oi', 'mensagem' => 'm']);

        NotificacaoCriada::dispatch($n); // fila "sync" no phpunit.xml
        $this->assertNotNull($n->fresh()->push_enviado_em);
    }

    public function test_api_de_dispositivos(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        Sanctum::actingAs($a);
        $this->postJson('/api/v1/dispositivos', ['token' => 'abc', 'plataforma' => 'ios'])->assertCreated();
        $this->postJson('/api/v1/dispositivos', ['token' => 'abc', 'plataforma' => 'xbox'])->assertStatus(422);

        Sanctum::actingAs($b); // mesmo aparelho, outro usuário: o token muda de dono
        $this->postJson('/api/v1/dispositivos', ['token' => 'abc', 'plataforma' => 'ios'])->assertCreated();
        $this->assertSame(0, $a->dispositivos()->count());
        $this->assertSame(1, $b->dispositivos()->count());

        $this->deleteJson('/api/v1/dispositivos', ['token' => 'abc'])->assertNoContent();
        $this->assertSame(0, $b->dispositivos()->count());
    }
}
