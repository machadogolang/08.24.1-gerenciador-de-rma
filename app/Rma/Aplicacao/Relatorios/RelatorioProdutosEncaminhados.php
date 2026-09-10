<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RMPE (`LEG-RMA-039`) - RMAs encaminhados dentro de um intervalo de datas real,
 * exigido via Form Request (`data_inicio`/`data_fim` obrigatórios). Corrige o
 * intervalo hardcoded para "2014" do legado - bug de manutenção, não RN documentada
 * (ver `proposal.md`).
 */
final class RelatorioProdutosEncaminhados
{
    public function listar(\DateTimeInterface $dataInicio, \DateTimeInterface $dataFim): Collection
    {
        // PAR14-REL-RMPE-002 - regra do Legacy: status IN (ENCAMINHADO, RECEBIDO)
        // AND com NF de remessa AND marcarestoque = 1 ORDER BY encaminhado DESC. O
        // intervalo de datas e a melhoria moderna ja decidida (o Legacy fixava 2014).
        return Rma::query()
            ->whereIn('status', [Status::Encaminhado->name, Status::Recebido->name])
            ->whereNotNull('nf_remessa')
            ->whereNotIn('nf_remessa', ['', '0'])
            ->where('marcarestoque', true)
            ->whereBetween('encaminhado_em', [$dataInicio, $dataFim])
            ->orderByDesc('encaminhado_em')
            ->get();
    }
}
