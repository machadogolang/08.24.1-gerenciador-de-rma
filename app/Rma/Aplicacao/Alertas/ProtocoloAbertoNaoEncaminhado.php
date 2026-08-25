<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-04 (`LEG-RMA-021`) — RMA recebido com protocolo (junto ao fabricante/fornecedor)
 * aberto, ainda não encaminhado.
 */
final class ProtocoloAbertoNaoEncaminhado
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Recebido)
            ->whereNotNull('protocolo')
            ->where('protocolo', '!=', '')
            ->get();
    }
}
