<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\GarantiaFornecedorExpirandoEm30Dias;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GarantiaFornecedorExpirandoEm30DiasTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_dentro_da_janela(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(350)]);

        $resultado = (new GarantiaFornecedorExpirandoEm30Dias())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_fora_da_janela(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(10)]);

        $resultado = (new GarantiaFornecedorExpirandoEm30Dias())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_extremo_superior_365_dias_nao_dispara_operador_estrito(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(365)]);

        $resultado = (new GarantiaFornecedorExpirandoEm30Dias())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_extremo_inferior_336_dias_nao_dispara_operador_estrito(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra_emissao' => now()->subDays(336)]);

        $resultado = (new GarantiaFornecedorExpirandoEm30Dias())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
