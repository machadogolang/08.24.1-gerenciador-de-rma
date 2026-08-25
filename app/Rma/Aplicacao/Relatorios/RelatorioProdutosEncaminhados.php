<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RMPE (`LEG-RMA-039`) — RMAs encaminhados dentro de um intervalo de datas real,
 * exigido via Form Request (`data_inicio`/`data_fim` obrigatórios). Corrige o
 * intervalo hardcoded para "2014" do legado — bug de manutenção, não RN documentada
 * (ver `proposal.md`).
 */
final class RelatorioProdutosEncaminhados
{
    public function listar(\DateTimeInterface $dataInicio, \DateTimeInterface $dataFim): Collection
    {
        return Rma::query()
            ->where('status', Status::Encaminhado)
            ->whereBetween('encaminhado_em', [$dataInicio, $dataFim])
            ->get();
    }
}
