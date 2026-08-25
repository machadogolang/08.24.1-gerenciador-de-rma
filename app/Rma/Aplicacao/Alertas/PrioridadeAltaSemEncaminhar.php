<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-08 (`LEG-RMA-025`) — RMA com prioridade alta ainda não encaminhado.
 */
final class PrioridadeAltaSemEncaminhar
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where('prioridade', Prioridade::Alta)
            ->get();
    }
}
