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
            'integrante_ids.*' => 'exists:users,id',
            'image_url' => ['nullable', function ($attr, $valor, $fail) {
                if ($valor instanceof \Illuminate\Http\UploadedFile && ! in_array($valor->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
                    $fail('A imagem deve ser jpeg, png, gif ou webp.');
                }
            }],
            'banner_url' => ['nullable', function ($attr, $valor, $fail) {
                if ($valor instanceof \Illuminate\Http\UploadedFile && ! in_array($valor->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
                    $fail('O banner deve ser jpeg, png, gif ou webp.');
                }
            }],
            'visibilidade' => 'nullable|in:publica,privada',
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
