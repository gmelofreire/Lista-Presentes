<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/perfil')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/perfil', [
                'name' => 'Test User',
                'email' => $user->email,
                'username' => 'novo_username',
                'perfil' => ['genero' => 'outro'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/perfil');

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('novo_username', $user->username);
    }

    public function test_changing_email_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/perfil', [
                'name' => $user->name,
                'email' => 'outro@example.com',
                'username' => $user->username,
                'perfil' => [],
            ])
            ->assertSessionHasErrors('senha_atual');

        $this->assertNotSame('outro@example.com', $user->fresh()->email);
    }

    public function test_user_account_is_deactivated_not_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/perfil', ['password' => 'password', 'confirm_desativar' => true])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNotNull($user->fresh());
        $this->assertFalse((bool) $user->fresh()->conta_ativa);
    }

    public function test_correct_password_must_be_provided_to_deactivate_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/perfil')
            ->delete('/perfil', ['password' => 'wrong-password', 'confirm_desativar' => true])
            ->assertSessionHasErrors('password')
            ->assertRedirect('/perfil');

        $this->assertTrue((bool) $user->fresh()->conta_ativa);
    }
}
