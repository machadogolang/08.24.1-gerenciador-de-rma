<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-05 (`LEG-RMA-022`) — garantia do fornecedor (365 dias a partir da NF de compra)
 * já expirada. Operador estrito `<`.
 *
 * `nfcompra_emissao` é coluna `date` (sem hora) — o limite usa `today()` (meia-noite),
 * não `now()` (hora atual): comparar uma data pura contra um timestamp com hora
 * deslocaria o limite em até 1 dia dependendo da hora em que a consulta roda.
 */
final class GarantiaFornecedorExpirada
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where('nfcompra_emissao', '<', today()->subDays(365))
            ->get();
    }
}
