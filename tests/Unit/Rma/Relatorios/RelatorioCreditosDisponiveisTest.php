<?php

namespace Tests\Unit\Rma\Relatorios;

use App\Models\Rma;
use App\Rma\Aplicacao\Relatorios\RelatorioCreditosDisponiveis;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PAR14-REL-RCD-002 - regra confirmada do Legacy `14.6.1/page/relatorios.php`:
 * status = CONCLUIDO AND creditodisponivel = 1.
 */
class RelatorioCreditosDisponiveisTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_rma_concluido_com_credito_disponivel(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'credito_disponivel' => true,
        ]);

        $resultado = (new RelatorioCreditosDisponiveis())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_sem_credito_disponivel(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'credito_disponivel' => false,
        ]);

        $resultado = (new RelatorioCreditosDisponiveis())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_credito_disponivel_fora_de_concluido(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'credito_disponivel' => true,
        ]);

        $resultado = (new RelatorioCreditosDisponiveis())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
