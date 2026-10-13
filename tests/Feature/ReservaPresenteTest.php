<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Seguranca\CriaDominio;
use Tests\TestCase;

class ReservaPresenteTest extends TestCase
{
    use CriaDominio, RefreshDatabase;

    public function test_participante_reserva_e_dono_nao_ve_quem(): void
    {
        $dono = User::factory()->create();
        $amigo = User::factory()->create();
        $lista = $this->lista($dono);
        $lista->adicionarParticipante($amigo->id, 'visualizador');
        $presente = $this->presente($lista, $dono);

        $this->actingAs($amigo)->post(route('presentes.reservar', $presente->id))->assertSessionHas('status');

        $this->assertDatabaseHas('presentes', ['id' => $presente->id, 'reservado_por' => $amigo->id, 'comprado' => true]);

        $this->actingAs($dono)->get(route('listas.show', $lista->id))
            ->assertInertia(fn ($page) => $page
                ->where('lista.presentes.0.reservado', true)
                ->where('lista.presentes.0.reservado_por_mim', false)
                ->missing('lista.presentes.0.reservado_por'));

        $this->actingAs($amigo)->get(route('listas.show', $lista->id))
            ->assertInertia(fn ($page) => $page->where('lista.presentes.0.reservado_por_mim', true));
    }

    public function test_dono_nao_reserva_o_proprio_presente(): void
    {
        $dono = User::factory()->create();
        $presente = $this->presente($this->lista($dono), $dono);

        $this->actingAs($dono)->post(route('presentes.reservar', $presente->id))->assertForbidden();
    }

    public function test_nao_participante_nao_reserva(): void
    {
        $dono = User::factory()->create();
        $intruso = User::factory()->create();
        $presente = $this->presente($this->lista($dono), $dono);

        $this->actingAs($intruso)->post(route('presentes.reservar', $presente->id))->assertForbidden();
    }

    public function test_reserva_dupla_e_bloqueada_e_so_o_reservante_libera(): void
    {
        $dono = User::factory()->create();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $lista = $this->lista($dono, ['visibilidade' => 'publica']);
        $presente = $this->presente($lista, $dono);

        $this->actingAs($a)->post(route('presentes.reservar', $presente->id));
        $this->actingAs($b)->post(route('presentes.reservar', $presente->id))->assertSessionHasErrors('error');
        $this->assertDatabaseHas('presentes', ['id' => $presente->id, 'reservado_por' => $a->id]);

        $this->actingAs($b)->delete(route('presentes.liberar', $presente->id))->assertSessionHasErrors('error');
        $this->actingAs($a)->delete(route('presentes.liberar', $presente->id))->assertSessionHas('status');
        $this->assertDatabaseHas('presentes', ['id' => $presente->id, 'reservado_por' => null, 'comprado' => false]);
    }

    public function test_remover_participante_nao_deixa_resquicio_no_pivot(): void
    {
        $dono = User::factory()->create();
        $amigo = User::factory()->create();
        $lista = $this->lista($dono);
        $lista->adicionarParticipante($amigo->id, 'editor');

        $this->assertTrue($amigo->listas()->whereKey($lista->id)->exists());
        $this->assertSame('editor', $amigo->listas()->first()->pivot->papel);
    }
}
