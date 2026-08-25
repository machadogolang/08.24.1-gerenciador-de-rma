<?php

namespace Tests\Unit\Rma\Relatorios;

use App\Models\Rma;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEncaminhados;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioProdutosEncaminhadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_rma_encaminhado_dentro_do_intervalo(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Encaminhado,
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
        $rma = Rma::factory()->create([
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
     * Prova de que o intervalo é real (parâmetro), não hardcoded para "2014" como no
     * legado — um intervalo em outro ano qualquer funciona igual.
     */
    public function test_intervalo_nao_e_hardcoded_para_2014(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Encaminhado,
            'encaminhado_em' => '2019-07-10 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2019-07-01'),
            new \DateTimeImmutable('2019-07-31'),
        );

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_com_outro_status(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Recebido,
            'encaminhado_em' => '2026-03-15 10:00:00',
        ]);

        $resultado = (new RelatorioProdutosEncaminhados())->listar(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-31'),
        );

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
