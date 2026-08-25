<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use App\Rma\Dominio\StatusDeLancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ARQ-001 (`INV-RMA-10`) — regressão para o achado de que edição e transições de ciclo
 * de vida reconstruíam o agregado só com os campos do núcleo, apagando prioridade,
 * marcarestoque, notas fiscais, valor e crédito. Cada teste grava um RMA com esses
 * campos periféricos preenchidos de propósito e prova que a ação sob teste não os zera.
 */
class PreservacaoDeEstadoDoAgregadoTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function camposPerifericos(): array
    {
        return [
            'prioridade' => Prioridade::Alta,
            'marcarestoque' => false,
            'nfcompra' => 'NFC-001',
            'nfvenda' => 'NFV-001',
            'lancadoretorno' => StatusDeLancamento::Pendente,
            'valor' => 199.90,
            'credito_disponivel' => true,
        ];
    }

    private function assertCamposPerifericosPreservados(RmaEloquent $rma): void
    {
        $rma->refresh();

        $this->assertSame(Prioridade::Alta, $rma->prioridade);
        $this->assertFalse($rma->marcarestoque);
        $this->assertSame('NFC-001', $rma->nfcompra);
        $this->assertSame('NFV-001', $rma->nfvenda);
        $this->assertSame(StatusDeLancamento::Pendente, $rma->lancadoretorno);
        $this->assertSame(199.90, (float) $rma->valor);
        $this->assertTrue($rma->credito_disponivel);
    }

    public function test_receber_preserva_campos_perifericos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([...$this->camposPerifericos(), 'status' => Status::Entrada]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/receber");

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
    }

    public function test_encaminhar_preserva_campos_perifericos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([...$this->camposPerifericos(), 'status' => Status::Recebido]);
        $assistencia = AssistenciaTecnica::factory()->create();

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario_tipo' => 'assistencia_tecnica',
            'destinatario_id' => $assistencia->id,
        ]);

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
    }

    public function test_concluir_preserva_campos_perifericos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([...$this->camposPerifericos(), 'status' => Status::Encaminhado]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", [
            'solucao' => 'REPARO',
        ]);

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
    }

    public function test_arquivar_preserva_campos_perifericos(): void
    {
        $superAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador]);
        $rma = RmaEloquent::factory()->create([...$this->camposPerifericos(), 'status' => Status::Recebido]);

        $response = $this->actingAs($superAdmin)->post("/rmas/{$rma->id}/arquivar");

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
    }

    public function test_reverter_preserva_campos_perifericos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            ...$this->camposPerifericos(),
            'status' => Status::Encaminhado,
            'encaminhado_em' => now(),
        ]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/reverter");

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
    }

    public function test_registrar_solucao_preserva_campos_perifericos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([...$this->camposPerifericos(), 'status' => Status::Encaminhado]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/solucao", [
            'solucao' => 'REPARO',
        ]);

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
    }

    public function test_editar_preserva_status_e_campos_perifericos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            ...$this->camposPerifericos(),
            'status' => Status::Encaminhado,
            'descricao' => 'Descrição antiga',
        ]);

        $response = $this->actingAs($operador)->put("/rmas/{$rma->id}", [
            'descricao' => 'Descrição nova',
            'defeito' => $rma->defeito,
        ]);

        $response->assertRedirect();
        $this->assertCamposPerifericosPreservados($rma);
        $this->assertSame(Status::Encaminhado, $rma->status);
        $this->assertSame('Descrição nova', $rma->descricao);
    }
}
