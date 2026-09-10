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
 * PAR14-USR-001 - a lista de usuarios do TEMA V1 volta ao contrato do Legacy
 * `14.6.1/menujs-right/usuarios.php`: NOME, ENDERECO DE E-MAIL, PERMISSAO e N LOGIN,
 * sem os controles inline que existiam no V2. As acoes administrativas continuam no
 * Controle do V1.
 */
class UsuariosV1ParidadeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function listagem_v1_usa_as_colunas_historicas_sem_controles_inline(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura, 'name' => 'Alvo V1', 'email' => 'alvo-v1@teste.local']);

        $tentativa = new TentativaDeAcesso([
            'user_id' => $alvo->id,
            'email_informado' => $alvo->email,
            'ip' => '10.0.0.5',
            'user_agent' => 'Chrome QA',
            'resultado' => ResultadoDeAcesso::Permitido,
        ]);
        $tentativa->save();

        $response = $this->actingAs($admin)->get('/v1/usuarios');

        $response->assertOk();
        $response->assertViewIs('temas.v1.identidade.usuarios');
        $response->assertSeeText('NOME');
        $response->assertSeeText('ENDERECO DE E-MAIL');
        $response->assertSeeText('PERMISSAO');
        $response->assertSeeText('N LOGIN');
        $response->assertSeeText('Alvo V1');
        $response->assertSeeText('Leitura');

        // N LOGIN projetado da auditoria.
        $response->assertSee('>1<', false);

        // Nada de controles inline do V2 nesta tela.
        $response->assertDontSee('name="papel"', false);
        $response->assertDontSee('name="nova_senha"', false);
    }
}
