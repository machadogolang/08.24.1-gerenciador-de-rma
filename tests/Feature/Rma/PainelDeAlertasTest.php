<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelDeAlertasTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_autenticado_ve_o_painel_com_rma_disparando_alerta(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create([
            'descricao' => 'RMA prioridade alta sem encaminhar',
            'status' => Status::Recebido,
            'prioridade' => Prioridade::Alta,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.alertas'));

        $response->assertOk();
        $response->assertSee('RMA prioridade alta sem encaminhar');
    }

    public function test_painel_nao_mostra_rma_que_nao_dispara_nenhum_alerta(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA tranquilo sem pendencia',
            'status' => Status::Concluido,
            'prioridade' => Prioridade::Baixa,
            'sn' => 'SN-OK',
            'nfcompra' => 'NF-1',
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.alertas'));

        $response->assertOk();
        $response->assertDontSee('RMA tranquilo sem pendencia');
    }

    public function test_visitante_nao_autenticado_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('rmas.alertas'));

        $response->assertRedirect(route('login'));
    }
}
