<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-10 (`LEG-RMA-027`) — RMA recebido sem número de série preenchido.
 */
final class SemNumeroDeSerie
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Recebido)
            ->where(fn ($query) => $query->whereNull('sn')->orWhere('sn', ''))
            ->orderByDesc('recebido_em')
            ->get();
    }
}
