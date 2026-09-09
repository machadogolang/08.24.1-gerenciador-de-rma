<?php

namespace Tests\Feature\Tenant;

use App\Identidade\Dominio\Papel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_cria_empresa(): void
    {
        $empresa = Company::factory()->create(['nome' => 'CellSystem']);

        $this->assertDatabaseHas('companies', ['id' => $empresa->id, 'nome' => 'CellSystem', 'ativa' => true]);
    }

    public function test_usuario_participa_de_uma_empresa_com_papel_no_vinculo(): void
    {
        $empresa = Company::factory()->create();
        $usuario = User::factory()->create();

        $usuario->empresas()->attach($empresa, ['papel' => Papel::Operador, 'ativo' => true]);

        $vinculo = $usuario->empresas()->first();

        $this->assertTrue($vinculo->is($empresa));
        $this->assertSame(Papel::Operador, $vinculo->pivot->papel);
        $this->assertTrue($vinculo->pivot->ativo);
    }

    public function test_usuario_participa_de_varias_empresas(): void
    {
        $empresaA = Company::factory()->create(['nome' => 'Empresa A']);
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $usuario = User::factory()->create();

        $usuario->empresas()->attach($empresaA, ['papel' => Papel::Supervisor]);
        $usuario->empresas()->attach($empresaB, ['papel' => Papel::Operador]);

        $this->assertSame(2, $usuario->empresas()->count());
        $this->assertTrue($usuario->empresas()->where('companies.id', $empresaB->id)->first()->pivot->papel === Papel::Operador);
    }

    public function test_vinculo_duplicado_e_rejeitado_pela_constraint(): void
    {
        $empresa = Company::factory()->create();
        $usuario = User::factory()->create();
        $usuario->empresas()->attach($empresa, ['papel' => Papel::Leitura]);

        $this->expectException(QueryException::class);

        $usuario->empresas()->attach($empresa, ['papel' => Papel::Operador]);
    }
}
