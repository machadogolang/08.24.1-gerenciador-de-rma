<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Paridade P1 - "Trocar p/ 15.8.1" no menu/session do TEMA V1 com POST/CSRF.
 */
class TrocarTemaMenuV1Test extends TestCase
{
    use RefreshDatabase;

    public static function papeisProvider(): array
    {
        return [
            'operador' => [Papel::Operador],
            'supervisor' => [Papel::Supervisor],
            'super-administrador' => [Papel::SuperAdministrador],
        ];
    }

    #[DataProvider('papeisProvider')]
    public function test_item_trocar_tema_aparece_no_shell_v1(Papel $papel): void
    {
        $usuario = User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get('/perfil');

        $response->assertOk();
        $response->assertViewIs('temas.v1.identidade.perfil');
        $response->assertSee('Trocar p/ 15.8.1', false);
        $response->assertSee('tema/alternar', false);
        $response->assertSee('method="POST"', false);
        $response->assertSee('name="_token"', false);
    }

    #[DataProvider('papeisProvider')]
    public function test_troca_pelo_fluxo_canonico_persiste_tema_v2(Papel $papel): void
    {
        $usuario = User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->post('/tema/alternar');

        $response->assertRedirect();
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);
    }
}
