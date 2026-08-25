<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\PrioridadeAltaSemEncaminhar;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrioridadeAltaSemEncaminharTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_prioridade_alta(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'prioridade' => Prioridade::Alta]);

        $resultado = (new PrioridadeAltaSemEncaminhar())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_prioridade_media(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'prioridade' => Prioridade::Media]);

        $resultado = (new PrioridadeAltaSemEncaminhar())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_prioridade_nula_nao_dispara(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'prioridade' => null]);

        $resultado = (new PrioridadeAltaSemEncaminhar())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Encaminhado, 'prioridade' => Prioridade::Alta]);

        $resultado = (new PrioridadeAltaSemEncaminhar())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
