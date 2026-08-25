<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * CP19 — `15.8.1/banco.php:737` (`right_encaminhado()`).
 */
final class Encaminhados
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Encaminhado)
            ->orderByDesc('encaminhado_em')
            ->get();
    }
}
