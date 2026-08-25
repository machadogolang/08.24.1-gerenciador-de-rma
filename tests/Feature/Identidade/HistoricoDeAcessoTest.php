<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `LEG-RMA-043` — o dado (`tentativas_de_acesso`) já existe desde a Fase 1, esta fase
 * só adiciona a tela de consulta. Mesma regra de autorização de
 * `HistoricoDeModificacaoTest` (`Papel::podeGerenciarUsuarios()`).
 */
class HistoricoDeAcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_ve_historico_de_acesso(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        TentativaDeAcesso::create([
            'user_id' => $supervisor->id,
            'email_informado' => $supervisor->email,
            'ip' => '10.0.0.1',
            'user_agent' => 'PHPUnit',
            'resultado' => ResultadoDeAcesso::Permitido,
        ]);

        $response = $this->actingAs($supervisor)->get('/historico-de-acesso');

        $response->assertOk();
        $response->assertSee($supervisor->email, false);
    }

    public function test_operador_nao_pode_ver_historico_de_acesso(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->get('/historico-de-acesso');

        $response->assertForbidden();
    }

    public function test_visitante_nao_autenticado_e_redirecionado_ao_login(): void
    {
        $response = $this->get('/historico-de-acesso');

        $response->assertRedirect('/login');
    }
}
