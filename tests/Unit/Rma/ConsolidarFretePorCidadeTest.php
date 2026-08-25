<?php

namespace Tests\Unit\Rma;

use App\Models\AssistenciaTecnica;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Rma\Aplicacao\ConsolidarFretePorCidade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `LEG-RMA-040`, RN-16 — TEMA V2 como especificação, cidade "PORTO ALEGRE" hardcoded.
 */
class ConsolidarFretePorCidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_rma_com_fornecedor_em_porto_alegre(): void
    {
        $fornecedor = Fornecedor::factory()->create(['cidade' => 'PORTO ALEGRE']);
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'fornecedor_id' => $fornecedor->id]);

        $resultado = (new ConsolidarFretePorCidade())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_lista_rma_com_fabricante_em_porto_alegre(): void
    {
        $fabricante = Fabricante::factory()->create(['cidade' => 'PORTO ALEGRE']);
        $rma = Rma::factory()->create(['status' => Status::Recebido, 'fabricante_id' => $fabricante->id]);

        $resultado = (new ConsolidarFretePorCidade())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_lista_rma_com_destinatario_assistencia_tecnica_em_porto_alegre(): void
    {
        $assistencia = AssistenciaTecnica::factory()->create(['cidade' => 'PORTO ALEGRE']);
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'destinatario_type' => AssistenciaTecnica::class,
            'destinatario_id' => $assistencia->id,
        ]);

        $resultado = (new ConsolidarFretePorCidade())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_com_cidade_diferente(): void
    {
        $fornecedor = Fornecedor::factory()->create(['cidade' => 'CURITIBA']);
        $rma = Rma::factory()->create(['status' => Status::Entrada, 'fornecedor_id' => $fornecedor->id]);

        $resultado = (new ConsolidarFretePorCidade())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_fora_de_entrada_ou_recebido(): void
    {
        $fornecedor = Fornecedor::factory()->create(['cidade' => 'PORTO ALEGRE']);
        $rma = Rma::factory()->create(['status' => Status::Encaminhado, 'fornecedor_id' => $fornecedor->id]);

        $resultado = (new ConsolidarFretePorCidade())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
