<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerfilValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'username' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('users', 'username')->ignore(auth()->id())
            ],
            'perfil.telefone' => ['required', 'string', 'max:255'],
            'perfil.data_nascimento' => ['required', 'date', 'before:today'],
            'perfil.genero' => ['required', 'string', 'in:masculino,feminino,outro'],
            'imagem' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'], // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            
            'username.required' => 'O nome de usuário é obrigatório.',
            'username.string' => 'O nome de usuário deve ser um texto válido.',
            'username.max' => 'O nome de usuário não pode ter mais de 255 caracteres.',
            'username.unique' => 'Este nome de usuário já está sendo usado.',
            
            // Mensagens para campos do perfil
            'perfil.telefone.required' => 'O telefone é obrigatório.',
            'perfil.telefone.string' => 'O telefone deve ser um texto válido.',
            'perfil.telefone.max' => 'O telefone não pode ter mais de 255 caracteres.',
            
            'perfil.data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'perfil.data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'perfil.data_nascimento.before' => 'A data de nascimento deve ser anterior ao dia de hoje.',
            
            'perfil.genero.required' => 'O gênero é obrigatório.',
            'perfil.genero.in' => 'O gênero deve ser: masculino, feminino ou outro.',
            
            'imagem.image' => 'O arquivo deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif ou svg.',
            'imagem.max' => 'A imagem não pode ser maior que 10MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'email',
            'username' => 'nome de usuário',
            'perfil.telefone' => 'telefone',
            'perfil.data_nascimento' => 'data de nascimento',
            'perfil.genero' => 'gênero',
            'perfil.foto' => 'foto',
            'imagem' => 'imagem',
        ];
    }
}
