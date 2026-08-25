<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-01 (`LEG-RMA-018`) — RMAs recebidos há mais de 30 dias e ainda não encaminhados.
 * Filtro inteiramente no SQL (decisão central da Fase 5, ver `design.md`) — elimina a
 * classe de bug "num_rows mentiroso" do legado (filtro de data em PHP pós-`SELECT`).
 * Operador estrito `<` (não `<=`), mesmo comportamento do legado.
 */
final class RecebidosSemEncaminhar30Dias
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Recebido)
            ->where('recebido_em', '<', now()->subDays(30))
            ->get();
    }
}
