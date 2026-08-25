<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\NaoVaiDarGarantia;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NaoVaiDarGarantiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_nf_de_venda_passou_de_365_dias(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Recebido,
            'nfvenda_emissao' => now()->subDays(366),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_nf_de_venda_recente(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Recebido,
            'nfvenda_emissao' => now()->subDays(10),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_exatamente_365_dias_nao_dispara_operador_estrito(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Recebido,
            'nfvenda_emissao' => now()->subDays(365),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_dispara_markvision_com_fornecedor_receita_mesmo_sem_nf_vencida(): void
    {
        $fabricante = Fabricante::factory()->create(['nome' => 'MARKVISION']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Receita']);

        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_markvision_sem_fornecedor_receita_e_sem_nf_vencida_nao_dispara(): void
    {
        $fabricante = Fabricante::factory()->create(['nome' => 'MARKVISION']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Outro Fornecedor']);

        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'nfcompra_emissao' => now()->subDays(10),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_dispara_markvision_com_nf_de_compra_vencida_mesmo_sem_fornecedor_receita(): void
    {
        // Segundo ramo do OR interno da regra MARKVISION (fabricante=MARKVISION E
        // (fornecedor=Receita OU nfcompra_emissao vencida)) — cobre o lado do OR que
        // o teste anterior (fornecedor=Receita) não exercitava.
        $fabricante = Fabricante::factory()->create(['nome' => 'MARKVISION']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Outro Fornecedor']);

        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'nfcompra_emissao' => now()->subDays(366),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_fabricante_diferente_de_markvision_nunca_dispara_pela_regra_markvision(): void
    {
        // Prova de que o join é por FK/nome exato "MARKVISION", não por qualquer
        // fabricante com fornecedor "Receita" ou NF vencida.
        $fabricante = Fabricante::factory()->create(['nome' => 'Outro Fabricante']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Receita']);

        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'nfcompra_emissao' => now()->subDays(366),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_status_concluido_nunca_dispara_mesmo_com_condicoes_satisfeitas(): void
    {
        // A regra só se aplica a status Entrada/Recebido — prova de que a query
        // realmente filtra por status no SQL, não só pelas condições de garantia.
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'nfvenda_emissao' => now()->subDays(400),
        ]);

        $resultado = (new NaoVaiDarGarantia())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
