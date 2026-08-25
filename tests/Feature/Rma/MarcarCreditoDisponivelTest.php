<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarcarCreditoDisponivelTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_marca_credito_disponivel_quando_solucao_e_gerado_credito(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            'solucao' => Solucao::GeradoCredito,
            'credito_disponivel' => false,
        ]);

        $response = $this->actingAs($operador)->post('/rmas-credito/marcar', [
            'rma_id' => $rma->id,
        ]);

        $response->assertRedirect();
        $rma->refresh();
        $this->assertTrue($rma->credito_disponivel);
    }

    public function test_nega_quando_solucao_nao_e_gerado_credito(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            'solucao' => Solucao::Reparo,
            'credito_disponivel' => false,
        ]);

        $response = $this->actingAs($operador)->post('/rmas-credito/marcar', [
            'rma_id' => $rma->id,
        ]);

        $response->assertStatus(422);
        $rma->refresh();
        $this->assertFalse($rma->credito_disponivel);
    }

    public function test_nega_quando_solucao_e_nula(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create([
            'solucao' => null,
            'credito_disponivel' => false,
        ]);

        $response = $this->actingAs($operador)->post('/rmas-credito/marcar', [
            'rma_id' => $rma->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_usuario_de_leitura_nao_pode_marcar_credito(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = RmaEloquent::factory()->create(['solucao' => Solucao::GeradoCredito]);

        $response = $this->actingAs($leitura)->post('/rmas-credito/marcar', [
            'rma_id' => $rma->id,
        ]);

        $response->assertForbidden();
    }

    public function test_visitante_nao_autenticado_e_redirecionado_para_login(): void
    {
        $rma = RmaEloquent::factory()->create(['solucao' => Solucao::GeradoCredito]);

        $response = $this->post('/rmas-credito/marcar', [
            'rma_id' => $rma->id,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_rma_id_inexistente_devolve_404(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/rmas-credito/marcar', [
            'rma_id' => 999999,
        ]);

        $response->assertNotFound();
    }

    public function test_pagina_de_aguardando_credito_lista_rma_com_solucao_pendente_credito(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        RmaEloquent::factory()->create([
            'descricao' => 'RMA aguardando credito',
            'solucao' => Solucao::PendenteCredito,
        ]);
        RmaEloquent::factory()->create([
            'descricao' => 'RMA ja com credito gerado',
            'solucao' => Solucao::GeradoCredito,
        ]);

        $response = $this->actingAs($usuario)->get('/rmas-credito');

        $response->assertOk();
        $response->assertSee('RMA aguardando credito');
        $response->assertDontSee('RMA ja com credito gerado');
    }

    public function test_pagina_de_aguardando_credito_exige_autenticacao(): void
    {
        $response = $this->get('/rmas-credito');

        $response->assertRedirect('/login');
    }
}
