<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RPEC (`LEG-RMA-038`) - produtos marcados para contagem de estoque
 * (`marcarestoque=true`). O status é filtro configurável pelo usuário (não hardcoded
 * como no legado) - `$status` opcional restringe a consulta quando informado.
 */
final class RelatorioProdutosEmEstoqueParaContagem
{
    public function listar(?Status $status = null): Collection
    {
        // PAR14-REL-RPEC-002 - regra do Legacy `14.6.1/page/relatorios.php`:
        // status IN (ENTRADA, RECEBIDO, ENCAMINHADO) AND sem NF de remessa AND
        // marcarestoque = 1 ORDER BY modelo (`nfremessa` do Legacy = `nf_remessa`
        // no schema moderno, coluna string nullable).
        return Rma::query()
            ->where('marcarestoque', true)
            ->where(fn ($query) => $query
                ->whereNull('nf_remessa')
                ->orWhere('nf_remessa', '')
                ->orWhere('nf_remessa', '0'))
            ->whereIn('status', [Status::Entrada->name, Status::Recebido->name, Status::Encaminhado->name])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy('modelo')
            ->get();
    }
}
