<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-09 (`LEG-RMA-026`) — RMA recebido sem nenhuma NF (nem compra, nem venda)
 * registrada.
 */
final class SemNotaFiscal
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Recebido)
            ->where(fn ($query) => $query->whereNull('nfcompra')->orWhere('nfcompra', ''))
            ->where(fn ($query) => $query->whereNull('nfvenda')->orWhere('nfvenda', ''))
            ->get();
    }
}
