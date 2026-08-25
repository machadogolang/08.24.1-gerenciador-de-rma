<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Fabricante;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * VIS-V1-010 — painel "Controle" do TEMA V1 (`14.6.1/page/controle.php`), distinto do
 * "Controle" do TEMA V2 (`rmas.historico.index`, inalterado — ver `HistoricoDeModificacaoTest`).
 */
class ControlePainelTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_ve_o_painel_controle(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);

        $response = $this->actingAs($supervisor)->get(route('rmas.controle.index'));

        $response->assertOk();
        $response->assertSee('ADICIONAR REPRESENTANTE');
        $response->assertSee('ARQUIVAR UMA SOLICITACAO DE RMA');
        $response->assertSee('DELETAR UMA SOLICITACAO DE RMA');
        $response->assertSee('DELETAR UM USUARIO');
        $response->assertSee('INFORMACAO DO PROCEDIMENTO DE RMA');
        $response->assertSee('LISTAR SOLICITACOES DE RMA ARQUIVADAS');
        $response->assertSee('MUDAR SENHA');
    }

    public function test_operador_nao_pode_ver_o_painel_controle(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->get(route('rmas.controle.index'));

        $response->assertForbidden();
    }

    public function test_visitante_nao_autenticado_e_redirecionado_ao_login(): void
    {
        $response = $this->get(route('rmas.controle.index'));

        $response->assertRedirect('/login');
    }

    public function test_painel_lista_apenas_rmas_arquivados(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante Arquivado']);
        $arquivado = RmaEloquent::factory()->create([
            'descricao' => 'RMA arquivado visivel',
            'status' => Status::Arquivado,
            'fabricante_id' => $fabricante->id,
        ]);
        RmaEloquent::factory()->create(['descricao' => 'RMA em entrada', 'status' => Status::Entrada]);

        $response = $this->actingAs($supervisor)->get(route('rmas.controle.index'));

        $response->assertOk();
        $response->assertSee('RMA arquivado visivel');
        $response->assertSee('Fabricante Arquivado');
        $response->assertDontSee('RMA em entrada');
    }

    public function test_painel_informa_nenhum_item_arquivado_quando_lista_vazia(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);

        $response = $this->actingAs($supervisor)->get(route('rmas.controle.index'));

        $response->assertOk();
        $response->assertSee('Nenhum item arquivado');
    }

    public function test_arquivar_por_numero_continua_funcionando_a_partir_da_rota_reaproveitada(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($supervisor)->post(route('rmas.arquivar', ['rma' => $rma->id]));

        $response->assertRedirect(route('rmas.show', $rma->id));
        $this->assertSame(Status::Arquivado, $rma->fresh()->status);
    }

    public function test_menu_v1_aponta_controle_para_o_painel_novo_nao_para_o_historico(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);

        $response = $this->actingAs($supervisor)->get(route('rmas.index'));

        $response->assertOk();
        $response->assertSee(route('rmas.controle.index'), false);
        $response->assertDontSee(route('rmas.historico.index'), false);
    }

    public function test_historico_de_modificacao_continua_acessivel_pela_propria_rota(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);

        $response = $this->actingAs($supervisor)->get(route('rmas.historico.index'));

        $response->assertOk();
    }
}
