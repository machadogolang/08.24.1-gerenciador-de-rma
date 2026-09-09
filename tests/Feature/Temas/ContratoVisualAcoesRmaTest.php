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
 * FRONT-003/UI-02C — contrato visual nas superfícies de RMA.
 * Links de Ver/Editar permanecem `<a href>` (GET); nenhuma rota muda.
 */
class ContratoVisualAcoesRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_resultado_de_busca_do_tema_v1_usa_variante_compacta_em_ver_e_editar(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        Rma::factory()->create(['descricao' => 'RMA contrato acoes V1']);

        $response = $this->actingAs($usuario)
            ->get('/v1/rma?tipo=texto&valor=contrato')
            ->assertOk();

        $response->assertViewIs('temas.v1.rma.index');
        $response->assertSee('class="acao acao--secundaria acao--compacta">Ver</a>', false);
        $response->assertSee('class="acao acao--secundaria acao--compacta">Editar</a>', false);
    }

    public static function detalhePorTemaProvider(): array
    {
        return [
            'v1' => [TemaPreferido::V1],
            'v2' => [TemaPreferido::V2],
        ];
    }

    #[DataProvider('detalhePorTemaProvider')]
    public function test_detalhe_de_rma_aplica_papel_primario_ao_editar(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
        $rma = Rma::factory()->create(['descricao' => 'Detalhe contrato acoes']);

        $response = $this->actingAs($usuario)
            ->get("/{$tema->value}/rma/{$rma->id}")
            ->assertOk();

        $response->assertViewIs("temas.{$tema->value}.rma.show");
        $response->assertSee('class="acao acao--primaria', false);
        $response->assertSee('>Editar</a>', false);
        $response->assertSeeText('Detalhe contrato acoes');
    }
}
