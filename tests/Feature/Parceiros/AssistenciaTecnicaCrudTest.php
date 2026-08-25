<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistenciaTecnicaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_lista_assistencias_tecnicas(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        AssistenciaTecnica::factory()->create(['nome' => 'Assistência Um']);

        $response = $this->actingAs($operador)->get('/parceiros/assistencias-tecnicas');

        $response->assertOk();
        $response->assertSeeText('Assistência Um');
    }

    public function test_operador_cria_assistencia_tecnica(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/parceiros/assistencias-tecnicas', [
            'nome' => 'Nova Assistência',
            'uf' => 'PR',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assistencias_tecnicas', ['nome' => 'Nova Assistência', 'uf' => 'PR']);
    }

    public function test_operador_edita_assistencia_tecnica(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $assistencia = AssistenciaTecnica::factory()->create(['nome' => 'Antigo']);

        $response = $this->actingAs($operador)->put("/parceiros/assistencias-tecnicas/{$assistencia->id}", [
            'nome' => 'Atualizado',
        ]);

        $response->assertRedirect();
        $this->assertSame('Atualizado', $assistencia->fresh()->nome);
    }

    public function test_operador_remove_assistencia_tecnica(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $assistencia = AssistenciaTecnica::factory()->create();

        $response = $this->actingAs($operador)->delete("/parceiros/assistencias-tecnicas/{$assistencia->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('assistencias_tecnicas', ['id' => $assistencia->id]);
    }

    public function test_usuario_de_leitura_nao_pode_criar_assistencia_tecnica(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($leitura)->post('/parceiros/assistencias-tecnicas', [
            'nome' => 'Não deveria existir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('assistencias_tecnicas', ['nome' => 'Não deveria existir']);
    }
}
