<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilValidator;
use App\Models\User;
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

    public function update(PerfilValidator $request): RedirectResponse
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
                $image = $request->file('imagem');
                
                // Método mais robusto para obter a extensão
                $extension = $image->getClientOriginalExtension();
                
                // Se não conseguir a extensão do nome original, usar o MIME type
                if (empty($extension)) {
                    $mimeType = $image->getMimeType();
                    $extension = match($mimeType) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                        'image/bmp' => 'bmp',
                        default => 'jpg' // fallback padrão
                    };
                }
                
                // Garantir que sempre tenha uma extensão válida
                if (empty($extension)) {
                    $extension = 'jpg';
                }
                
                $imageName = 'perfil_' . time() . '_' . uniqid() . '.' . $extension;
                
                $imagePath = $image->storeAs('perfil', $imageName, 'public');
                $perfilData['image_url'] = '/storage/' . $imagePath;
                
                if ($user->perfil?->image_url) {
                    $oldImagePath = str_replace('/storage/', '', $user->perfil->image_url);
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }
            }
            
            if ($user->perfil) {
                $user->perfil->update($perfilData);
            } else {
                $user->perfil()->create($perfilData);
            }
            
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
