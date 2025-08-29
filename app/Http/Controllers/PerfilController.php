<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilValidator;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PerfilController extends Controller
{
    protected $title = "Perfil";

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
            $user->fill([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'username' => $validatedData['username']
            ]);
            
            $user->save();
            
            $perfilData = $validatedData['perfil'];
            
            if ($request->hasFile('imagem')) {
                $perfilData['image_url'] = $uploader->upload($request->file('imagem'), 'perfil', $uploader->extensoesImagem);
            }
            
            $user->perfil::updateOrCreate(['user_id' => $user->id], $perfilData);
            
            return Redirect::route('perfil.edit')->with('status', 'Perfil atualizado com sucesso!');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar perfil: ' . $e->getMessage());
            
            return Redirect::route('perfil.edit')
                ->withErrors(['error' => 'Ocorreu um erro ao atualizar o perfil. Tente novamente.'])
                ->withInput();
        }
    }
    
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
