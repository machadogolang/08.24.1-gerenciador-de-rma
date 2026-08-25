<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use Illuminate\Support\Collection;

/**
 * CP19 — `metodo.php:99` (`listar_nome_de_descricoes()`): `SELECT descricao,
 * COUNT(descricao) FROM bd GROUP BY descricao`, sem filtro de status — todos os RMAs.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class TodosProdutos
{
    public function listar(): Collection
    {
        return Rma::query()
            ->get()
            ->groupBy('descricao')
            ->map(fn ($grupo, $descricao) => [
                'nome' => mb_substr((string) $descricao, 0, 16),
                'contagem' => $grupo->count(),
            ])
            ->values();
    }
}
