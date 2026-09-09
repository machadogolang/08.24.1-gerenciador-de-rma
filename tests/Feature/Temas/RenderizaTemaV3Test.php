<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Fabricante;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T3-08/09/10 - Tema V3 oculto renderiza apenas por prefixo `/v3`, sem alterar
 * preferencia de usuario e sem aparecer no seletor publico.
 */
class RenderizaTemaV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuarioV1(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    public function test_dashboard_v3_renderiza_pela_rota_oculta_e_nao_muda_preferencia(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get('/v3');

        $response->assertOk();
        $response->assertViewIs('temas.v3.dashboard.index');
        $response->assertSeeText('Dashboard');
        $response->assertSeeText('Filas operacionais');
        $this->assertSame(TemaPreferido::V1, $usuario->fresh()->tema_preferido);
    }

    public function test_listagem_v3_filtra_por_fila_e_renderiza_cartoes_mobile(): void
    {
        $usuario = $this->usuarioV1();
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante V3 QA']);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'fabricante_id' => $fabricante->id,
            'descricao' => 'RMA entrada V3 QA',
        ]);
        Rma::factory()->create([
            'status' => Status::Concluido,
            'fabricante_id' => $fabricante->id,
            'descricao' => 'RMA concluido V3 QA',
        ]);

        $todos = $this->actingAs($usuario)->get('/v3/rmas');
        $todos->assertOk();
        $todos->assertViewIs('temas.v3.rma.index');
        $todos->assertSeeText('RMA entrada V3 QA');
        $todos->assertSeeText('RMA concluido V3 QA');

        $entrada = $this->actingAs($usuario)->get('/v3/rmas?fila=entrada');
        $entrada->assertOk();
        $entrada->assertSeeText('RMA entrada V3 QA');
        $entrada->assertDontSeeText('RMA concluido V3 QA');
        $entrada->assertSee('class="cartoes-rma"', false);
    }

    public function test_listagem_v3_reusa_busca_existente(): void
    {
        $usuario = $this->usuarioV1();
        Rma::factory()->create(['descricao' => 'Serial V3-ABC-123']);
        Rma::factory()->create(['descricao' => 'Nao deve aparecer']);

        $response = $this->actingAs($usuario)->get('/v3/rmas?q=ABC-123');

        $response->assertOk();
        $response->assertSeeText('Serial V3-ABC-123');
        $response->assertDontSeeText('Nao deve aparecer');
    }

    public function test_v3_nao_aparece_no_seletor_publico_do_perfil(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get('/perfil');

        $response->assertOk();
        $response->assertDontSeeText('Tema V3');
        $response->assertDontSeeText('v3');
        $response->assertSeeText('Alternar tema');
    }

    public function test_detalhe_v3_renderiza_cabecalho_operacional_e_secoes(): void
    {
        $usuario = $this->usuarioV1();
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante detalhe V3']);
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'fabricante_id' => $fabricante->id,
            'descricao' => 'RMA detalhe V3 operacional',
            'modelo' => 'MODELO V3',
            'sn' => 'SN-V3-001',
            'nf_remessa' => 'NFREMESSA-V3',
            'rastreio_ida' => 'RASTREIO-V3',
        ]);

        $response = $this->actingAs($usuario)->get("/v3/rma/{$rma->id}");

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.show');
        $response->assertSeeText('RMA detalhe V3 operacional');
        $response->assertSeeText('Proxima acao');
        $response->assertSeeText('Receber');
        foreach ([
            'Resumo',
            'Produto',
            'Parceiros e origem',
            'Fiscal',
            'Destinatario e logistica',
            'Solucao e credito',
            'Historico e auditoria',
        ] as $secao) {
            $response->assertSeeText($secao);
        }
        $response->assertSeeText('MODELO V3');
        $response->assertSeeText('SN-V3-001');
        $response->assertSeeText('NFREMESSA-V3');
    }
}
