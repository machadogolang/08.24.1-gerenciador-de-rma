<?php

namespace Tests\Unit\Rma\Relatorios;

use App\Models\Rma;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEncaminhados;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PAR14-REL-RMPE-002 - regra confirmada do Legacy: status IN (ENCAMINHADO, RECEBIDO)
 * AND com NF de remessa AND marcarestoque = 1, dentro do intervalo informado (o
 * intervalo real substitui o "2014" hardcoded do Legacy).
 */
class RelatorioProdutosEncaminhadosTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function flagsLegado(): array
    {
        return ['marcarestoque' => true, 'nf_remessa' => '123'];
    }

    public function test_lista_rma_encaminhado_dentro_do_intervalo(): void
    {
        $rma = Rma::factory()->create($this->flagsLegado() + [
            'status' => Status::Encaminhado,
            'encaminhado_em' => '2026-03-15 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-31'),
        );

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_lista_rma_recebido_com_remessa(): void
    {
        $rma = Rma::factory()->create($this->flagsLegado() + [
            'status' => Status::Recebido,
            'encaminhado_em' => '2026-03-15 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-31'),
        );

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_encaminhado_fora_do_intervalo(): void
    {
        $rma = Rma::factory()->create($this->flagsLegado() + [
            'status' => Status::Encaminhado,
            'encaminhado_em' => '2025-01-01 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-31'),
        );

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    /**
     * Prova de que o intervalo e real (parametro), nao hardcoded para "2014".
     */
    public function test_intervalo_nao_e_hardcoded_para_2014(): void
    {
        $rma = Rma::factory()->create($this->flagsLegado() + [
            'status' => Status::Encaminhado,
            'encaminhado_em' => '2019-07-10 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2019-07-01'),
            new \DateTimeImmutable('2019-07-31'),
        );

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_fora_dos_status_do_legado(): void
    {
        $rma = Rma::factory()->create($this->flagsLegado() + [
            'status' => Status::Concluido,
            'encaminhado_em' => '2026-03-15 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-31'),
        );

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_encaminhado_sem_nf_de_remessa(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => null,
            'encaminhado_em' => '2026-03-15 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-31'),
        );

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
