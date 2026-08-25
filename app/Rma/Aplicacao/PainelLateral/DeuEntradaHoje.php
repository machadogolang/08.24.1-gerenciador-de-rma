<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use Illuminate\Database\Eloquent\Collection;

/**
 * CP19 (paridade visual V2) — `15.8.1/banco.php:708` (`right_entrada()`): RMAs cuja
 * entrada aconteceu a partir de hoje 00:00. `created_at` é o equivalente moderno da
 * coluna `entrada` do legado (marca a criação do registro).
 */
final class DeuEntradaHoje
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('created_at', '>=', now()->startOfDay())
            ->orderByDesc('created_at')
            ->get();
    }
}
