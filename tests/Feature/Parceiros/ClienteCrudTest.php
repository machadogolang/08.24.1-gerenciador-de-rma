<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_lista_clientes(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        Cliente::factory()->create(['nome' => 'Cliente Um']);

        $response = $this->actingAs($operador)->get('/parceiros/clientes');

        $response->assertOk();
        $response->assertSeeText('Cliente Um');
    }

    public function test_operador_cria_cliente(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/parceiros/clientes', [
            'nome' => 'Novo Cliente',
            'uf' => 'SP',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', ['nome' => 'Novo Cliente', 'uf' => 'SP']);
    }

    public function test_operador_edita_cliente(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $cliente = Cliente::factory()->create(['nome' => 'Antigo']);

        $response = $this->actingAs($operador)->put("/parceiros/clientes/{$cliente->id}", [
            'nome' => 'Atualizado',
        ]);

        $response->assertRedirect();
        $this->assertSame('Atualizado', $cliente->fresh()->nome);
    }

    public function test_operador_remove_cliente(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($operador)->delete("/parceiros/clientes/{$cliente->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_usuario_de_leitura_nao_pode_criar_cliente(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($leitura)->post('/parceiros/clientes', [
            'nome' => 'Não deveria existir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('clientes', ['nome' => 'Não deveria existir']);
    }

    public function test_usuario_de_leitura_ainda_pode_listar_clientes(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        Cliente::factory()->create(['nome' => 'Visível']);

        $response = $this->actingAs($leitura)->get('/parceiros/clientes');

        $response->assertOk();
        $response->assertSeeText('Visível');
    }
}
