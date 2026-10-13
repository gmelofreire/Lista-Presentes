<?php

namespace Tests\Feature;

use App\Exceptions\RegraDeNegocioException;
use App\Models\User;
use App\Services\AmizadeService;
use App\Services\GrupoService;
use App\Services\ListaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Seguranca\CriaDominio;
use Tests\TestCase;

class ServicosDeDominioTest extends TestCase
{
    use CriaDominio, RefreshDatabase;

    public function test_nao_remove_nem_rebaixa_o_ultimo_admin_da_lista(): void
    {
        $dono = User::factory()->create();
        $lista = $this->lista($dono);
        $service = app(ListaService::class);
        $participanteId = $lista->participantes()->value('id');

        try {
            $service->removerParticipante($lista, $participanteId);
            $this->fail('Deveria recusar remover o último admin.');
        } catch (RegraDeNegocioException $e) {
            $this->assertStringContainsString('último administrador', $e->getMessage());
        }

        $this->expectException(RegraDeNegocioException::class);
        $service->alterarPapel($lista, $participanteId, 'editor');
    }

    public function test_atualizar_grupo_preserva_papeis_e_proprietario(): void
    {
        $dono = User::factory()->create();
        $admin = User::factory()->create();
        $grupo = $this->grupo($dono);
        $grupo->integrantes()->attach($admin->id, ['role' => 'admin']);

        app(GrupoService::class)->atualizar($grupo, $dono, [
            'nome' => 'Novo nome',
            'integrante_ids' => [$admin->id], // proprietário omitido de propósito
        ]);

        $this->assertDatabaseHas('grupo_usuario', ['grupo_id' => $grupo->id, 'user_id' => $admin->id, 'role' => 'admin']);
        $this->assertDatabaseHas('grupo_usuario', ['grupo_id' => $grupo->id, 'user_id' => $dono->id, 'role' => 'proprietario']);
        $this->assertSame('Novo nome', $grupo->fresh()->nome);
    }

    public function test_admin_nao_remove_outro_admin_do_grupo(): void
    {
        $dono = User::factory()->create();
        $a1 = User::factory()->create();
        $a2 = User::factory()->create();
        $grupo = $this->grupo($dono);
        $grupo->integrantes()->attach([$a1->id => ['role' => 'admin'], $a2->id => ['role' => 'admin']]);

        $this->expectException(RegraDeNegocioException::class);
        app(GrupoService::class)->removerMembro($grupo, $a1, $a2->id);
    }

    public function test_aceitar_amizade_notifica_o_solicitante(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $service = app(AmizadeService::class);

        $service->solicitar($a, $b);
        $service->aceitar($b, $a->id);

        $this->assertDatabaseHas('notificacoes', ['user_id' => $b->id, 'tipo' => 'solicitacao_amizade']);
        $this->assertDatabaseHas('notificacoes', ['user_id' => $a->id, 'tipo' => 'amizade_aceita']);
        $this->assertContains($b->id, $a->amigoIds()->all());
    }

    public function test_regra_de_negocio_vira_json_422_na_api(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)->postJson(route('amizade.block', $a->id))
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }
}
