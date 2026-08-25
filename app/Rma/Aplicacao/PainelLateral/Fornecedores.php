<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Collection;

/**
 * CP19 — `15.8.1/banco.php:912` (`right_fornecedores()`): `GROUP BY fornecedor`.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class Fornecedores
{
    public function listar(): Collection
    {
        return Rma::query()
            ->with('fornecedor')
            ->whereIn('status', [Status::Entrada, Status::Recebido, Status::Encaminhado])
            ->whereNotNull('fornecedor_id')
            ->get()
            ->filter(fn (Rma $r) => $r->fornecedor !== null)
            ->groupBy('fornecedor_id')
            ->map(fn ($grupo) => [
                'nome' => mb_substr($grupo->first()->fornecedor->nome, 0, 16),
                'contagem' => $grupo->count(),
            ])
            ->values();
    }
}
