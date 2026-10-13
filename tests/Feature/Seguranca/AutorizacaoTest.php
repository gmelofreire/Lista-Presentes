<?php

namespace Tests\Feature\Seguranca;

use App\Models\Amizade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorizacaoTest extends TestCase
{
    use CriaDominio, RefreshDatabase;

    private User $dono;

    private User $intruso;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dono = User::factory()->create();
        $this->intruso = User::factory()->create();
    }

    public function test_intruso_nao_ve_nem_edita_lista_alheia(): void
    {
        $lista = $this->lista($this->dono);

        $this->actingAs($this->intruso)->get(route('listas.show', $lista->id))->assertForbidden();
        $this->actingAs($this->intruso)->get(route('listas.edit', $lista->id))->assertForbidden();
        $this->actingAs($this->intruso)->post(route('listas.update', $lista->id), ['nome' => 'x', 'visibilidade' => 'publica'])->assertForbidden();
        $this->actingAs($this->intruso)->delete(route('listas.destroy', $lista->id))->assertForbidden();
        $this->assertDatabaseHas('listas', ['id' => $lista->id, 'nome' => 'Lista']);
    }

    public function test_lista_publica_e_visivel_mas_nao_editavel(): void
    {
        $lista = $this->lista($this->dono, ['visibilidade' => 'publica']);

        $this->actingAs($this->intruso)->get(route('listas.show', $lista->id))->assertOk();
        $this->actingAs($this->intruso)->get(route('listas.edit', $lista->id))->assertForbidden();
    }

    public function test_update_nao_permite_mass_assignment(): void
    {
        $lista = $this->lista($this->dono);

        $this->actingAs($this->dono)->post(route('listas.update', $lista->id), [
            'nome' => 'Novo', 'visibilidade' => 'privada', 'cadastrado_por_id' => $this->intruso->id, 'token_convite' => 'x',
        ])->assertRedirect();

        $lista->refresh();
        $this->assertSame('Novo', $lista->nome);
        $this->assertSame($this->dono->id, $lista->cadastrado_por_id);
        $this->assertNull($lista->token_convite);
    }

    public function test_intruso_nao_mexe_em_presente_alheio(): void
    {
        $lista = $this->lista($this->dono);
        $presente = $this->presente($lista, $this->dono);

        $this->actingAs($this->intruso)->get(route('presentes.edit', $presente->id))->assertForbidden();
        $this->actingAs($this->intruso)->delete(route('presentes.destroy', $presente->id))->assertForbidden();
        $this->actingAs($this->intruso)->post(route('presentes.store'), ['nome' => 'x', 'lista_id' => $lista->id])->assertForbidden();
        $this->assertDatabaseHas('presentes', ['id' => $presente->id]);
    }

    public function test_visualizador_nao_adiciona_presente(): void
    {
        $lista = $this->lista($this->dono);
        $lista->adicionarParticipante($this->intruso->id, 'visualizador');

        $this->actingAs($this->intruso)->post(route('presentes.store'), ['nome' => 'x', 'lista_id' => $lista->id])->assertForbidden();
    }

    public function test_editor_adiciona_presente(): void
    {
        $lista = $this->lista($this->dono);
        $lista->adicionarParticipante($this->intruso->id, 'editor');

        $this->actingAs($this->intruso)->post(route('presentes.store'), ['nome' => 'x', 'lista_id' => $lista->id])->assertRedirect();
        $this->assertDatabaseHas('presentes', ['nome' => 'x', 'cadastrado_por' => $this->intruso->id]);
    }

    public function test_convite_exige_token_valido_e_entra_como_visualizador(): void
    {
        $lista = $this->lista($this->dono);

        // sem token gerado: ninguém entra
        $this->actingAs($this->intruso)->get(route('listas.participar', $lista->id))->assertForbidden();

        $token = $this->actingAs($this->dono)->postJson(route('listas.convite', $lista->id))->json('token');

        $this->actingAs($this->intruso)->get(route('listas.participar', ['listaId' => $lista->id, 'token' => 'errado', 'papel' => 'admin']))->assertForbidden();
        $this->actingAs($this->intruso)->get(route('listas.participar', ['listaId' => $lista->id, 'token' => $token, 'papel' => 'admin']))->assertRedirect();

        $this->assertDatabaseHas('lista_participantes', ['lista_id' => $lista->id, 'usuario_id' => $this->intruso->id, 'papel' => 'visualizador']);
    }

    public function test_so_admin_gerencia_participantes(): void
    {
        $lista = $this->lista($this->dono);
        $lista->adicionarParticipante($this->intruso->id, 'editor');

        $this->actingAs($this->intruso)->postJson(route('listas.convite', $lista->id))->assertForbidden();
        $this->actingAs($this->intruso)->post(route('listas.participantes.store', $lista->id), ['usuario_id' => $this->intruso->id, 'papel' => 'admin'])->assertForbidden();
    }

    public function test_grupo_so_para_membros_e_gestao_so_para_admin(): void
    {
        $grupo = $this->grupo($this->dono);

        $this->actingAs($this->intruso)->get(route('grupos.show', $grupo->id))->assertForbidden();
        $this->actingAs($this->intruso)->get(route('grupos.edit', $grupo->id))->assertForbidden();
        $this->actingAs($this->intruso)->delete(route('grupos.destroy', $grupo->id))->assertForbidden();
        $this->actingAs($this->intruso)->postJson(route('grupos.inviteLink', $grupo->id))->assertForbidden();
        $this->actingAs($this->intruso)->postJson(route('grupos.makeAdmin', $grupo->id), ['member_id' => $this->intruso->id])->assertForbidden();

        $grupo->integrantes()->attach($this->intruso->id, ['role' => 'visualizador']);
        $this->actingAs($this->intruso)->postJson(route('grupos.removeMember', $grupo->id), ['member_id' => $this->dono->id])->assertForbidden();
        $this->actingAs($this->intruso)->get(route('grupos.show', $grupo->id))->assertOk();
    }

    public function test_convite_de_grupo_funciona_para_admin(): void
    {
        $grupo = $this->grupo($this->dono);

        $link = $this->actingAs($this->dono)->postJson(route('grupos.inviteLink', $grupo->id))->assertOk()->json('link');
        $this->actingAs($this->intruso)->get($link)->assertRedirect(route('grupos.show', $grupo->id));
        $this->assertDatabaseHas('grupo_usuario', ['grupo_id' => $grupo->id, 'user_id' => $this->intruso->id, 'role' => 'visualizador']);
    }

    public function test_grupo_convida_apenas_amigos(): void
    {
        $grupo = $this->grupo($this->dono);

        $this->actingAs($this->dono)->postJson(route('grupos.inviteFriends', $grupo->id), ['friend_ids' => [$this->intruso->id]])->assertOk();
        $this->assertDatabaseMissing('grupo_usuario', ['grupo_id' => $grupo->id, 'user_id' => $this->intruso->id]);

        $this->amigos($this->dono, $this->intruso);
        $this->actingAs($this->dono)->postJson(route('grupos.inviteFriends', $grupo->id), ['friend_ids' => [$this->intruso->id]])->assertOk();
        $this->assertDatabaseHas('grupo_usuario', ['grupo_id' => $grupo->id, 'user_id' => $this->intruso->id]);
    }

    public function test_categoria_alheia_protegida(): void
    {
        $cat = $this->categoria($this->dono);

        $this->actingAs($this->intruso)->get(route('categorias.edit', $cat->id))->assertForbidden();
        $this->actingAs($this->intruso)->put(route('categorias.update', $cat->id), ['nome' => 'x', 'hex_cor' => '#fff'])->assertForbidden();
        $this->actingAs($this->intruso)->delete(route('categorias.destroy', $cat->id))->assertNotFound();
        $this->assertDatabaseHas('categorias', ['id' => $cat->id, 'nome' => 'Cat']);
    }

    public function test_presente_nao_aceita_categoria_de_outro_usuario(): void
    {
        $lista = $this->lista($this->dono);
        $catAlheia = $this->categoria($this->intruso);

        $this->actingAs($this->dono)->post(route('presentes.store'), [
            'nome' => 'x', 'lista_id' => $lista->id, 'categoria_ids' => [$catAlheia->id],
        ])->assertSessionHasErrors('categoria_ids.0');
    }

    public function test_amizade_rejeita_auto_amizade_e_duplicata(): void
    {
        $this->actingAs($this->dono)->post(route('amizade.store', $this->dono->id))->assertSessionHas('error');
        $this->assertSame(0, Amizade::count());

        $this->actingAs($this->dono)->post(route('amizade.store', $this->intruso->id))->assertRedirect();
        $this->actingAs($this->intruso)->post(route('amizade.store', $this->dono->id))->assertSessionHas('error');
        $this->assertSame(1, Amizade::count());
    }

    public function test_so_o_destinatario_aceita_solicitacao(): void
    {
        $this->actingAs($this->dono)->post(route('amizade.store', $this->intruso->id));

        // o próprio solicitante tentando se aceitar não tem efeito
        $this->actingAs($this->dono)->put(route('amizades.update', $this->intruso->id));
        $this->assertDatabaseHas('amizades', ['status' => 'pendente']);

        $this->actingAs($this->intruso)->put(route('amizades.update', $this->dono->id));
        $this->assertDatabaseHas('amizades', ['status' => 'aceito']);
    }

    public function test_ssrf_bloqueado(): void
    {
        foreach (['http://127.0.0.1/', 'http://169.254.169.254/latest/meta-data/', 'http://localhost:8000/', 'file:///etc/passwd', 'http://10.0.0.1/'] as $url) {
            $this->actingAs($this->dono)->postJson(route('presentes.buscar-dados-url'), ['url' => $url])
                ->assertStatus(422);
        }
    }

    public function test_upload_de_svg_e_rejeitado(): void
    {
        $svg = \Illuminate\Http\UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->actingAs($this->dono)->post(route('listas.store'), [
            'nome' => 'x', 'visibilidade' => 'privada', 'image_url' => $svg,
        ])->assertSessionHasErrors('image_url');
    }

    public function test_per_page_e_limitado(): void
    {
        $this->actingAs($this->dono)->get(route('listas.index', ['per_page' => 100000]))
            ->assertInertia(fn ($page) => $page->where('listas.per_page', 50));
    }
}
