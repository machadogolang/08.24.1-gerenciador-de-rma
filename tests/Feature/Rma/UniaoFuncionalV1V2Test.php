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

    public function test_tema_v1_sinaliza_urgencia_por_threshold_75_com_classe_tr_urgente(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $fabricante = Fabricante::factory()->create();
        $rmaUrgente = Rma::factory()->create([
            'status' => Status::Recebido,
            'origem' => \App\Rma\Dominio\Origem::Cliente,
            'marcarestoque' => false,
            'valor' => 120.00,
            'recebido_em' => now(),
            'fabricante_id' => $fabricante->id,
            'descricao' => 'Item Caro Urgente',
        ]);

        $response = $this->actingAs($supervisor)->get(route('rmas.recebidos'));

        $response->assertOk();
        $response->assertSee('TrUrgente', false);
        $response->assertSee('Item Caro Urgente');
    }

    public function test_tema_v1_permite_definir_e_atualizar_prioridade(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $fabricante = Fabricante::factory()->create();
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'prioridade' => \App\Rma\Dominio\Prioridade::Baixa,
            'fabricante_id' => $fabricante->id,
            'descricao' => 'Produto Teste Prioridade',
        ]);

        // Verificacao na tela de detalhe V1
        $responseShow = $this->actingAs($supervisor)->get(route('rmas.show', $rma->id));
        $responseShow->assertOk();
        $responseShow->assertSee('PRIORIDADE');
        $responseShow->assertSee('name="prioridade"', false);

        // Atualizacao para Alta via PUT
        $responseUpdate = $this->actingAs($supervisor)->put(route('rmas.update', $rma->id), [
            'prioridade' => 'alta',
            'descricao' => 'Produto Teste Prioridade',
            'defeito' => 'Defeito qualquer',
            'fabricante_id' => $fabricante->id,
        ]);

        $responseUpdate->assertRedirect(route('rmas.show', $rma->id));
        $this->assertSame(\App\Rma\Dominio\Prioridade::Alta, $rma->fresh()->prioridade);
    }

    public function test_tema_v1_permite_criar_usuario_com_sucesso(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $responseGet = $this->actingAs($supervisor)->get('/v1/usuarios/novo');
        $responseGet->assertOk();
        $responseGet->assertViewIs('temas.v1.identidade.usuarios-novo');
        $responseGet->assertSee('CADASTRAR USUÁRIO');

        $responsePost = $this->actingAs($supervisor)->post('/v1/usuarios', [
            'name' => 'Operador V1',
            'email' => 'operador.v1@cellsystem.local',
            'password' => 'senha-segura-123',
            'papel' => Papel::Operador->name,
        ]);

        $responsePost->assertRedirect(route('identidade.usuarios.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'operador.v1@cellsystem.local',
            'name' => 'Operador V1',
        ]);
    }

    public function test_tema_v1_parceiros_exibe_rmas_associados_e_dados_completos(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $cliente = \App\Models\Cliente::factory()->create(['nome' => 'Cliente Corporativo']);
        $rma = Rma::factory()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Servidor Dell PowerEdge',
            'status' => Status::Entrada,
        ]);

        $response = $this->actingAs($supervisor)->get(route('v1.parceiros.clientes.show', $cliente->id));

        $response->assertOk();
        $response->assertSee('RMAs associados');
        $response->assertSee('Servidor Dell PowerEdge');
        $response->assertSee(route('rmas.show', $rma->id), false);
    }

    public function test_transporte_porto_alegre_disponivel_nos_dois_temas(): void
    {
        $supervisorV1 = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        $supervisorV2 = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        // Tema V1
        $responseV1 = $this->actingAs($supervisorV1)->get(route('rmas.logistica.frete-porto-alegre'));
        $responseV1->assertOk();
        $responseV1->assertViewIs('temas.v1.rma.logistica.frete-porto-alegre');
        $responseV1->assertSee('Transporte para Porto Alegre');

        // Tema V2
        $responseV2 = $this->actingAs($supervisorV2)->get(route('rmas.logistica.frete-porto-alegre'));
        $responseV2->assertOk();
        $responseV2->assertViewIs('temas.v2.rma.logistica.frete-porto-alegre');
        $responseV2->assertSee('Porto Alegre');

        // Rotas determinísticas QA
        $this->actingAs($supervisorV1)->get('/v1/logistica/porto-alegre')->assertOk();
        $this->actingAs($supervisorV2)->get('/v2/logistica/porto-alegre')->assertOk();
    }

    public function test_destinatarios_com_frete_e_cfop_exibidos_nos_dois_temas(): void
    {
        $supervisorV1 = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        $supervisorV2 = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $fornecedor = \App\Models\Fornecedor::factory()->create([
            'nome' => 'Distribuidora Sul',
            'cfop' => '5.102',
            'frete' => 'FOB',
        ]);

        // V1
        $resV1 = $this->actingAs($supervisorV1)->get(route('v1.parceiros.fornecedores.show', $fornecedor->id));
        $resV1->assertOk();
        $resV1->assertSee('5.102');
        $resV1->assertSee('FOB');

        // V2
        $resV2 = $this->actingAs($supervisorV2)->get(route('v2.parceiros.fornecedores.show', $fornecedor->id));
        $resV2->assertOk();
        $resV2->assertSee('5.102');
        $resV2->assertSee('FOB');
    }

    public function test_ajuda_e_procedimento_operacional_disponivel_e_estilizado_em_ambos_os_temas(): void
    {
        $supervisorV1 = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        $supervisorV2 = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        // V1
        $resV1 = $this->actingAs($supervisorV1)->get(route('rmas.ajuda'));
        $resV1->assertOk();
        $resV1->assertViewIs('temas.v1.rma.ajuda');
        $resV1->assertSee('Central de Ajuda');
        $resV1->assertSee('3 ETAPAS: Entrada, Processamento e Saída');

        // V2
        $resV2 = $this->actingAs($supervisorV2)->get(route('rmas.ajuda'));
        $resV2->assertOk();
        $resV2->assertViewIs('temas.v2.rma.ajuda');
        $resV2->assertSee('Procedimento Operacional de RMA');
        $resV2->assertSee('3 ETAPAS: Entrada, Processamento e Saída');

        // Rotas determinísticas QA
        $this->actingAs($supervisorV1)->get('/v1/ajuda')->assertOk();
        $this->actingAs($supervisorV2)->get('/v2/ajuda')->assertOk();
    }
}
