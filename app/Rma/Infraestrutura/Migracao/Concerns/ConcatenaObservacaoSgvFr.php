<?php

namespace App\Rma\Infraestrutura\Migracao\Concerns;

/**
 * `INV-RMA-06` §16 — `cliente`/`fornecedor` legado usam 2 colunas (`observacaoSGV` +
 * `observacaoFR`, nome vazado de módulo copiado), V3 tem um único campo. Concatena só as
 * partes não vazias, `"SGV: {...}\nFR: {...}"`.
 */
trait ConcatenaObservacaoSgvFr
{
    private function concatenarObservacaoSgvFr(?string $sgv, ?string $fr): ?string
    {
        $partes = [];

        if ($sgv !== null && trim($sgv) !== '') {
            $partes[] = "SGV: {$sgv}";
        }

        if ($fr !== null && trim($fr) !== '') {
            $partes[] = "FR: {$fr}";
        }

        return $partes === [] ? null : implode("\n", $partes);
    }
}
