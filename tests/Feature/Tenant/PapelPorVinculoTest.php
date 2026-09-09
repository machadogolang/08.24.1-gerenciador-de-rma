<?php

namespace Tests\Feature\Tenant;

use App\Identidade\Dominio\Papel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PapelPorVinculoTest extends TestCase
{
    use RefreshDatabase;

    public function test_mesmo_usuario_tem_autorizacao_diferente_por_empresa(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $usuario = User::factory()->create();
        $usuario->empresas()->updateExistingPivot($cell->id, ['papel' => Papel::Supervisor]);
        $usuario->empresas()->attach($empresaB, ['papel' => Papel::Leitura, 'ativo' => true]);

        // Supervisor na empresa A acessa gestão de usuários...
        $this->withSession(['empresa_ativa_id' => $cell->id])
            ->actingAs($usuario)
            ->get('/usuarios')
            ->assertOk();

        // ...mas o MESMO usuário com Leitura na empresa B recebe 403.
        $this->withSession(['empresa_ativa_id' => $empresaB->id])
            ->actingAs($usuario)
            ->get('/usuarios')
            ->assertForbidden();
    }

    public function test_supervisor_da_empresa_a_nao_ganha_poder_na_empresa_b(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $usuario = User::factory()->create();
        $usuario->empresas()->updateExistingPivot($cell->id, ['papel' => Papel::Supervisor]);
        $usuario->empresas()->attach($empresaB, ['papel' => Papel::Leitura, 'ativo' => true]);

        $this->withSession(['empresa_ativa_id' => $empresaB->id])
            ->actingAs($usuario)
            ->get('/usuarios')
            ->assertForbidden();
    }

    public function test_login_permitido_quando_pelo_menos_um_vinculo_ativo_pode_autenticar(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $usuario = User::factory()->create();
        $usuario->empresas()->updateExistingPivot($cell->id, ['papel' => Papel::Operador]);
        $usuario->empresas()->attach($empresaB, ['papel' => Papel::Bloqueado, 'ativo' => true]);

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ])->assertRedirect();
    }

    public function test_todos_os_vinculos_bloqueados_negam_login(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $usuario = User::factory()->create();
        $usuario->empresas()->updateExistingPivot($cell->id, ['papel' => Papel::Bloqueado]);
        $usuario->empresas()->attach($empresaB, ['papel' => Papel::Bloqueado, 'ativo' => true]);

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }
}
