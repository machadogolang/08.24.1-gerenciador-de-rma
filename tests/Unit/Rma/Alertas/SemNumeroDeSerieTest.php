<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\SemNumeroDeSerie;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemNumeroDeSerieTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_sn_nulo(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'sn' => null]);

        $resultado = (new SemNumeroDeSerie())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_sn_preenchido(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'sn' => 'SN-123']);

        $resultado = (new SemNumeroDeSerie())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_sn_zero_nao_e_tratado_como_vazio(): void
    {
        // Prova que a checagem é SQL (null/''), não uma interpretação PHP "falsy" —
        // '0' é falsy em PHP mas é um sn preenchido de verdade.
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'sn' => '0']);

        $resultado = (new SemNumeroDeSerie())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_dispara_quando_sn_string_vazia(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'sn' => '']);

        $resultado = (new SemNumeroDeSerie())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'sn' => null]);

        $resultado = (new SemNumeroDeSerie())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
