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
 * FRONT-003/UI-02C - contrato em formulários, crédito e identidade.
 * Só inconsistências reais foram alteradas; componentes históricos (buttonSave,
 * formSubmit, formButtonEnviarPanel) permanecem como fidelidade de tema.
 */
class ContratoVisualAcoesFormulariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_v1_marca_alternar_tema_como_acao_secundaria(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/perfil')->assertOk();

        $response->assertSee('class="acao acao--secundaria">Alternar tema', false);
        $response->assertSeeText('atual: v1');
    }

    public function test_formulario_de_parceiro_v1_marca_voltar_como_acao_secundaria(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)
            ->get('/v1/parceiros/fornecedores/create')
            ->assertOk();

        $response->assertSee('class="acao acao--secundaria">Voltar</a>', false);
        $response->assertSee('class="buttonSave">Salvar</button>', false);
    }

    public function test_formulario_de_rma_v1_marca_voltar_como_acao_secundaria(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        $rma = Rma::factory()->create(['descricao' => 'Edicao contrato V1']);

        $response = $this->actingAs($usuario)
            ->get("/v1/rma/{$rma->id}/edit")
            ->assertOk();

        $response->assertSee('class="acao acao--secundaria">Voltar</a>', false);
    }

    public static function creditoPorTemaProvider(): array
    {
        return [
            'v1' => [TemaPreferido::V1],
            'v2' => [TemaPreferido::V2],
        ];
    }

    #[DataProvider('creditoPorTemaProvider')]
    public function test_credito_marca_acao_primaria_em_ambos_os_temas(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);

        $response = $this->actingAs($usuario)->get('/rmas-credito')->assertOk();

        $response->assertSee('class="acao acao--primaria">Marcar crédito disponível</button>', false);
    }

    public function test_aba_novo_rma_do_tema_v2_marca_acao_primaria(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma')->assertOk();

        $response->assertSee('class="acao acao--primaria btn formSubmit">Abrir novo RMA</a>', false);
    }
}
