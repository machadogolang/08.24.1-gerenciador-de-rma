<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FornecedorCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_lista_fornecedores(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        Fornecedor::factory()->create(['nome' => 'Fornecedor Um']);

        $response = $this->actingAs($operador)->get('/parceiros/fornecedores');

        $response->assertOk();
        $response->assertSeeText('Fornecedor Um');
    }

    public function test_operador_cria_fornecedor(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/parceiros/fornecedores', [
            'nome' => 'Novo Fornecedor',
            'uf' => 'MG',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fornecedores', ['nome' => 'Novo Fornecedor', 'uf' => 'MG']);
    }

    public function test_operador_edita_fornecedor(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Antigo']);

        $response = $this->actingAs($operador)->put("/parceiros/fornecedores/{$fornecedor->id}", [
            'nome' => 'Atualizado',
        ]);

        $response->assertRedirect();
        $this->assertSame('Atualizado', $fornecedor->fresh()->nome);
    }

    public function test_operador_remove_fornecedor(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actingAs($operador)->delete("/parceiros/fornecedores/{$fornecedor->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('fornecedores', ['id' => $fornecedor->id]);
    }

    public function test_usuario_de_leitura_nao_pode_criar_fornecedor(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($leitura)->post('/parceiros/fornecedores', [
            'nome' => 'Não deveria existir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('fornecedores', ['nome' => 'Não deveria existir']);
    }
}
