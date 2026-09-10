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
    public function listar(?\DateTimeInterface $dataInicio = null, ?\DateTimeInterface $dataFim = null): Collection
    {
        // PAR14-REL-RMPE-002 - regra do Legacy: status IN (ENCAMINHADO, RECEBIDO)
        // AND com NF de remessa AND marcarestoque = 1 ORDER BY encaminhado DESC. O
        // Legacy NAO filtrava por periodo; o intervalo de datas e um filtro moderno
        // OPCIONAL (sem ele, a selecao roda sem filtro de periodo).
        return Rma::query()
            ->whereIn('status', [Status::Encaminhado->name, Status::Recebido->name])
            ->whereNotNull('nf_remessa')
            ->whereNotIn('nf_remessa', ['', '0'])
            ->where('marcarestoque', true)
            ->when(
                $dataInicio !== null && $dataFim !== null,
                fn ($query) => $query->whereBetween('encaminhado_em', [$dataInicio, $dataFim]),
            )
            ->orderByDesc('encaminhado_em')
            ->get();
    }
}
