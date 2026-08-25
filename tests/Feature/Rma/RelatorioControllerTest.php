<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_rcd_mostra_rma_com_credito_disponivel(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA com credito', 'credito_disponivel' => true]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rcd'));

        $response->assertOk();
        $response->assertSee('RMA com credito');
    }

    public function test_rpec_filtra_por_status_informado(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA recebido para contagem',
            'marcarestoque' => true,
            'status' => Status::Recebido,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA encaminhado para contagem',
            'marcarestoque' => true,
            'status' => Status::Encaminhado,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rpec', ['status' => 'Recebido']));

        $response->assertOk();
        $response->assertSee('RMA recebido para contagem');
        $response->assertDontSee('RMA encaminhado para contagem');
    }

    public function test_rmpe_exige_intervalo_de_datas(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rmpe'));

        $response->assertSessionHasErrors(['data_inicio', 'data_fim']);
    }

    public function test_rmpe_lista_encaminhados_no_intervalo_informado(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA encaminhado no periodo',
            'status' => Status::Encaminhado,
            'encaminhado_em' => '2026-05-10 10:00:00',
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rmpe', [
            'data_inicio' => '2026-05-01',
            'data_fim' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('RMA encaminhado no periodo');
    }

    public function test_visitante_nao_autenticado_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('rmas.relatorios.rcd'));

        $response->assertRedirect(route('login'));
    }
}
