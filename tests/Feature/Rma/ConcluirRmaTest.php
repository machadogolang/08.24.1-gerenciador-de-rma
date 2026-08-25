<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Eventos\RmaConcluido;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConcluirRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_conclui_rma_encaminhado_com_solucao(): void
    {
        Event::fake();

        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", [
            'solucao' => Solucao::DevolucaoDoProduto->value,
        ]);

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Status::Concluido, $rma->status);
        $this->assertNotNull($rma->concluido_em);
        $this->assertSame(Solucao::DevolucaoDoProduto, $rma->solucao);
        Event::assertDispatched(RmaConcluido::class);
    }

    public function test_nao_pode_concluir_sem_solucao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", []);

        $response->assertSessionHasErrors(['solucao']);
        $rma->refresh();
        $this->assertSame(Status::Encaminhado, $rma->status);
    }

    public function test_nao_pode_concluir_rma_que_nao_foi_encaminhado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", [
            'solucao' => Solucao::Reparo->value,
        ]);

        $response->assertStatus(422);
    }

    /**
     * @return list<array{0: Solucao}>
     */
    public static function solucoesQueImplicamMesmoAparelho(): array
    {
        return [
            [Solucao::TrocaDePecaInterna],
            [Solucao::Reparo],
            [Solucao::OrcamentoPago],
            [Solucao::OrcamentoNegado],
            [Solucao::ReparoPeloRma],
            [Solucao::TestadoTudoOk],
        ];
    }

    #[DataProvider('solucoesQueImplicamMesmoAparelho')]
    public function test_snretorno_auto_preenchido_para_solucoes_que_implicam_mesmo_aparelho(Solucao $solucao): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado, 'sn' => 'SN-ORIGINAL', 'snretorno' => null]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", ['solucao' => $solucao->value]);

        $rma->refresh();
        $this->assertSame('SN-ORIGINAL', $rma->snretorno);
    }

    /**
     * @return list<array{0: Solucao}>
     */
    public static function solucoesQueNaoImplicamMesmoAparelho(): array
    {
        return [
            [Solucao::TrocaDoProduto],
            [Solucao::PendenteCredito],
            [Solucao::GeradoCredito],
            [Solucao::DevolucaoDoProduto],
            [Solucao::ReembolsoDoDinheiro],
            [Solucao::OrcamentoPendente],
            [Solucao::CasoSolucionado],
            [Solucao::Procon],
            [Solucao::DescritoNaObservacao],
            [Solucao::SemGarantia],
        ];
    }

    #[DataProvider('solucoesQueNaoImplicamMesmoAparelho')]
    public function test_snretorno_fica_em_branco_para_solucoes_que_nao_implicam_mesmo_aparelho(Solucao $solucao): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado, 'sn' => 'SN-ORIGINAL', 'snretorno' => null]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", ['solucao' => $solucao->value]);

        $rma->refresh();
        $this->assertNull($rma->snretorno);
    }
}
