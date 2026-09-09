<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * P4 - RPEC/RMPE descobríveis nos menus dos dois temas, além do RCD.
 */
class DescobribilidadeRelatoriosTest extends TestCase
{
    use RefreshDatabase;

    public static function temasProvider(): array
    {
        return [
            'v1' => [TemaPreferido::V1],
            'v2' => [TemaPreferido::V2],
        ];
    }

    #[DataProvider('temasProvider')]
    public function test_relatorios_rpec_e_rmpe_aparecem_no_menu(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);

        $response = $this->actingAs($usuario)->get('/parceiros/fornecedores');

        $response->assertOk();
        $response->assertSee(route('rmas.relatorios.rcd'), false);
        $response->assertSee(route('rmas.relatorios.rpec'), false);
        $response->assertSee(route('rmas.relatorios.rmpe'), false);
        $response->assertSee('RPEC', false);
        $response->assertSee('RMPE', false);
    }
}
