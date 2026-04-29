<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GrupoValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $integrantes = array_unique(
            array_merge(
                (array) $this->input('integrante_ids', []),
                [auth()->id()]
            )
        );

        $data = ['integrante_ids' => $integrantes];

        if ($this->routeIs('grupos.store')) {
            $data['cadastrado_por'] = auth()->id();
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'integrante_ids' => 'required|array',
            'image_url' => 'nullable',
            'banner_url' => 'nullable',
            'visibilidade' => 'nullable|string',
        ];

        if ($this->routeIs('grupos.store')) {
            $rules['cadastrado_por'] = 'required|exists:users,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'descricao.required' => 'O campo descrição é obrigatório.',
            'integrante_ids.required' => 'O campo integrantes é obrigatório.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome do grupo',
            'descricao' => 'descrição do grupo',
            'integrante_ids' => 'integrantes do grupo',
        ];
    }
}
