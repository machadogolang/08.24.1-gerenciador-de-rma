<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RmaIsolamentoTest extends TestCase
{
    use RefreshDatabase;

    private function criarRmaNaEmpresa(Company $empresa, string $descricao): RmaEloquent
    {
        $rma = RmaEloquent::factory()->make(['descricao' => $descricao]);
        $rma->forceFill(['tenant_id' => $empresa->id])->save();

        return $rma;
    }

    public function test_empresa_a_nao_lista_rma_da_empresa_b(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de RMA']);
        $rmaB = $this->criarRmaNaEmpresa($empresaB, 'Defeito sigiloso da empresa B');
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/rmas-entrada')
            ->assertOk()
            ->assertDontSee($rmaB->descricao);
    }

    public function test_empresa_a_nao_abre_edicao_de_rma_da_empresa_b(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de RMA']);
        $rmaB = $this->criarRmaNaEmpresa($empresaB, 'Defeito sigiloso da empresa B');
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/rmas/'.$rmaB->id.'/edit')
            ->assertNotFound();
    }

    public function test_empresa_a_nao_recebe_rma_da_empresa_b(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de RMA']);
        $rmaB = $this->criarRmaNaEmpresa($empresaB, 'Defeito sigiloso da empresa B');
        $usuario = User::factory()->create(['papel' => \App\Identidade\Dominio\Papel::Operador]);

        $this->actingAs($usuario)
            ->post('/rmas/'.$rmaB->id.'/receber')
            ->assertNotFound();
    }

    public function test_empresa_a_cria_rma_no_proprio_tenant(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post('/rmas', [
            'descricao' => 'Defeito criado pela empresa A',
            'defeito' => 'Criado em teste',
            'cliente_nome' => 'Cliente da empresa A',
        ])->assertRedirect();

        $rma = RmaEloquent::query()->withoutGlobalScopes()
            ->where('descricao', 'Defeito criado pela empresa A')
            ->sole();

        $this->assertSame($cell->id, $rma->tenant_id);
    }
}
