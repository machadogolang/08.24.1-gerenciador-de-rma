<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\GarantiaFornecedorExpirada;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GarantiaFornecedorExpiradaTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_nf_de_compra_passou_de_365_dias(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(366)]);

        $resultado = (new GarantiaFornecedorExpirada())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_nf_de_compra_recente(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(10)]);

        $resultado = (new GarantiaFornecedorExpirada())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_exatamente_365_dias_nao_dispara_operador_estrito(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(365)]);

        $resultado = (new GarantiaFornecedorExpirada())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Concluido, 'nfcompra_emissao' => now()->subDays(366)]);

        $resultado = (new GarantiaFornecedorExpirada())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
