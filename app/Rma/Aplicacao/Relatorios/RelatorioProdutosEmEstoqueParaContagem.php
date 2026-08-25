<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RPEC (`LEG-RMA-038`) — produtos marcados para contagem de estoque
 * (`marcarestoque=true`). O status é filtro configurável pelo usuário (não hardcoded
 * como no legado) — `$status` opcional restringe a consulta quando informado.
 */
final class RelatorioProdutosEmEstoqueParaContagem
{
    public function listar(?Status $status = null): Collection
    {
        return Rma::query()
            ->where('marcarestoque', true)
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->get();
    }
}
