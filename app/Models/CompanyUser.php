<?php

namespace App\Models;

use App\Identidade\Dominio\Papel;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot tipado de `company_user` — dá cast a `papel` e `ativo` no vínculo, para que
 * Policies/S9 leiam `Papel` do vínculo ativo sem conversão manual.
 */
class CompanyUser extends Pivot
{
    protected $table = 'company_user';

    protected function casts(): array
    {
        return [
            'papel' => Papel::class,
            'ativo' => 'boolean',
        ];
    }
}
