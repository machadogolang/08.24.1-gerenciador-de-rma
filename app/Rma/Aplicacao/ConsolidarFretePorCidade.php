<?php

namespace App\Rma\Aplicacao;

use App\Models\AssistenciaTecnica;
use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * `LEG-RMA-040`, RN-16 — **TEMA V2 como especificação** (confirmado: código idêntico
 * existe em TEMA V1, `14.6.1/inc/startpage.php:139-165`, mas está inteiramente
 * comentado — widget desativado, deliberadamente ou por regressão não documentada, não
 * reproduzido). Cidade "PORTO ALEGRE" mantida hardcoded — comportamento documentado do
 * legado, sem política configurável a reconstruir aqui. JOINs reais via relação
 * Eloquent (FK desde a Fase 2/3/4) — sem os aliases mortos `FOD`/`FAD` do legado
 * (achado de refatoração incompleta, não reproduzido).
 */
final class ConsolidarFretePorCidade
{
    private const CIDADE = 'PORTO ALEGRE';

    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where(function ($query) {
                $query->whereHas('fornecedor', fn ($q) => $q->where('cidade', self::CIDADE))
                    ->orWhereHas('fabricante', fn ($q) => $q->where('cidade', self::CIDADE))
                    ->orWhereHasMorph(
                        'destinatario',
                        [AssistenciaTecnica::class],
                        fn ($q) => $q->where('cidade', self::CIDADE),
                    );
            })
            ->get();
    }
}
