<?php

namespace App\Rma\Dominio\Eventos;

use App\Models\User;
use App\Rma\Dominio\Rma;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado por `ReverterRmaParaEntrada (Fase 4)` ao final da operação. Fase 7:
 * `RegistrarModificacaoDeRma` assina este evento (mesmo padrão de `RmaConcluido`).
 */
final class RmaRevertido
{
    use Dispatchable;

    public function __construct(
        public readonly User $ator,
        public readonly Rma $rma,
    ) {}
}
