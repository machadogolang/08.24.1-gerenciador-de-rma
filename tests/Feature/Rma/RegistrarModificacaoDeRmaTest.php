<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\ModificacaoDeRma;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\AcaoDeModificacao;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `LEG-RMA-044`. Prova, para cada um dos 8 valores de `AcaoDeModificacao`, que o
 * listener `RegistrarModificacaoDeRma` (assinando o evento de domínio correspondente)
 * grava uma linha em `modificacoes_de_rma` com `acao` correta e `estado_apos`
 * refletindo os campos-chave do RMA no momento da ação.
 */
class RegistrarModificacaoDeRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_criacao_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/rmas', [
            'descricao' => 'Notebook não liga',
            'defeito' => 'Não liga',
        ]);

        $response->assertRedirect();

        $rma = RmaEloquent::query()->where('descricao', 'Notebook não liga')->firstOrFail();
        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Criacao, $modificacao->acao);
        $this->assertSame($operador->id, $modificacao->user_id);
        $this->assertSame('Notebook não liga', $modificacao->estado_apos['descricao']);
    }

    public function test_edicao_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['descricao' => 'Original']);

        $response = $this->actingAs($operador)->put("/rmas/{$rma->id}", [
            'descricao' => 'Editado',
            'defeito' => 'Defeito X',
        ]);

        $response->assertRedirect();

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Edicao, $modificacao->acao);
        $this->assertSame('Editado', $modificacao->estado_apos['descricao']);
    }

    public function test_receber_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/receber");

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Receber, $modificacao->acao);
        $this->assertSame('Recebido', $modificacao->estado_apos['status']);
    }

    public function test_encaminhar_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);
        $assistencia = \App\Models\AssistenciaTecnica::factory()->create();

        $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario_tipo' => 'assistencia_tecnica',
            'destinatario_id' => $assistencia->id,
        ]);

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Encaminhar, $modificacao->acao);
        $this->assertSame('Encaminhado', $modificacao->estado_apos['status']);
    }

    public function test_concluir_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", [
            'solucao' => Solucao::DevolucaoDoProduto->value,
        ]);

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Concluir, $modificacao->acao);
        $this->assertSame('Concluido', $modificacao->estado_apos['status']);
    }

    public function test_arquivar_registra_modificacao(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $this->actingAs($supervisor)->post("/rmas/{$rma->id}/arquivar");

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Arquivar, $modificacao->acao);
        $this->assertSame('Arquivado', $modificacao->estado_apos['status']);
    }

    public function test_reverter_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/reverter");

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::Reverter, $modificacao->acao);
        $this->assertSame('Entrada', $modificacao->estado_apos['status']);
    }

    public function test_registrar_solucao_registra_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/solucao", [
            'solucao' => Solucao::Reparo->value,
        ]);

        $modificacao = ModificacaoDeRma::query()->where('rma_id', $rma->id)->firstOrFail();

        $this->assertSame(AcaoDeModificacao::RegistrarSolucao, $modificacao->acao);
        $this->assertSame(Solucao::Reparo->value, $modificacao->estado_apos['solucao']);
    }
}
