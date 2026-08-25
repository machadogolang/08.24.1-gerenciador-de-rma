<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncaminharRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_encaminha_rma_recebido_com_destinatario(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);
        $assistencia = AssistenciaTecnica::factory()->create();

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario_tipo' => 'assistencia_tecnica',
            'destinatario_id' => $assistencia->id,
        ]);

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Status::Encaminhado, $rma->status);
        $this->assertNotNull($rma->encaminhado_em);
        $this->assertSame(AssistenciaTecnica::class, $rma->destinatario_type);
        $this->assertSame($assistencia->id, $rma->destinatario_id);
    }

    public function test_nao_pode_encaminhar_sem_destinatario(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario_tipo' => '',
            'destinatario_id' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['destinatario_tipo', 'destinatario_id']);
        $rma->refresh();
        $this->assertSame(Status::Recebido, $rma->status);
    }

    public function test_nao_pode_encaminhar_rma_que_ainda_esta_em_entrada(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);
        $assistencia = AssistenciaTecnica::factory()->create();

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario_tipo' => 'assistencia_tecnica',
            'destinatario_id' => $assistencia->id,
        ]);

        $response->assertStatus(422);
    }
}
