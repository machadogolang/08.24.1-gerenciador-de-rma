<?php

namespace Tests\Unit\Rma\Relatorios;

use App\Models\Rma;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEmEstoqueParaContagem;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioProdutosEmEstoqueParaContagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_rma_marcado_para_contagem_de_estoque(): void
    {
        $rma = Rma::factory()->create(['marcarestoque' => true]);

        $resultado = (new RelatorioProdutosEmEstoqueParaContagem())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_lista_rma_nao_marcado_para_contagem_de_estoque(): void
    {
        $rma = Rma::factory()->create(['marcarestoque' => false]);

        $resultado = (new RelatorioProdutosEmEstoqueParaContagem())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_filtro_de_status_configuravel_pelo_usuario(): void
    {
        $recebido = Rma::factory()->create(['marcarestoque' => true, 'status' => Status::Recebido]);
        $encaminhado = Rma::factory()->create(['marcarestoque' => true, 'status' => Status::Encaminhado]);

        $resultado = (new RelatorioProdutosEmEstoqueParaContagem())->listar(Status::Recebido);

        $this->assertTrue($resultado->contains('id', $recebido->id));
        $this->assertFalse($resultado->contains('id', $encaminhado->id));
    }
}
