<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\Company;
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
            'destinatario' => "assistencia_tecnica:{$assistencia->id}",
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
            'destinatario' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['destinatario']);
        $rma->refresh();
        $this->assertSame(Status::Recebido, $rma->status);
    }

    public function test_nao_encaminha_para_id_inexistente(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario' => 'assistencia_tecnica:999999',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['destinatario']);
        $this->assertSame(Status::Recebido, $rma->fresh()->status);
        $this->assertNull($rma->fresh()->destinatario_id);
    }

    public function test_nao_encaminha_para_destinatario_de_outra_empresa(): void
    {
        // UX-003/P7 - o `<select>` so lista o tenant ativo e o servidor revalida:
        // id de outra empresa nao pode ser gravado nem vazar.
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Recebido]);

        $empresaB = Company::factory()->create(['nome' => 'Empresa B do encaminhamento']);
        $sigiloso = AssistenciaTecnica::factory()->make(['nome' => 'Assistencia sigilosa B']);
        $sigiloso->forceFill(['tenant_id' => $empresaB->id])->save();

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario' => "assistencia_tecnica:{$sigiloso->id}",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['destinatario']);
        $rma->refresh();
        $this->assertSame(Status::Recebido, $rma->status);
        $this->assertNull($rma->destinatario_id);
    }

    public function test_nao_pode_encaminhar_rma_que_ainda_esta_em_entrada(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);
        $assistencia = AssistenciaTecnica::factory()->create();

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/encaminhar", [
            'destinatario' => "assistencia_tecnica:{$assistencia->id}",
        ]);

        $response->assertStatus(422);
    }
}
