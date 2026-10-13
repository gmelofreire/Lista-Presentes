<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Seguranca\CriaDominio;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use CriaDominio, RefreshDatabase;

    private const SENHA = 'Senha@12345';

    public function test_registro_login_me_e_logout(): void
    {
        $r = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana', 'email' => 'ana@example.com', 'username' => 'ana_1',
            'password' => self::SENHA, 'password_confirmation' => self::SENHA, 'termos' => true,
        ])->assertCreated()->assertJsonStructure(['token', 'tipo', 'user' => ['id', 'username', 'email']]);

        $this->withToken($r->json('token'))->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.username', 'ana_1');

        $login = $this->postJson('/api/v1/auth/login', ['login' => 'ana_1', 'password' => self::SENHA, 'device_name' => 'ios'])
            ->assertOk()->json('token');
        $this->postJson('/api/v1/auth/login', ['login' => 'ana@example.com', 'password' => self::SENHA])->assertOk();

        $this->withToken($login)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 2); // o do registro e o do login por e-mail; o de "ios" foi revogado
    }

    public function test_login_invalido_e_rota_protegida(): void
    {
        User::factory()->create(['username' => 'bia']);

        $this->postJson('/api/v1/auth/login', ['login' => 'bia', 'password' => 'errada'])->assertStatus(422)->assertJsonValidationErrors('login');
        $this->getJson('/api/v1/listas')->assertUnauthorized();
        $this->getJson('/api/v1/rota-que-nao-existe')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_login_reativa_conta_desativada(): void
    {
        $u = User::factory()->create(['username' => 'cris', 'password' => self::SENHA]);
        $u->forceFill(['conta_ativa' => false, 'desativado_em' => now()])->save();

        $this->postJson('/api/v1/auth/login', ['login' => 'cris', 'password' => self::SENHA])->assertOk();
        $this->assertTrue((bool) $u->fresh()->conta_ativa);
    }

    public function test_crud_de_lista_com_cursor_e_imagem_absoluta(): void
    {
        $u = User::factory()->create();
        Sanctum::actingAs($u);

        $id = $this->postJson('/api/v1/listas', ['nome' => 'Natal', 'visibilidade' => 'privada'])
            ->assertCreated()->assertJsonPath('data.meu_papel', 'admin')->json('data.id');

        $this->postJson('/api/v1/listas', ['nome' => 'Aniversário', 'visibilidade' => 'privada'])->assertCreated();

        $page = $this->getJson('/api/v1/listas?per_page=1')->assertOk();
        $page->assertJsonCount(1, 'data')->assertJsonStructure(['data', 'links', 'meta' => ['next_cursor']]);
        $this->assertStringStartsWith('http', $page->json('data.0.imagem'));

        $this->patchJson("/api/v1/listas/$id", ['nome' => 'Natal 2026', 'visibilidade' => 'publica'])->assertOk()->assertJsonPath('data.nome', 'Natal 2026');
        $this->deleteJson("/api/v1/listas/$id")->assertNoContent();
        $this->getJson("/api/v1/listas/$id")->assertNotFound();
    }

    public function test_api_respeita_policies(): void
    {
        $dono = User::factory()->create();
        $outro = User::factory()->create();
        $lista = $this->lista($dono);
        $presente = $this->presente($lista, $dono);

        Sanctum::actingAs($outro);
        $this->getJson("/api/v1/listas/{$lista->id}")->assertForbidden();
        $this->deleteJson("/api/v1/listas/{$lista->id}")->assertForbidden();
        $this->postJson("/api/v1/listas/{$lista->id}/presentes", ['nome' => 'x'])->assertForbidden();
        $this->postJson("/api/v1/presentes/{$presente->id}/reserva")->assertForbidden();
        $this->getJson('/api/v1/listas')->assertJsonCount(0, 'data');
    }

    public function test_reserva_nao_vaza_quem_reservou(): void
    {
        $dono = User::factory()->create();
        $amigo = User::factory()->create();
        $lista = $this->lista($dono);
        $lista->adicionarParticipante($amigo->id, 'visualizador');
        $presente = $this->presente($lista, $dono);

        Sanctum::actingAs($amigo);
        $this->postJson("/api/v1/presentes/{$presente->id}/reserva")->assertOk()->assertJsonPath('data.reservado_por_mim', true);

        Sanctum::actingAs($dono);
        $this->postJson("/api/v1/presentes/{$presente->id}/reserva")->assertForbidden();
        $r = $this->getJson("/api/v1/listas/{$lista->id}")->assertOk();
        $r->assertJsonPath('data.presentes.0.reservado', true)->assertJsonPath('data.presentes.0.reservado_por_mim', false);
        $this->assertStringNotContainsString($amigo->id, json_encode($r->json('data.presentes')));

        $terceiro = User::factory()->create();
        $lista->adicionarParticipante($terceiro->id, 'visualizador');
        Sanctum::actingAs($terceiro);
        $this->postJson("/api/v1/presentes/{$presente->id}/reserva")->assertStatus(409)->assertJson(['success' => false]);
    }

    public function test_presente_aninhado_na_lista_e_validacao_422(): void
    {
        $dono = User::factory()->create();
        $lista = $this->lista($dono);
        Sanctum::actingAs($dono);

        $this->postJson("/api/v1/listas/{$lista->id}/presentes", ['nome' => 'Livro', 'preco' => '29,90'])
            ->assertCreated()->assertJsonPath('data.preco', 29.9)->assertJsonPath('data.lista_id', $lista->id);
        $this->postJson("/api/v1/listas/{$lista->id}/presentes", [])->assertStatus(422)->assertJsonValidationErrors('nome');
    }

    public function test_amizades_grupos_notificacoes_categorias(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        Sanctum::actingAs($a);
        $this->postJson("/api/v1/amigos/{$b->id}/solicitacao")->assertCreated();
        $this->postJson("/api/v1/amigos/{$b->id}/solicitacao")->assertStatus(409);
        $this->getJson('/api/v1/amigos/pendentes')->assertJsonCount(1, 'enviadas');

        Sanctum::actingAs($b);
        $this->getJson('/api/v1/notificacoes/nao-lidas')->assertJson(['count' => 1]);
        $this->postJson("/api/v1/amigos/{$a->id}/aceitar")->assertOk();
        $this->postJson('/api/v1/notificacoes/ler-todas')->assertNoContent();
        $this->getJson('/api/v1/notificacoes/nao-lidas')->assertJson(['count' => 0]);

        Sanctum::actingAs($a);
        $this->getJson('/api/v1/amigos')->assertJsonCount(1, 'data');
        $g = $this->postJson('/api/v1/grupos', ['nome' => 'Família', 'integrante_ids' => [$b->id]])
            ->assertCreated()->assertJsonCount(2, 'data.integrantes')->json('data.id');
        $this->getJson("/api/v1/grupos/$g")->assertOk()->assertJsonPath('data.meu_papel', 'proprietario');
        $this->postJson("/api/v1/grupos/$g/convite")->assertOk()->assertJsonStructure(['token']);

        $c = $this->postJson('/api/v1/categorias', ['nome' => 'Livros', 'descricao' => 'x', 'hex_cor' => '#ff0000'])->assertCreated()->json('data.id');
        $this->getJson('/api/v1/categorias')->assertJsonCount(1, 'data');
        $this->deleteJson("/api/v1/categorias/$c")->assertNoContent();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/v1/grupos/$g")->assertForbidden();
    }

    public function test_ssrf_na_api(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/presentes/buscar-dados-url', ['url' => 'http://169.254.169.254/'])->assertStatus(422);
    }
}
