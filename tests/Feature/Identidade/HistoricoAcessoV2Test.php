<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-AUD-004 - logs de autenticacao do TEMA V2 no contrato do Legacy
 * `subp/logs_de_autenticacao.php`, incluindo SISTEMA OPERACIONAL e APP preservados
 * como campos historicos (`sistema_operacional_legado`/`app_legado`).
 */
class HistoricoAcessoV2Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function logs_de_autenticacao_v2_mostram_o_contrato_do_legacy(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $tentativa = new TentativaDeAcesso([
            'user_id' => $admin->id,
            'email_informado' => 'qa-v2@teste.local',
            'ip' => '10.0.0.7',
            'user_agent' => 'Chrome QA',
            'resultado' => ResultadoDeAcesso::Permitido,
            'sistema_operacional_legado' => 'Windows 10',
            'app_legado' => '15.8.1',
        ]);
        $tentativa->save();

        $response = $this->actingAs($admin)->get('/v2/historico-de-acesso');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.historico-de-acesso.index');
        $response->assertSeeText('SISTEMA OPERACIONAL');
        $response->assertSeeText('RETORNO');
        $response->assertSeeText('Quantidade retornada');
        $response->assertSeeText('Windows 10');
        $response->assertSeeText('15.8.1');
        $response->assertSeeText('Chrome QA');
        $response->assertSeeText('10.0.0.7');
        $response->assertSeeText('Logs de autenticacao');
    }
}
