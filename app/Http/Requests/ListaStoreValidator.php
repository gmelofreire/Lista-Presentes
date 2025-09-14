<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListaStoreValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'cadastrado_por_id' => auth()->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'visibilidade' => 'required|in:privada,publica',
            'data_evento' => 'nullable|date',
            'grupo_id' => 'nullable|exists:grupos,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cadastrado_por_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'visibilidade.required' => 'O visibilidade é obrigatório.',
            'data_evento.date' => 'A data do evento deve ser uma data válida.',
            'image_url.image' => 'A imagem deve ser um arquivo de imagem.',
            'image_url.mimes' => 'A imagem deve ser um arquivo de tipo jpeg, png, jpg, gif ou svg.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
            'data_evento' => 'data do evento',
            'image_url' => 'imagem',
        ];
    }
}
