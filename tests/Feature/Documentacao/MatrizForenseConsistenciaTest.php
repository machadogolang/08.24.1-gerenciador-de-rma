<?php

namespace Tests\Feature\Documentacao;

use Tests\TestCase;

/**
 * ADENDO P0/bloco 5 (AD-42) - protecao simples contra inconsistencia documental
 * na matriz forense.
 *
 * Nao e framework: le o arquivo canonico, extrai as linhas de tabela que comecam
 * por um ID `PAR14-*`/`PAR15-*` e falha se o MESMO ID aparecer com marcadores de
 * estado conflitantes (`[x]` contra `[R]` ou `[ ]`). Foi exatamente esse tipo de
 * contradicao (resumo dizendo `[x]`, tabela detalhada dizendo `[R]`) que a
 * reconciliacao de 2026-09-10 precisou corrigir a mao.
 */
class MatrizForenseConsistenciaTest extends TestCase
{
    public function test_nenhum_id_da_matriz_aparece_com_status_conflitante(): void
    {
        $caminho = base_path('docs/produto/2026-09-10-matriz-forense-paridade-legacy-v1-v2.md');
        $this->assertFileExists($caminho);

        $statusPorId = [];

        foreach (file($caminho, FILE_IGNORE_NEW_LINES) as $numero => $linha) {
            if (! preg_match('/^\|\s*(PAR1[45]-[A-Z0-9-]+)\s*\|/', (string) $linha, $casamento)) {
                continue;
            }

            preg_match_all('/\[(x|R| )\]/', (string) $linha, $marcadores);

            foreach ($marcadores[1] as $marcador) {
                $statusPorId[$casamento[1]][$marcador][] = $numero + 1;
            }
        }

        $conflitos = [];

        foreach ($statusPorId as $id => $porMarcador) {
            if (count($porMarcador) > 1) {
                $conflitos[] = $id.': '.json_encode($porMarcador);
            }
        }

        $this->assertSame(
            [],
            $conflitos,
            "IDs com status conflitante na matriz forense:\n".implode("\n", $conflitos),
        );
    }
}
