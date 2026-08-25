<?php

namespace Tests\Unit\Rma;

use App\Rma\Dominio\Solucao;
use PHPUnit\Framework\TestCase;

class SolucaoTest extends TestCase
{
    public function test_tem_exatamente_16_valores(): void
    {
        $this->assertCount(16, Solucao::cases());
    }

    /**
     * @return list<Solucao>
     */
    private function osQueImplicamMesmoAparelho(): array
    {
        return [
            Solucao::TrocaDePecaInterna,
            Solucao::Reparo,
            Solucao::OrcamentoPago,
            Solucao::OrcamentoNegado,
            Solucao::ReparoPeloRma,
            Solucao::TestadoTudoOk,
        ];
    }

    public function test_exatamente_6_solucoes_implicam_mesmo_aparelho_de_retorno(): void
    {
        $implicam = array_filter(
            Solucao::cases(),
            fn (Solucao $solucao) => $solucao->implicaMesmoAparelhoDeRetorno(),
        );

        $this->assertCount(6, $implicam);
        $this->assertEqualsCanonicalizing($this->osQueImplicamMesmoAparelho(), array_values($implicam));
    }

    public function test_as_10_demais_solucoes_nao_implicam_mesmo_aparelho_de_retorno(): void
    {
        $naoImplicam = array_filter(
            Solucao::cases(),
            fn (Solucao $solucao) => ! $solucao->implicaMesmoAparelhoDeRetorno(),
        );

        $this->assertCount(10, $naoImplicam);
    }
}
