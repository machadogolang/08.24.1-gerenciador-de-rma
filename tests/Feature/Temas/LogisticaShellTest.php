<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FRONT-003/UI-04 - logística (frete Porto Alegre e boletins relacionados) no shell.
 */
class LogisticaShellTest extends TestCase
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
    public function test_frete_porto_alegre_renderiza_no_shell_do_tema(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);

        $response = $this->actingAs($usuario)
            ->get(route('rmas.logistica.frete-porto-alegre'));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.logistica.frete-porto-alegre");
        $response->assertSee('class="logistica-tabela"', false);
        $response->assertSeeText('Frete consolidado - Porto Alegre');
    }

    #[DataProvider('temasProvider')]
    public function test_boletins_relacionados_renderizam_no_shell_do_tema(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
        $rma = Rma::factory()->create(['descricao' => 'RMA principal boletins shell']);

        $response = $this->actingAs($usuario)
            ->get(route('rmas.logistica.boletins-relacionados', $rma->id));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.logistica.boletins-relacionados");
        $response->assertSee('class="logistica-tabela"', false);
        $response->assertSee('class="acao acao--secundaria">Voltar ao RMA', false);
        $response->assertSeeText('Nenhum boletim relacionado.');
    }
}
