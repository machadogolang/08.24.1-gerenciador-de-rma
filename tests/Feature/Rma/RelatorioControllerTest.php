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
        Rma::factory()->create([
            'descricao' => 'RMA com credito',
            'status' => Status::Concluido,
            'credito_disponivel' => true,
        ]);

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

    public function test_rmpe_sem_intervalo_lista_todos_os_encaminhados(): void
    {
        // PAR14-REL-RMPE-002 - o Legacy nao filtrava por periodo (a query ignora
        // data). Sem query string o relatorio deve responder 200 e listar tudo.
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA encaminhado fora de qualquer periodo informado',
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => '987',
            'encaminhado_em' => '2026-01-05 10:00:00',
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rmpe'));

        $response->assertOk();
        $response->assertSee('RMA encaminhado fora de qualquer periodo informado');
    }

    public function test_rmpe_exige_os_dois_campos_quando_apenas_um_e_informado(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rmpe', [
            'data_inicio' => '2026-05-01',
        ]));

        $response->assertSessionHasErrors(['data_fim']);
    }

    public function test_rmpe_lista_encaminhados_no_intervalo_informado(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA encaminhado no periodo',
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => '123',
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
