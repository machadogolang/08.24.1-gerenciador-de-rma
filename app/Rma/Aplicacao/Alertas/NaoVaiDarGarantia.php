<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-02 (`LEG-RMA-019`) — inclui a regra MARKVISION hardcoded do legado: fabricante
 * MARKVISION nunca dá garantia quando o fornecedor é "Receita" OU quando a NF de
 * compra já passou de 365 dias. Join real via FK (`fabricante`/`fornecedor`,
 * relações Eloquent — Fase 2/3), não comparação de string. Filtro inteiramente no SQL.
 *
 * `nfvenda_emissao`/`nfcompra_emissao` são colunas `date` — limites com `today()`
 * (não `now()`), mesma razão de `GarantiaFornecedorExpirada`.
 */
final class NaoVaiDarGarantia
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('nfvenda_emissao')
                        ->where('nfvenda_emissao', '<', today()->subDays(365));
                })->orWhere(function ($query) {
                    $query->whereHas('fabricante', fn ($q) => $q->where('nome', 'MARKVISION'))
                        ->where(function ($query) {
                            $query->whereHas('fornecedor', fn ($q) => $q->where('nome', 'Receita'))
                                ->orWhere(function ($query) {
                                    $query->whereNotNull('nfcompra_emissao')
                                        ->where('nfcompra_emissao', '<', today()->subDays(365));
                                });
                        });
                });
            })
            ->get();
    }
}
