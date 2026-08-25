<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarSolucaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_registra_solucao_independente_do_status(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->post("/rmas/{$rma->id}/solucao", [
            'solucao' => Solucao::PendenteCredito->value,
        ]);

        $response->assertRedirect();
        $rma->refresh();
        $this->assertSame(Solucao::PendenteCredito, $rma->solucao);
        $this->assertSame(Status::Entrada, $rma->status);
    }

    public function test_registrar_solucao_aplica_auto_preenchimento_de_snretorno(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['sn' => 'SN-XYZ', 'snretorno' => null]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/solucao", [
            'solucao' => Solucao::Reparo->value,
        ]);

        $rma->refresh();
        $this->assertSame('SN-XYZ', $rma->snretorno);
    }

    public function test_usuario_de_leitura_nao_pode_registrar_solucao(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = RmaEloquent::factory()->create();

        $response = $this->actingAs($leitura)->post("/rmas/{$rma->id}/solucao", [
            'solucao' => Solucao::Reparo->value,
        ]);

        $response->assertForbidden();
    }
}
