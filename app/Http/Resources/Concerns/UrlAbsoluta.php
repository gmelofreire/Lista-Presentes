<?php

namespace App\Http\Resources\Concerns;

trait UrlAbsoluta
{
    /** Imagens ficam salvas como caminho relativo (/storage/..., /img/...) ou URL externa; a API sempre devolve URL absoluta. */
    protected static function absoluta(?string $caminho): ?string
    {
        if (! $caminho) {
            return null;
        }

        return preg_match('#^https?://#i', $caminho) ? $caminho : url($caminho);
    }
}
