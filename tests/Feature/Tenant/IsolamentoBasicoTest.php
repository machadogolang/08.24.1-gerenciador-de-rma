<?php

namespace Tests\Feature\Tenant;

use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Compartilhado\Uf;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsolamentoBasicoTest extends TestCase
{
    use RefreshDatabase;

    private function criarClientePara(Company $empresa, string $nome): Cliente
    {
        $cliente = Cliente::factory()->make(['nome' => $nome]);
        $cliente->forceFill(['tenant_id' => $empresa->id])->save();

        return $cliente;
    }

    public function test_escopo_global_so_lista_empresa_do_contexto(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);

        $clienteA = $this->criarClientePara($cell, 'Cliente da A');
        $clienteB = $this->criarClientePara($empresaB, 'Cliente da B');

        app(ContextoDeTenant::class)->definir($cell);
        $idsComContextoA = Cliente::query()->pluck('id')->all();
        $this->assertContains($clienteA->id, $idsComContextoA);
        $this->assertNotContains($clienteB->id, $idsComContextoA);

        app(ContextoDeTenant::class)->definir($empresaB);
        $idsComContextoB = Cliente::query()->pluck('id')->all();
        $this->assertContains($clienteB->id, $idsComContextoB);
        $this->assertNotContains($clienteA->id, $idsComContextoB);
    }

    public function test_observer_preenche_tenant_na_criacao_pelo_contexto(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        app(ContextoDeTenant::class)->definir($cell);

        $cliente = Cliente::query()->create([
            'nome' => 'Cliente do observer',
            'uf' => Uf::RS,
        ]);

        $this->assertSame($cell->id, $cliente->tenant_id);
    }

    public function test_tenant_id_nao_e_mass_assignable_pelo_request(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        app(ContextoDeTenant::class)->definir($cell);

        $cliente = Cliente::query()->create([
            'nome' => 'Tentativa de spoofing',
            'tenant_id' => $empresaB->id,
        ]);

        $this->assertSame($cell->id, $cliente->tenant_id);
        $this->assertNotSame($empresaB->id, $cliente->tenant_id);
    }

    public function test_route_binding_rejeita_registro_de_outra_empresa(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $clienteB = $this->criarClientePara($empresaB, 'Cliente sigiloso B');
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/parceiros/clientes/'.$clienteB->id.'/edit')
            ->assertNotFound();
    }
}
