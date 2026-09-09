<?php

namespace Tests\Feature\Tenant;

use App\Identidade\Dominio\Papel;
use App\Models\Company;
use App\Models\ModificacaoDeRma;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\AcaoDeModificacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaIsolamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresa_a_nao_ve_historico_de_modificacao_da_empresa_b(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de auditoria']);
        $autorB = User::factory()->create();
        $autorB->empresas()->detach();
        $autorB->empresas()->attach($empresaB, ['papel' => Papel::Operador]);

        $rmaB = RmaEloquent::factory()->make(['descricao' => 'RMA sigiloso da empresa B']);
        $rmaB->forceFill(['tenant_id' => $empresaB->id])->save();

        $modificacao = new ModificacaoDeRma([
            'rma_id' => $rmaB->id,
            'user_id' => $autorB->id,
            'acao' => AcaoDeModificacao::Criacao,
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'estado_apos' => ['descricao' => $rmaB->descricao],
        ]);
        $modificacao->tenant_id = $empresaB->id;
        $modificacao->save();

        $supervisorA = User::factory()->create(['papel' => Papel::Supervisor]);

        $this->actingAs($supervisorA)
            ->get('/rmas-historico')
            ->assertOk()
            ->assertDontSee("#{$rmaB->id}", false)
            ->assertDontSee($autorB->name, false);
    }
}
