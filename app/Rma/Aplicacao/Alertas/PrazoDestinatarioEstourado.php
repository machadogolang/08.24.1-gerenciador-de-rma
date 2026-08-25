<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-07 (`LEG-RMA-024`) — RMA encaminhado a um destinatário há mais de 30 dias sem
 * retorno.
 */
final class PrazoDestinatarioEstourado
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Encaminhado)
            ->where('encaminhado_em', '<', now()->subDays(30))
            ->get();
    }
}
