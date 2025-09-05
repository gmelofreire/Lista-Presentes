<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresenteUpdateValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'preco' => $this->preco ? str_replace(',', '.', $this->preco) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
            'preco' => 'nullable|numeric',
            'link' => 'nullable|string',
            'image_url' => 'nullable',
            'anotacoes' => 'nullable|string|max:255',
            'categoria_ids' => 'nullable|array',
            'avaliacao' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório',
            'preco.numeric' => 'O preço deve ser um número',
            'avaliacao.integer' => 'A avaliação deve ser um número inteiro',
        ];
    }
}
