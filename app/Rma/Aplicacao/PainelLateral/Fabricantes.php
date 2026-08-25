<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Collection;

/**
 * CP19 — `15.8.1/banco.php:898` (`right_fabricantes()`): `GROUP BY fabricante`.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class Fabricantes
{
    public function listar(): Collection
    {
        return Rma::query()
            ->with('fabricante')
            ->whereIn('status', [Status::Entrada, Status::Recebido, Status::Encaminhado])
            ->whereNotNull('fabricante_id')
            ->get()
            ->filter(fn (Rma $r) => $r->fabricante !== null)
            ->groupBy('fabricante_id')
            ->map(fn ($grupo) => [
                'nome' => mb_substr($grupo->first()->fabricante->nome, 0, 16),
                'contagem' => $grupo->count(),
            ])
            ->values();
    }
}
