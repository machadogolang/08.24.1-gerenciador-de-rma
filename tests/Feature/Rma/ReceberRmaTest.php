<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceberRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_recebe_rma_em_entrada(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/receber");

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Status::Recebido, $rma->status);
        $this->assertNotNull($rma->recebido_em);
    }

    public function test_nao_pode_receber_rma_que_ja_foi_recebido(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/receber");

        $response->assertStatus(422);
    }

    public function test_usuario_de_leitura_nao_pode_receber(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($leitura)->post("/rmas/{$rma->id}/receber");

        $response->assertForbidden();
    }
}
