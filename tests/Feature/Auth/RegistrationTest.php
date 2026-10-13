<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'test_user',
            'password' => 'Senha@12345',
            'password_confirmation' => 'Senha@12345',
            'termos' => true,
        ]);

        // O cadastro não faz login automático: o usuário segue para a página de verificação pendente.
        $this->assertGuest();
        $response->assertRedirect(route('verification.pending'));
        $this->assertDatabaseHas('users', ['username' => 'test_user']);
    }
}
