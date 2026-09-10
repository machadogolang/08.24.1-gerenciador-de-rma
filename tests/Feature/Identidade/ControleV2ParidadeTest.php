<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\ModificacaoDeRma;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\AcaoDeModificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-AUD-001..003/005 - o TEMA V2 volta ao contrato de Controle do Legacy
 * `15.8.1/page/controle.php` (+ `inc/menu_controle.php`): menu/breadcrumb de 5 itens
 * e "Logs de modificacao" com as colunas historicas projetadas de
 * `estado_apos`/`user_agent` e a acao Ver.
 */
class ControleV2ParidadeTest extends TestCase
{
    use RefreshDatabase;

    private function adminV2(): User
    {
        return User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    #[Test]
    public function hub_controle_v2_mostra_o_menu_historico(): void
    {
        $response = $this->actingAs($this->adminV2())->get('/v2/controle');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.controle');
        $response->assertSeeText('Logs de autenticacao');
        $response->assertSeeText('Logs de modificacao');
        $response->assertSeeText('Alterar senha');
        $response->assertSeeText('Usuarios');
    }

    #[Test]
    public function tema_v1_no_controle_e_redirecionado_para_o_controle_proprio(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $this->actingAs($admin)->get('/controle')->assertRedirect(route('rmas.controle.index'));
    }

    #[Test]
    public function logs_de_modificacao_v2_projetam_as_colunas_do_legacy(): void
    {
        $admin = $this->adminV2();
        $rma = Rma::factory()->create(['descricao' => 'Descricao atual', 'numero_legado' => '4242']);

        $modificacao = new ModificacaoDeRma([
            'rma_id' => $rma->id,
            'user_id' => $admin->id,
            'acao' => AcaoDeModificacao::Edicao,
            'ip' => '10.0.0.9',
            'user_agent' => 'Chrome QA',
            'estado_apos' => [
                'fabricante' => 'Fabricante Historico',
                'descricao' => 'Descricao Historica',
                'modelo' => 'Modelo Historico',
            ],
        ]);
        $modificacao->tenant_id = $rma->tenant_id;
        $modificacao->save();

        $response = $this->actingAs($admin)->get('/rmas-historico');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.historico.index');
        $response->assertSeeText('BD NUMERO');
        $response->assertSeeText('NAVEGADOR');
        $response->assertSeeText('4242');
        $response->assertSeeText('Fabricante Historico');
        $response->assertSeeText('Descricao Historica');
        $response->assertSeeText('Modelo Historico');
        $response->assertSeeText('Chrome QA');
        // Acao Ver leva ao detalhe do RMA (equivalente moderno de `info/{numero}`).
        $response->assertSee(route('rmas.show', ['rma' => $rma->id]), false);
    }
}
