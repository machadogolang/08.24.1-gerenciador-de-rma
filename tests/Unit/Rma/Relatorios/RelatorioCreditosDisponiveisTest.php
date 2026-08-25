<?php

namespace Tests\Unit\Rma\Relatorios;

use App\Models\Rma;
use App\Rma\Aplicacao\Relatorios\RelatorioCreditosDisponiveis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioCreditosDisponiveisTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_rma_com_credito_disponivel(): void
    {
        $rma = Rma::factory()->create(['credito_disponivel' => true]);

        $resultado = (new RelatorioCreditosDisponiveis())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_sem_credito_disponivel(): void
    {
        $rma = Rma::factory()->create(['credito_disponivel' => false]);

        $resultado = (new RelatorioCreditosDisponiveis())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
