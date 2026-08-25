<?php

namespace App\Rma\Dominio\Eventos;

use App\Models\User;
use App\Rma\Dominio\Rma;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado por `EditarRma (Fase 3)` ao final da operação. Fase 7:
 * `RegistrarModificacaoDeRma` assina este evento (mesmo padrão de `RmaConcluido`).
 */
final class RmaEditado
{
    use Dispatchable;

    public function __construct(
        public readonly User $ator,
        public readonly Rma $rma,
    ) {}
}
