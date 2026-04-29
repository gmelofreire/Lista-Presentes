<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilValidator;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PerfilController extends Controller
{
    protected $title = 'Perfil';

    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'status' => session('status'),
            'title' => $this->title,
        ]);
    }

    public function update(PerfilValidator $request, FileUploadService $uploader): RedirectResponse
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();

            $emailAlterado = $user->email !== $validatedData['email'];
            $usernameAlterado = $user->username !== $validatedData['username'];

            if ($emailAlterado) {
                if (! isset($validatedData['senha_atual']) || empty($validatedData['senha_atual'])) {
                    return Redirect::route('perfil.edit')
                        ->withErrors(['senha_atual' => 'Para alterar o email, informe sua senha atual.'])
                        ->withInput();
                }

                if (! \Illuminate\Support\Facades\Hash::check($validatedData['senha_atual'], $user->password)) {
                    return Redirect::route('perfil.edit')
                        ->withErrors(['senha_atual' => 'Senha atual incorreta.'])
                        ->withInput();
                }
            }

            if ($emailAlterado && $user->hasVerifiedEmail()) {
                $user->email_verified_at = null;
                $user->save();
                $user->sendEmailVerificationNotification();
            }

            $user->fill([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'username' => $validatedData['username'],
            ]);

            $user->save();

            $perfilData = $validatedData['perfil'];

            if ($request->hasFile('imagem')) {
                $perfilData['image_url'] = $uploader->upload($request->file('imagem'), 'perfil', $uploader->extensoesImagem);
            }

            $user->perfil::updateOrCreate(['user_id' => $user->id], $perfilData);

            $mensagem = 'Perfil atualizado com sucesso!';
            if ($emailAlterado) {
                $mensagem .= ' Um novo email de verificação foi enviado.';
            }

            return Redirect::route('perfil.edit')->with('status', $mensagem);

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar perfil: '.$e->getMessage());

            return Redirect::route('perfil.edit')
                ->withErrors(['error' => 'Ocorreu um erro ao atualizar o perfil. Tente novamente.'])
                ->withInput();
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirm_desativar' => ['accepted'],
        ]);

        $user = $request->user();

        $user->conta_ativa = false;
        $user->desativado_em = now();
        $user->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('status', 'Conta desativada com sucesso. Você pode reativar a qualquer momento fazendo login novamente.');
    }
}
