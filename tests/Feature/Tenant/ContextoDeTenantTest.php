<?php

namespace Tests\Feature\Tenant;

use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Identidade\Dominio\Papel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContextoDeTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_com_um_vinculo_ativo_tem_contexto_resolvido(): void
    {
        $usuario = User::factory()->create();
        $empresa = $usuario->empresas()->first();

        $this->actingAs($usuario)->get('/perfil');

        $contexto = app(ContextoDeTenant::class);
        $this->assertTrue($contexto->temEmpresa());
        $this->assertSame($empresa->id, $contexto->empresaId());
    }

    public function test_usuario_sem_vinculo_recebe_403(): void
    {
        $usuario = User::factory()->create();
        $usuario->empresas()->detach();

        $this->actingAs($usuario)->get('/perfil')->assertForbidden();
    }

    public function test_multi_vinculo_escolhe_empresa_da_sessao_quando_valida(): void
    {
        $usuario = User::factory()->create();
        $empresaA = $usuario->empresas()->first();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $usuario->empresas()->attach($empresaB, ['papel' => Papel::Operador, 'ativo' => true]);

        $this->withSession(['empresa_ativa_id' => $empresaB->id])
            ->actingAs($usuario)
            ->get('/perfil');

        $this->assertSame($empresaB->id, app(ContextoDeTenant::class)->empresaId());
    }

    public function test_multi_vinculo_sem_sessao_usa_primeiro_vinculo(): void
    {
        $usuario = User::factory()->create();
        $empresaA = $usuario->empresas()->first();
        Company::factory()->create(['nome' => 'Empresa C']);
        $empresaC = Company::query()->where('nome', 'Empresa C')->sole();
        $usuario->empresas()->attach($empresaC, ['papel' => Papel::Leitura, 'ativo' => true]);

        $this->actingAs($usuario)->get('/perfil');

        $this->assertSame($empresaA->id, app(ContextoDeTenant::class)->empresaId());
    }
}
