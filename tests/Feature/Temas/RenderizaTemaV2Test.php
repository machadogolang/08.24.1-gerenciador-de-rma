<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Cliente;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 8 — smoke: cada tela principal renderiza sem erro no TEMA V2, tanto pela rota
 * prefixada (`/v2/...`) quanto pelo fluxo normal (usuário com `tema_preferido` = V2).
 * O painel de RMAs (`temas/v2/rma/index.blade.php`) é o único com os 7 tab-panes —
 * confirma que todos aparecem no mesmo HTML (ver design.md "Mecanismo de navegação
 * por tema").
 */
class RenderizaTemaV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_com_tema_v2_e_redirecionado_para_view_do_tema_v2_apos_login(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($usuario)->get('/usuarios');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.usuarios');
    }

    public function test_painel_de_rmas_v2_renderiza_com_os_7_tab_panes(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create(['descricao' => 'RMA tema V2']);

        $response = $this->actingAs($usuario)->get('/v2/rma?tipo=texto&valor=RMA');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.index');
        $response->assertSeeText('RMA tema V2');

        foreach (['inicio', 'pesquisar', 'novo_rma', 'entrada', 'recebido', 'encaminhado', 'concluido'] as $painel) {
            $response->assertSee('id="' . $painel . '"', false);
        }
    }

    public function test_novo_rma_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($usuario)->get('/v2/rma/create');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.create');
    }

    public function test_detalhe_de_rma_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create(['descricao' => 'Detalhe tema V2']);

        $response = $this->actingAs($usuario)->get("/v2/rma/{$rma->id}");

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.show');
        $response->assertSeeText('Detalhe tema V2');
    }

    public function test_clientes_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Cliente::factory()->create(['nome' => 'Cliente tema V2']);

        $response = $this->actingAs($usuario)->get('/v2/parceiros/clientes');

        $response->assertOk();
        $response->assertViewIs('temas.v2.parceiros.index');
        $response->assertSeeText('Cliente tema V2');
    }

    public function test_perfil_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador, 'anotacao' => 'Nota V2']);

        $response = $this->actingAs($usuario)->get('/v2/perfil');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.perfil');
        $response->assertSeeText('Nota V2');
    }
}
