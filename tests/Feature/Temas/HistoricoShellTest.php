<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FRONT-003/UI-04 - históricos de RMA e de acesso no shell V1/V2.
 */
class HistoricoShellTest extends TestCase
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
    public function test_historico_de_modificacoes_renderiza_no_shell_do_tema(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => $tema,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.historico.index'));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.historico.index");
        $response->assertSeeText('Histórico de modificações de RMA');
        $response->assertSee('class="historico-tabela"', false);
    }

    #[DataProvider('temasProvider')]
    public function test_historico_de_acesso_renderiza_no_shell_do_tema(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => $tema,
        ]);

        $response = $this->actingAs($usuario)->get(route('identidade.historico-de-acesso.index'));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.identidade.historico-de-acesso.index");
        $response->assertSeeText('Histórico de acesso');
        $response->assertSee('class="historico-tabela"', false);
    }
}
