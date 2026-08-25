<?php

namespace Tests\Unit\Rma;

use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use PHPUnit\Framework\TestCase;

/**
 * ARQ-002 (`INV-RMA-10`) — o relatório precisa deixar explícito quando as contagens de
 * "destino" são apenas o que seria gravado (`--dry-run`), nunca escrita real, para não
 * ser lido como uma migração de fato concluída.
 */
class RelatorioDeReconciliacaoTest extends TestCase
{
    public function test_resumo_normal_nao_menciona_dry_run(): void
    {
        $relatorio = new RelatorioDeReconciliacao;
        $relatorio->contarOrigem('rmas', 5);
        $relatorio->contarDestino('rmas', 5);

        $resumo = $relatorio->resumo();

        $this->assertStringNotContainsString('dry-run', $resumo);
        $this->assertStringContainsString('destino=5', $resumo);
    }

    public function test_resumo_marcado_como_dry_run_rotula_a_coluna_como_planejado(): void
    {
        $relatorio = new RelatorioDeReconciliacao;
        $relatorio->marcarComoDryRun();
        $relatorio->contarOrigem('rmas', 5);
        $relatorio->contarDestino('rmas', 3);

        $resumo = $relatorio->resumo();

        $this->assertStringContainsString('dry-run', $resumo);
        $this->assertStringContainsString('planejado=3', $resumo);
        $this->assertStringNotContainsString('destino=3', $resumo);
    }
}
