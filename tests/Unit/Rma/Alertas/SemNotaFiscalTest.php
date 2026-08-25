<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\SemNotaFiscal;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemNotaFiscalTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_nenhuma_nf_preenchida(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'nfcompra' => null, 'nfvenda' => null]);

        $resultado = (new SemNotaFiscal())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_as_duas_nf_preenchidas(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'nfcompra' => 'NF-1', 'nfvenda' => 'NF-2']);

        $resultado = (new SemNotaFiscal())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_apenas_uma_nf_preenchida_nao_dispara(): void
    {
        // Prova o AND: só dispara se as DUAS estiverem vazias, não basta uma.
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'nfcompra' => 'NF-1', 'nfvenda' => null]);

        $resultado = (new SemNotaFiscal())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_dispara_quando_nf_sao_string_vazia(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'nfcompra' => '', 'nfvenda' => '']);

        $resultado = (new SemNotaFiscal())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'nfcompra' => null, 'nfvenda' => null]);

        $resultado = (new SemNotaFiscal())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
