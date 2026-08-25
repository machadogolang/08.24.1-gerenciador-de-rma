<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-06 (`LEG-RMA-023`) — mesma base de `GarantiaFornecedorExpirada`, janela aberta
 * `(hoje-365d, hoje-336d)` (dias restantes = 365 - dias decorridos, exibido para o
 * usuário fora desta classe). Ambos os limites estritos.
 *
 * `nfcompra_emissao` é coluna `date` — limites com `today()`, mesma razão de
 * `GarantiaFornecedorExpirada`.
 */
final class GarantiaFornecedorExpirandoEm30Dias
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where('nfcompra_emissao', '>', today()->subDays(365))
            ->where('nfcompra_emissao', '<', today()->subDays(336))
            ->get();
    }
}
