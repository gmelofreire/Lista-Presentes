<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Violação de regra de negócio com mensagem segura para o usuário.
 * Renderizada em bootstrap/app.php: JSON para API/axios, redirect com erro para Inertia.
 */
class RegraDeNegocioException extends RuntimeException
{
    public function __construct(string $mensagem, public readonly int $status = 422)
    {
        parent::__construct($mensagem);
    }
}
