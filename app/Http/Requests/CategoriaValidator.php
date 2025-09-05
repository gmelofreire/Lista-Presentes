<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            "cadastrado_por" => auth()->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            "nome" => "required|string|max:255",
            "descricao" => "nullable|string",
            "hex_cor" => "required|string|max:7",
            "cadastrado_por" => "required|string",
        ];
    }

    public function messages(): array
    {
        return [
            "nome.required" => "O nome é obrigatório",
            "descricao.required" => "A descrição é obrigatória",
            "hex_cor.required" => "A cor é obrigatória",
            "cadastrado_por.required" => "O usuário é obrigatório",
        ];
    }

    public function attributes(): array
    {
        return [
            "nome" => "nome",
            "descricao" => "descrição",
            "hex_cor" => "cor",
            "cadastrado_por" => "usuário",
        ];
    }
}
