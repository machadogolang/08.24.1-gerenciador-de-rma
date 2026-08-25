<?php

namespace Tests\Feature\Parceiros;

use App\Models\Cliente;
use App\Parceiros\Aplicacao\EncontrarOuCriarCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncontrarOuCriarClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_nome_novo_cria_cliente(): void
    {
        $caso = new EncontrarOuCriarCliente();

        $cliente = $caso->encontrarOuCriar('Cliente Novo');

        $this->assertTrue($cliente->wasRecentlyCreated);
        $this->assertSame('Cliente Novo', $cliente->nome);
        $this->assertDatabaseCount('clientes', 1);
    }

    public function test_nome_exatamente_igual_reaproveita(): void
    {
        $existente = Cliente::factory()->create(['nome' => 'Cliente Existente']);
        $caso = new EncontrarOuCriarCliente();

        $cliente = $caso->encontrarOuCriar('Cliente Existente');

        $this->assertSame($existente->id, $cliente->id);
        $this->assertDatabaseCount('clientes', 1);
    }

    public function test_nome_com_espaco_duplo_e_maiuscula_diferente_reaproveita(): void
    {
        $existente = Cliente::factory()->create(['nome' => 'Cliente Duplicado']);
        $caso = new EncontrarOuCriarCliente();

        $cliente = $caso->encontrarOuCriar('  cliente   DUPLICADO  ');

        $this->assertSame($existente->id, $cliente->id);
        $this->assertDatabaseCount('clientes', 1);
    }

    public function test_nome_de_outro_cliente_nao_colide(): void
    {
        Cliente::factory()->create(['nome' => 'Cliente A']);
        $caso = new EncontrarOuCriarCliente();

        $cliente = $caso->encontrarOuCriar('Cliente B');

        $this->assertNotSame('Cliente A', $cliente->nome);
        $this->assertSame('Cliente B', $cliente->nome);
        $this->assertDatabaseCount('clientes', 2);
    }
}
