<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Status;
use Illuminate\Support\Collection;

/**
 * CP19 — `15.8.1/banco.php:759` (`right_produtosdecliente()`): `GROUP BY descricao
 * WHERE status IN (entrada,recebido,encaminhado) AND (origem='Cliente' OR
 * 'Licitação') AND marcarestoque=0`.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class ProdutosDeCliente
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido, Status::Encaminhado])
            ->whereIn('origem', [Origem::Cliente->value, Origem::Licitacao->value])
            ->where('marcarestoque', false)
            ->get()
            ->groupBy('descricao')
            ->map(fn ($grupo, $descricao) => [
                'nome' => mb_substr((string) $descricao, 0, 16),
                'contagem' => $grupo->count(),
            ])
            ->values();
    }
}
