<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresenteValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'preco' => $this->preco ? str_replace(',', '.', $this->preco) : null,
            'cadastrado_por' => auth()->user()->id,
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
            'lista_id' => 'required|string',
            'anotacoes' => 'nullable|string|max:255',
            'avaliacao' => 'nullable|integer',
            'cadastrado_por' => 'required|string',
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
