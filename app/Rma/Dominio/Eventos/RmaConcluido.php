<?php

namespace App\Rma\Dominio\Eventos;

use App\Rma\Dominio\Rma;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado por `ConcluirRma`. Sem listener nesta fase — a Fase 7 (notificações)
 * assina este evento para enviar o e-mail de conclusão.
 */
final class RmaConcluido
{
    use Dispatchable;

    public function __construct(
        public readonly Rma $rma,
    ) {}
}
