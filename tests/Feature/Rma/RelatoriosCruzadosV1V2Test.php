<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Fabricante;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UF-08 e UF-09 - Unificacao Funcional de Relatorios cruzados entre Temas V1 e V2:
 * - UF-08 (GAP-V2-01..03): RCD, RPEC e RMPE acessiveis e descobriveis no Tema V2.
 * - UF-09 (GAP-V1-06): Hub Estatistico de Relatorios acessivel e adaptado ao Tema V1.
 */
class RelatoriosCruzadosV1V2Test extends TestCase
{
    use RefreshDatabase;

    public function test_tema_v2_relatorios_hub_apresenta_navegacao_descobrivel_para_rcd_rpec_rmpe(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.index'));

        $response->assertOk();
        $response->assertSee('Relatorios');
        $response->assertSee('Estatisticas Gerais');
        $response->assertSee('RCD (Creditos)');
        $response->assertSee('RPEC (Estoque)');
        $response->assertSee('RMPE (Movimentacao)');
    }

    public function test_tema_v2_acessa_rcd_rpec_e_rmpe_com_layout_v2_e_menu_de_navegacao(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $fabricante = Fabricante::factory()->create();

        Rma::factory()->create([
            'status' => Status::Concluido,
            'solucao' => Solucao::GeradoCredito,
            'credito_disponivel' => true,
            'fabricante_id' => $fabricante->id,
        ]);

        // RCD sob Tema V2
        $respRcd = $this->actingAs($usuario)->get(route('rmas.relatorios.rcd'));
        $respRcd->assertOk();
        $respRcd->assertSee('RCD (Creditos)');
        $respRcd->assertSee('Estatisticas Gerais');

        // RPEC sob Tema V2
        $respRpec = $this->actingAs($usuario)->get(route('rmas.relatorios.rpec'));
        $respRpec->assertOk();
        $respRpec->assertSee('RPEC (Estoque)');
        $respRpec->assertSee('Estatisticas Gerais');

        // RMPE sob Tema V2
        $respRmpe = $this->actingAs($usuario)->get(route('rmas.relatorios.rmpe'));
        $respRmpe->assertOk();
        $respRmpe->assertSee('RMPE (Movimentacao)');
        $respRmpe->assertSee('Estatisticas Gerais');
    }

    public function test_tema_v1_relatorios_hub_estatistico_apresenta_layout_e_tabelas_v1(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.index'));

        $response->assertOk();
        $response->assertSee('Resumo estatistico e metricas operacionais do sistema');
        $response->assertSee('SITUACAO');
        $response->assertSee('DADOS DO SISTEMA');
        $response->assertSee('RESOLUCAO');
        $response->assertSee('ORIGEM');
        $response->assertSee('DADOS RELACIONADOS A NF');
        $response->assertSee('TOP FORNECEDORES');
        $response->assertSee('images/tema-v1/bd.png');
    }

    public function test_tema_v1_menu_sessao_contem_link_para_estatisticas_gerais(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.index'));

        $response->assertOk();
        $response->assertSee(route('rmas.relatorios.index'));
        $response->assertSee('Estatísticas Gerais');
    }

    public function test_urls_qa_deterministicas_v1_e_v2_respondem_200(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $this->actingAs($usuario)->get('/v1/relatorios')->assertOk();
        $this->actingAs($usuario)->get('/v1/relatorios/rcd')->assertOk();
        $this->actingAs($usuario)->get('/v1/relatorios/rpec')->assertOk();
        $this->actingAs($usuario)->get('/v1/relatorios/rmpe')->assertOk();

        $this->actingAs($usuario)->get('/v2/relatorios')->assertOk();
        $this->actingAs($usuario)->get('/v2/relatorios/rcd')->assertOk();
        $this->actingAs($usuario)->get('/v2/relatorios/rpec')->assertOk();
        $this->actingAs($usuario)->get('/v2/relatorios/rmpe')->assertOk();
    }
}
