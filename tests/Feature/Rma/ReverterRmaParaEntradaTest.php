<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReverterRmaParaEntradaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_reverte_rma_encaminhado_no_mesmo_dia(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            'status' => Status::Encaminhado,
            'recebido_em' => Carbon::now(),
            'encaminhado_em' => Carbon::now(),
        ]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/reverter");

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Status::Entrada, $rma->status);
        $this->assertNull($rma->recebido_em);
        $this->assertNull($rma->encaminhado_em);
    }

    public function test_operador_nao_reverte_rma_encaminhado_em_dia_anterior(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            'status' => Status::Encaminhado,
            'recebido_em' => Carbon::yesterday(),
            'encaminhado_em' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/reverter");

        $response->assertForbidden();
        $rma->refresh();
        $this->assertSame(Status::Encaminhado, $rma->status);
    }

    public function test_superadministrador_reverte_mesmo_em_dia_anterior(): void
    {
        $superadmin = User::factory()->create(['papel' => Papel::SuperAdministrador]);
        $rma = RmaEloquent::factory()->create([
            'status' => Status::Encaminhado,
            'recebido_em' => Carbon::yesterday(),
            'encaminhado_em' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($superadmin)->post("/rmas/{$rma->id}/reverter");

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Status::Entrada, $rma->status);
    }

    public function test_nao_pode_reverter_rma_em_entrada(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/reverter");

        $response->assertStatus(422);
    }
}
