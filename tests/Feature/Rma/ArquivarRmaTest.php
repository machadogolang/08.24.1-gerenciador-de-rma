<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Prova de que `ArquivarRma` segue TEMA V2 (`15.8.1/banco.php::arquivar()`), não TEMA
 * V1 (`14.6.1/post/arquivar.php`, `Fatal Error` incondicional — `new controle()`,
 * classe inexistente). Os três status permitidos abaixo (incluindo `Recebido`, o
 * cenário que dispararia o Fatal Error em TEMA V1) devem arquivar com sucesso, não
 * lançar exceção.
 */
class ArquivarRmaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<array{0: Status}>
     */
    public static function statusQuePodemArquivar(): array
    {
        return [
            [Status::Entrada],
            [Status::Recebido],
            [Status::Encaminhado],
        ];
    }

    #[DataProvider('statusQuePodemArquivar')]
    public function test_supervisor_arquiva_rma_em_qualquer_status_permitido_sem_fatal_error(Status $status): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $rma = RmaEloquent::factory()->create(['status' => $status]);

        $response = $this->actingAs($supervisor)->post("/rmas/{$rma->id}/arquivar");

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Status::Arquivado, $rma->status);
        $this->assertNotNull($rma->arquivado_em);
    }

    public function test_nao_pode_arquivar_rma_ja_concluido(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Concluido]);

        $response = $this->actingAs($supervisor)->post("/rmas/{$rma->id}/arquivar");

        $response->assertStatus(422);
    }

    public function test_operador_sem_gerenciar_usuarios_nao_pode_arquivar(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/arquivar");

        $response->assertForbidden();
    }
}
