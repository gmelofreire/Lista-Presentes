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
        // Na API a lista vem da URL (/listas/{lista}/presentes).
        if ($this->route('lista')) {
            $this->merge(['lista_id' => $this->route('lista')]);
        }

        $this->merge([
            'preco' => $this->preco ? str_replace(',', '.', $this->preco) : null,
            'cadastrado_por' => auth()->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'nullable|numeric',
            'link' => 'nullable|string',
            'image_url' => 'nullable',
            'lista_id' => 'required|string|exists:listas,id',
            'anotacoes' => 'nullable|string',
            'avaliacao' => 'nullable|integer',
            'categoria_ids' => 'nullable|array',
            'categoria_ids.*' => ['string', \Illuminate\Validation\Rule::exists('categorias', 'id')->where('cadastrado_por', auth()->id())],
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
