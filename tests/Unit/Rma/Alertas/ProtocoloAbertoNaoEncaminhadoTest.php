<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\ProtocoloAbertoNaoEncaminhado;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtocoloAbertoNaoEncaminhadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_ha_protocolo_preenchido(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'protocolo' => '12345']);

        $resultado = (new ProtocoloAbertoNaoEncaminhado())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_protocolo_e_nulo(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'protocolo' => null]);

        $resultado = (new ProtocoloAbertoNaoEncaminhado())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_protocolo_string_vazia_nao_dispara(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'protocolo' => '']);

        $resultado = (new ProtocoloAbertoNaoEncaminhado())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'protocolo' => '12345']);

        $resultado = (new ProtocoloAbertoNaoEncaminhado())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
