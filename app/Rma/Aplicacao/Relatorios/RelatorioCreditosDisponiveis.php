<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\Rma;
use Illuminate\Database\Eloquent\Collection;

/**
 * RCD (`LEG-RMA-037`) - RMAs com crédito disponível para uso, marcados por
 * `MarcarCreditoDisponivel`.
 */
final class RelatorioCreditosDisponiveis
{
    public function listar(): Collection
    {
        // PAR14-REL-RCD-002 - regra do Legacy: status = CONCLUIDO AND
        // creditodisponivel = 1 ORDER BY protocolo, destinatario.
        return Rma::query()
            ->where('credito_disponivel', true)
            ->where('status', \App\Rma\Dominio\Status::Concluido)
            ->orderBy('protocolo')
            ->orderBy('destinatario_id')
            ->get();
    }
}
