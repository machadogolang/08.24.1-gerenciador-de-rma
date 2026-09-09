<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FRONT-003/UI-02C - prova semântica do contrato visual de ações nos dois temas.
 * A prova visual/computed style fica no Playwright (UI-02D); aqui garantimos que o
 * HTML renderizado carrega o papel certo sem mudar rotas/CSRF/método.
 */
class ContratoVisualAcoesParceirosTest extends TestCase
{
    use RefreshDatabase;

    public static function superficieParceirosProvider(): array
    {
        $temas = [TemaPreferido::V1, TemaPreferido::V2];
        $tipos = [
            'clientes' => Cliente::class,
            'fabricantes' => Fabricante::class,
            'fornecedores' => Fornecedor::class,
            'assistencias-tecnicas' => AssistenciaTecnica::class,
        ];

        $casos = [];
        foreach ($temas as $tema) {
            foreach ($tipos as $tipo => $modelo) {
                $casos["{$tema->value}-{$tipo}"] = [$tema, $tipo, $modelo];
            }
        }

        return $casos;
    }

    #[DataProvider('superficieParceirosProvider')]
    public function test_listagem_de_parceiros_aplica_contrato_de_acoes(
        TemaPreferido $tema,
        string $tipo,
        string $modelo,
    ): void {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
        $modelo::factory()->create(['nome' => 'Parceiro Contrato QA']);

        $response = $this->actingAs($usuario)
            ->get("/{$tema->value}/parceiros/{$tipo}")
            ->assertOk();

        $response->assertViewIs("temas.{$tema->value}.parceiros.index");
        $response->assertSee('class="acao acao--primaria">Novo</a>', false);
        $response->assertSee('class="acao acao--secundaria acao--compacta">Editar</a>', false);
        $response->assertSee('class="acao acao--perigo acao--compacta">Remover</button>', false);

        // Semântica preservada: mutação continua `<button>` em POST com CSRF e DELETE.
        $response->assertSee('method="POST"', false);
        $response->assertSee('name="_token"', false);
        $response->assertSee('name="_method" value="DELETE"', false);
        $response->assertSeeText('Parceiro Contrato QA');
    }
}
