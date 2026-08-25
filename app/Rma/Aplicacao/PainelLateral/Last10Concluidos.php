<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * CP19 — `15.8.1/banco.php:748` (`right_concluido()`), `LIMIT 10` literal do legado.
 */
final class Last10Concluidos
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Concluido)
            ->orderByDesc('concluido_em')
            ->limit(10)
            ->get();
    }
}
