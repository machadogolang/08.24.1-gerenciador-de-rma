<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Fabricante;
use App\Models\ModificacaoDeRma;
use App\Models\Rma;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use App\Rma\Dominio\AcaoDeModificacao;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UF-10 e UF-11 - Unificacao Funcional de Auditoria e Creditos nos Temas V1 e V2:
 * - UF-10 (GAP-V1-03..05): Historico de acesso, modificacao e atalho Ver sob Tema V1.
 * - UF-11 (GAP-V1-07): Tabela completa de creditos disponiveis e acao no Tema V1.
 */
class UniaoFuncionalV1V2Test extends TestCase
{
    use RefreshDatabase;

    public function test_tema_v1_controle_disponibiliza_paineis_para_logs_de_autenticacao_e_modificacao(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($supervisor)->get(route('rmas.controle.index'));

        $response->assertOk();
        $response->assertSee('LOGS DE AUTENTICAÇÃO');
        $response->assertSee('LOGS DE MODIFICAÇÃO DE RMA');
        $response->assertSee(route('identidade.historico-de-acesso.index'), false);
        $response->assertSee(route('rmas.historico.index'), false);
    }

    public function test_tema_v1_renderiza_historico_de_modificacoes_com_detalhe_ver_e_estilo_nativo(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante Alpha']);
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'descricao' => 'Placa mae com defeito',
            'modelo' => 'H510M',
            'fabricante_id' => $fabricante->id,
        ]);

        $modificacao = new ModificacaoDeRma([
            'rma_id' => $rma->id,
            'user_id' => $supervisor->id,
            'acao' => AcaoDeModificacao::Criacao,
            'estado_apos' => [
                'fabricante' => $fabricante->nome,
                'descricao' => $rma->descricao,
                'modelo' => $rma->modelo,
            ],
            'ip' => '127.0.0.1',
        ]);
        $modificacao->tenant_id = $rma->tenant_id;
        $modificacao->save();

        $response = $this->actingAs($supervisor)->get(route('rmas.historico.index'));

        $response->assertOk();
        $response->assertSee('Histórico de modificações de RMA');
        $response->assertSee('Fabricante Alpha');
        $response->assertSee('Placa mae com defeito');
        $response->assertSee('H510M');
        $response->assertSee('Criacao');
        $response->assertSee(route('rmas.show', $rma->id), false);
        $response->assertSee('images/rma/ver.png', false);
    }

    public function test_tema_v1_renderiza_historico_de_acesso_com_estilo_nativo(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        TentativaDeAcesso::create([
            'user_id' => $supervisor->id,
            'email_informado' => 'supervisor@cellsystem.local',
            'ip' => '192.168.1.100',
            'resultado' => ResultadoDeAcesso::Permitido,
            'user_agent' => 'Mozilla/5.0 TestBrowser',
        ]);

        $response = $this->actingAs($supervisor)->get(route('identidade.historico-de-acesso.index'));

        $response->assertOk();
        $response->assertSee('Histórico de acesso');
        $response->assertSee('supervisor@cellsystem.local');
        $response->assertSee('192.168.1.100');
        $response->assertSee('Permitido');
    }

    public function test_tema_v1_creditos_apresenta_tabela_completa_e_formulario_de_marcar(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $fabricante = Fabricante::factory()->create(['nome' => 'Asus']);
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'solucao' => Solucao::GeradoCredito,
            'credito_disponivel' => true,
            'fabricante_id' => $fabricante->id,
            'descricao' => 'GPU RTX 3060',
            'modelo' => 'Dual OC',
            'valor' => 1500.00,
        ]);

        $response = $this->actingAs($supervisor)->get(route('rmas.credito.index'));

        $response->assertOk();
        $response->assertSee('RCD');
        $response->assertSee('Créditos disponíveis');
        $response->assertSee('GPU RTX 3060');
        $response->assertSee('Dual OC');
        $response->assertSee('Asus');
        $response->assertSee('1500.00');
        $response->assertSee(route('rmas.show', $rma->id), false);
        $response->assertSee('MARCAR CRÉDITO DISPONÍVEL');
        $response->assertSee(route('rmas.credito.marcar'), false);
    }

    public function test_rotas_qa_deterministicas_v1_respondem_200(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $this->actingAs($supervisor)->get('/v1/controle')->assertOk();
        $this->actingAs($supervisor)->get('/v1/creditos')->assertOk();
        $this->actingAs($supervisor)->get('/v1/historico-de-acesso')->assertOk();
        $this->actingAs($supervisor)->get('/v1/rmas-historico')->assertOk();
    }
}
