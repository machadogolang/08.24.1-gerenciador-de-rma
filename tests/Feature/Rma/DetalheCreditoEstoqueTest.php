<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Prioridade 3 da matriz forense (PAR14-RMA-STOCK/CREDIT-001, PAR15-RMA-STOCK/CREDIT-001):
 * prova funcional de salvar -> reload -> persistencia e do comportamento por Policy para
 * os controles de estoque e credito, em cada tema, cada um com o seu tipo de controle
 * (V1: checkbox; V2: select Nao/Sim).
 */
class DetalheCreditoEstoqueTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payloadBase(Rma $rma): array
    {
        return ['descricao' => $rma->descricao, 'defeito' => $rma->defeito];
    }

    #[Test]
    public function v1_persiste_estoque_e_credito_e_reidrata_os_checks(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['status' => Status::Entrada]);

        $this->actingAs($operador)->put("/v1/rma/{$rma->id}", $this->payloadBase($rma) + [
            'marcarestoque' => '1',
            'credito_disponivel' => '1',
            'acao' => 'salvar',
        ])->assertRedirect("/v1/rma/{$rma->id}");

        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'marcarestoque' => true,
            'credito_disponivel' => true,
        ]);

        $html = $this->actingAs($operador)->get("/v1/rma/{$rma->id}")->assertOk()->getContent();
        $this->assertStringContainsString('value="1" checked', $html);
    }

    #[Test]
    public function v2_persiste_estoque_e_credito_via_select_e_reidrata_selecionado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['status' => Status::Entrada]);

        $this->actingAs($operador)->put("/v2/rma/{$rma->id}", $this->payloadBase($rma) + [
            'marcarestoque' => '1',
            'credito_disponivel' => '1',
            'selectacaodown' => 'salvar',
            'okdown' => 'OK',
        ])->assertRedirect("/v2/rma/{$rma->id}");

        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'marcarestoque' => true,
            'credito_disponivel' => true,
        ]);

        $html = $this->actingAs($operador)->get("/v2/rma/{$rma->id}")->assertOk()->getContent();
        $this->assertStringContainsString('value="1" selected', $html);
    }

    #[Test]
    public function v1_salvar_com_estoque_desmarcado_persiste_falso(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'marcarestoque' => true,
            'credito_disponivel' => true,
        ]);

        $this->actingAs($operador)->put("/v1/rma/{$rma->id}", $this->payloadBase($rma) + [
            'marcarestoque' => '0',
            'credito_disponivel' => '0',
            'acao' => 'salvar',
        ])->assertRedirect("/v1/rma/{$rma->id}");

        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'marcarestoque' => false,
            'credito_disponivel' => false,
        ]);
    }

    #[Test]
    public function papel_leitura_nao_pode_salvar_estoque_e_credito(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        // Estado inicial explicito: a factory nao pode mascarar o teste de nao-escrita.
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'marcarestoque' => false,
            'credito_disponivel' => false,
        ]);

        $this->actingAs($leitura)->put("/v1/rma/{$rma->id}", $this->payloadBase($rma) + [
            'marcarestoque' => '1',
            'credito_disponivel' => '1',
            'acao' => 'salvar',
        ])->assertForbidden();

        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'marcarestoque' => false,
            'credito_disponivel' => false,
        ]);
    }
}
