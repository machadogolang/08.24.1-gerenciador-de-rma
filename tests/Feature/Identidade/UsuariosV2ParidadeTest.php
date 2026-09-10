<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PAR15-USR-001 - a tela de usuarios do TEMA V2 volta a organizacao historica do
 * Legacy `15.8.1/subp/usuarios.php`: colunas Nome/E-mail/QT Login/Ultimo login/
 * Permissao/Acao e 3 acoes compactas por icone, com superficies V2 dedicadas.
 *
 * `QT Login`/`Ultimo login` sao projecao de `tentativas_de_acesso` (PAR15-DATA-001/002),
 * nunca coluna duplicada - o teste prova a semantica (so tentativas permitidas contam).
 */
class UsuariosV2ParidadeTest extends TestCase
{
    use RefreshDatabase;

    private function registrarTentativa(User $usuario, ResultadoDeAcesso $resultado, string $data): void
    {
        $tentativa = new TentativaDeAcesso;
        $tentativa->forceFill([
            'user_id' => $usuario->id,
            'email_informado' => $usuario->email,
            'ip' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'resultado' => $resultado,
            'created_at' => $data,
            'updated_at' => $data,
        ]);
        $tentativa->save();
    }

    public function test_listagem_v2_restaura_colunas_historicas_e_acoes_compactas(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor, 'tema_preferido' => TemaPreferido::V2]);
        User::factory()->create(['papel' => Papel::Leitura, 'name' => 'Alvo Um', 'email' => 'alvo@example.com']);

        $response = $this->actingAs($supervisor)->get('/v2/usuarios');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.usuarios');
        $response->assertSeeText('QT Login');
        $response->assertSeeText('Ultimo login');
        $response->assertSeeText('Permissao');
        $response->assertSeeText('Acao');
        $response->assertSeeText('Alvo Um');
        $response->assertSee('images/rma/senha2.png', false);
        $response->assertSee('images/rma/permissao3.png', false);
        $response->assertSee('images/rma/apagar.png', false);
    }

    public function test_qt_login_e_ultimo_login_derivam_apenas_de_acessos_permitidos(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor, 'tema_preferido' => TemaPreferido::V2]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura, 'name' => 'Alvo Derivado']);

        $this->registrarTentativa($alvo, ResultadoDeAcesso::Permitido, '2016-03-05 08:30:00');
        $this->registrarTentativa($alvo, ResultadoDeAcesso::Permitido, '2016-04-10 09:00:00');
        $this->registrarTentativa($alvo, ResultadoDeAcesso::Negado, '2017-01-01 10:00:00');

        $response = $this->actingAs($supervisor)->get('/v2/usuarios');

        $response->assertOk();
        // Ultimo login permitido: 10/04/2016 (o negado de 2017 nao conta).
        $response->assertSeeText('10/04/2016');
        $response->assertDontSeeText('01/01/2017');
    }

    public function test_superficies_dedicadas_v2_renderizam(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor, 'tema_preferido' => TemaPreferido::V2]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura]);

        $this->actingAs($supervisor)->get("/v2/usuarios/{$alvo->id}/permissoes")
            ->assertOk()
            ->assertViewIs('temas.v2.identidade.usuarios-permissoes');

        $this->actingAs($supervisor)->get("/v2/usuarios/{$alvo->id}/resetar-senha")
            ->assertOk()
            ->assertViewIs('temas.v2.identidade.usuarios-resetar-senha');

        $this->actingAs($supervisor)->get("/v2/usuarios/{$alvo->id}/apagar")
            ->assertOk()
            ->assertViewIs('temas.v2.identidade.usuarios-apagar');
    }

    public function test_superficies_dedicadas_sao_exclusivas_do_tema_v2(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor, 'tema_preferido' => TemaPreferido::V1]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura]);

        $this->actingAs($supervisor)->get("/usuarios/{$alvo->id}/permissoes")
            ->assertRedirect(route('identidade.usuarios.index'));
    }

    public function test_operador_nao_acessa_superficie_dedicada(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V2]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura]);

        $this->actingAs($operador)->get("/v2/usuarios/{$alvo->id}/permissoes")->assertForbidden();
    }
}
