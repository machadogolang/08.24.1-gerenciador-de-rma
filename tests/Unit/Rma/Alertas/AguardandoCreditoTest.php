<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\AguardandoCredito;
use App\Rma\Dominio\Solucao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AguardandoCreditoTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_solucao_e_pendente_credito(): void
    {
        $rma = Rma::factory()->create(['solucao' => Solucao::PendenteCredito]);

        $resultado = (new AguardandoCredito())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outra_solucao(): void
    {
        $rma = Rma::factory()->create(['solucao' => Solucao::GeradoCredito]);

        $resultado = (new AguardandoCredito())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_solucao_e_nula(): void
    {
        $rma = Rma::factory()->create(['solucao' => null]);

        $resultado = (new AguardandoCredito())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
