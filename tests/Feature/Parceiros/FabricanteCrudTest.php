<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Models\Fabricante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FabricanteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_lista_fabricantes(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        Fabricante::factory()->create(['nome' => 'Fabricante Um']);

        $response = $this->actingAs($operador)->get('/parceiros/fabricantes');

        $response->assertOk();
        $response->assertSeeText('Fabricante Um');
    }

    public function test_operador_cria_fabricante(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/parceiros/fabricantes', [
            'nome' => 'Novo Fabricante',
            'uf' => 'RJ',
            'cfop' => '5.102',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fabricantes', ['nome' => 'Novo Fabricante', 'uf' => 'RJ', 'cfop' => '5.102']);
    }

    public function test_operador_edita_fabricante(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Antigo']);

        $response = $this->actingAs($operador)->put("/parceiros/fabricantes/{$fabricante->id}", [
            'nome' => 'Atualizado',
        ]);

        $response->assertRedirect();
        $this->assertSame('Atualizado', $fabricante->fresh()->nome);
    }

    public function test_operador_remove_fabricante(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create();

        $response = $this->actingAs($operador)->delete("/parceiros/fabricantes/{$fabricante->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('fabricantes', ['id' => $fabricante->id]);
    }

    public function test_usuario_de_leitura_nao_pode_criar_fabricante(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($leitura)->post('/parceiros/fabricantes', [
            'nome' => 'Não deveria existir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('fabricantes', ['nome' => 'Não deveria existir']);
    }
}
