<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\Rma;
use Illuminate\Database\Eloquent\Collection;

/**
 * RCD (`LEG-RMA-037`) — RMAs com crédito disponível para uso, marcados por
 * `MarcarCreditoDisponivel`.
 */
final class RelatorioCreditosDisponiveis
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('credito_disponivel', true)
            ->get();
    }
}
