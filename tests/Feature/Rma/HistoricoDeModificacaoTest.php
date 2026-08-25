<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\ModificacaoDeRma;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\AcaoDeModificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `LEG-RMA-044` — reproduz `subp/logs_de_modificacao.php`, exige
 * `Papel::podeGerenciarUsuarios()`.
 */
class HistoricoDeModificacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_ve_historico_de_modificacao(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $autor = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create();
        ModificacaoDeRma::create([
            'rma_id' => $rma->id,
            'user_id' => $autor->id,
            'acao' => AcaoDeModificacao::Criacao,
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'estado_apos' => ['descricao' => $rma->descricao],
        ]);

        $response = $this->actingAs($supervisor)->get('/rmas-historico');

        $response->assertOk();
        $response->assertSee("#{$rma->id}", false);
        $response->assertSee('Criacao', false);
        $response->assertSee($autor->name, false);
    }

    public function test_operador_nao_pode_ver_historico_de_modificacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->get('/rmas-historico');

        $response->assertForbidden();
    }

    public function test_visitante_nao_autenticado_e_redirecionado_ao_login(): void
    {
        $response = $this->get('/rmas-historico');

        $response->assertRedirect('/login');
    }
}
