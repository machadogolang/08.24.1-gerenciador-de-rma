<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use App\Rma\Dominio\StatusDeLancamento;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-03 (`LEG-RMA-020`) — RMA concluído com a NF de retorno ainda pendente de lançar.
 */
final class NfRetornoPendenteDeLancar
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Concluido)
            ->where('lancadoretorno', StatusDeLancamento::Pendente)
            ->get();
    }
}
