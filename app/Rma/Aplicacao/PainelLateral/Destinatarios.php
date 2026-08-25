<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Collection;

/**
 * CP19 — `15.8.1/banco.php:885` (`right_destinatarios()`): `GROUP BY destinatario`
 * (nome). O legado grava o destinatário como texto solto; o V3 usa relação
 * polimórfica (`destinatario_type`/`destinatario_id`) — agrupar em PHP pelo par
 * tipo+id (mesmo par usado por `ListagensPorStatusController::mapaDeDestinatarios()`)
 * evita colidir ids de tabelas diferentes, depois resolve para o nome e soma.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class Destinatarios
{
    public function listar(): Collection
    {
        $registros = Rma::query()
            ->with('destinatario')
            ->whereIn('status', [Status::Recebido, Status::Encaminhado])
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
