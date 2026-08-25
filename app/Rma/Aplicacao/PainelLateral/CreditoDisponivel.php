<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use Illuminate\Support\Collection;

/**
 * CP19 — `15.8.1/banco.php:855` (`right_creditodisponivel()`): `GROUP BY
 * destinatario WHERE creditodisponivel = 1`. Mesma resolução polimórfica de
 * `Destinatarios`.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class CreditoDisponivel
{
    public function listar(): Collection
    {
        $registros = Rma::query()
            ->with('destinatario')
            ->where('credito_disponivel', true)
            ->whereNotNull('destinatario_id')
            ->get();

        return $registros
            ->filter(fn (Rma $r) => $r->destinatario !== null)
            ->groupBy(fn (Rma $r) => $r->destinatario_type.'#'.$r->destinatario_id)
            ->map(fn ($grupo) => [
                'nome' => mb_substr($grupo->first()->destinatario->nome, 0, 16),
                'contagem' => $grupo->count(),
            ])
            ->values();
    }
}
