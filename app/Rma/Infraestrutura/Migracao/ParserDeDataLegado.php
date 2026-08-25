<?php

namespace App\Rma\Infraestrutura\Migracao;

use Carbon\Carbon;

/**
 * PENDÊNCIA-1 de `INV-RMA-06` (resolvida com o parser de 3 tentativas descrito lá):
 * `recebido`/`encaminhado`/`concluido`/`arquivado`/`nfcompra_emissao`/`nfvenda_emissao`/
 * `nf_remessa_emissao`/`nf_retorno_emissao` são todos `varchar` no legado, digitação
 * livre sem máscara (RN-02 documenta que `Diferenca_de_dias()` espera `d/m/Y`, mas o
 * campo não força esse formato). (a) tenta `d/m/Y`; (b) se falhar, tenta `Y-m-d`; (c) se
 * ambos falharem, devolve resultado não-parseável — **nunca lança exceção**, quem chama
 * decide o que fazer (gravar `NULL` + registrar no relatório de reconciliação com o
 * valor bruto original).
 */
final class ParserDeDataLegado
{
    public static function parse(?string $bruto): ResultadoDeParseDeData
    {
        $bruto = $bruto === null ? null : trim($bruto);

        if ($bruto === null || $bruto === '') {
            return new ResultadoDeParseDeData(data: null, ok: true, bruto: $bruto);
        }

        foreach (['d/m/Y', 'Y-m-d'] as $formato) {
            try {
                $data = Carbon::createFromFormat($formato, $bruto);
            } catch (\Throwable) {
                // Carbon lança exceção (não devolve `false`) para entrada que não bate
                // nem minimamente no formato tentado — tratado como "próxima
                // tentativa", nunca deixado escapar (nunca lança exceção que aborte a
                // linha inteira, `INV-RMA-06` PENDÊNCIA-1).
                continue;
            }

            if ($data !== false && $data->format($formato) === $bruto) {
                // Formatos puramente de data — zera a hora para não herdar o
                // horário-corrente do momento em que o migrador rodou.
                return new ResultadoDeParseDeData(data: $data->startOfDay(), ok: true, bruto: $bruto);
            }
        }

        return new ResultadoDeParseDeData(data: null, ok: false, bruto: $bruto);
    }
}
