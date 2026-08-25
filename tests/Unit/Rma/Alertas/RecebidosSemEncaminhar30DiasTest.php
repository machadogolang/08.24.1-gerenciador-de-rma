<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\RecebidosSemEncaminhar30Dias;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecebidosSemEncaminhar30DiasTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_recebido_ha_mais_de_30_dias(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'recebido_em' => now()->subDays(31)]);

        $resultado = (new RecebidosSemEncaminhar30Dias())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_recebido_ha_menos_de_30_dias(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'recebido_em' => now()->subDays(10)]);

        $resultado = (new RecebidosSemEncaminhar30Dias())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_exatamente_30_dias_nao_dispara_operador_estrito(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'recebido_em' => now()->subDays(30)]);

        $resultado = (new RecebidosSemEncaminhar30Dias())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Encaminhado, 'recebido_em' => now()->subDays(31)]);

        $resultado = (new RecebidosSemEncaminhar30Dias())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
