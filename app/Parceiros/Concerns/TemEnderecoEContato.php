<?php

namespace App\Parceiros\Concerns;

use App\Compartilhado\Uf;

/**
 * Campos de endereço/contato/comercial compartilhados por Fabricante, Fornecedor e
 * AssistenciaTecnica — schema idêntico entre os 3 (ver `design.md`). `Cliente` não usa
 * esta trait: seu schema é genuinamente diferente (sem `email_secundario`/`www`/
 * `frete`/`cfop`/`politica_de_garantia`).
 */
trait TemEnderecoEContato
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'uf' => Uf::class,
        ];
    }
}
