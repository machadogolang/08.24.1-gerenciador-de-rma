<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T3-16 - Secundarias no Tema V3:
 * Alertas, historico de modificacoes, historico de acesso, logistica, ajuda, perfil e creditos.
 */
class SecundariasV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    public function test_painel_de_alertas_v3_renderiza(): void
    {
        $usuario = $this->usuario();
        Rma::factory()->create([
            'status' => Status::Entrada,
            'prioridade' => Prioridade::Alta,
            'descricao' => 'RMA Alerta V3 QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v3/alertas');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.alertas.index');
        $response->assertSeeText('Painel de Alertas');
        $response->assertSeeText('RMA Alerta V3 QA');
    }

    public function test_historico_de_modificacao_v3_renderiza(): void
    {
        $usuario = $this->usuario();
        $rma = Rma::factory()->create();
        $modificacao = new \App\Models\ModificacaoDeRma([
            'rma_id' => $rma->id,
            'user_id' => $usuario->id,
            'acao' => \App\Rma\Dominio\AcaoDeModificacao::Criacao,
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'estado_anterior' => [],
            'estado_apos' => ['descricao' => 'RMA modificado V3 QA'],
        ]);
        $modificacao->tenant_id = $rma->tenant_id;
        $modificacao->save();

        $response = $this->actingAs($usuario)->get('/v3/rmas-historico');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.historico.index');
        $response->assertSeeText('Histórico de Modificações de RMA');
        $response->assertSee('data-tabela-skinless="true"', false);
    }

    public function test_historico_de_acesso_v3_renderiza(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)->get('/v3/historico-de-acesso');

        $response->assertOk();
        $response->assertViewIs('temas.v3.identidade.historico-de-acesso.index');
        $response->assertSeeText('Histórico de Acesso');
    }

    public function test_logistica_porto_alegre_v3_renderiza(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)->get('/v3/logistica/porto-alegre');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.logistica.frete-porto-alegre');
        $response->assertSeeText('Transporte para Porto Alegre');
    }

    public function test_ajuda_procedimentos_v3_renderiza(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)->get('/v3/ajuda');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.ajuda');
        $response->assertSeeText('Central de Ajuda e Procedimentos');
    }

    public function test_perfil_v3_renderiza(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)->get('/v3/perfil');

        $response->assertOk();
        $response->assertViewIs('temas.v3.identidade.perfil');
        $response->assertSeeText('Meu Perfil');
        $response->assertSeeText($usuario->name);
    }

    public function test_creditos_v3_renderiza(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)->get('/v3/creditos');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.credito.index');
        $response->assertSeeText('Gestão de Créditos');
    }
}
