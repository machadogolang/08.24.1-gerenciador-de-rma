<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * CP19 — `15.8.1/banco.php:726` (`right_recebido()`).
 */
final class Recebidos
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Recebido)
            ->orderByDesc('recebido_em')
            ->get();
    }
}
