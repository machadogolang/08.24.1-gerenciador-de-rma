<?php

namespace Tests\Feature\Tenant;

use App\Compartilhado\Tenant\PertenceATenant;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\ModificacaoDeRma;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GateDeIsolamentoTest extends TestCase
{
    use RefreshDatabase;

    public static function modelosTenantScopedProvider(): array
    {
        return [
            'cliente' => [Cliente::class, 'clientes'],
            'fabricante' => [Fabricante::class, 'fabricantes'],
            'fornecedor' => [Fornecedor::class, 'fornecedores'],
            'assistencia-tecnica' => [AssistenciaTecnica::class, 'assistencias_tecnicas'],
            'rma' => [RmaEloquent::class, 'rmas'],
            'modificacao-de-rma' => [ModificacaoDeRma::class, 'modificacoes_de_rma'],
        ];
    }

    /**
     * @param  class-string  $modelo
     */
    #[DataProvider('modelosTenantScopedProvider')]
    public function test_model_tenant_scoped_possui_trait_coluna_e_nao_e_mass_assignable(string $modelo, string $tabela): void
    {
        $this->assertTrue(in_array(PertenceATenant::class, class_uses_recursive($modelo), true));
        $this->assertTrue(Schema::hasColumn($tabela, 'tenant_id'));

        $fillable = (new $modelo)->getFillable();
        $this->assertNotContains('tenant_id', $fillable);
    }

    public function test_relatorio_da_empresa_a_nao_soma_rma_da_empresa_b(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B relatorio']);
        $rmaB = RmaEloquent::factory()->make([
            'descricao' => 'RMA sigiloso relatorio B',
            'status' => Status::Concluido,
            'credito_disponivel' => true,
        ]);
        $rmaB->forceFill(['tenant_id' => $empresaB->id])->save();
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/rmas-relatorios/rcd')
            ->assertOk()
            ->assertDontSee($rmaB->descricao);
    }

    public function test_alerta_da_empresa_a_nao_exibe_rma_da_empresa_b(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B alerta']);
        $rmaB = RmaEloquent::factory()->make([
            'descricao' => 'RMA sigiloso alerta B',
            'solucao' => Solucao::PendenteCredito,
        ]);
        $rmaB->forceFill(['tenant_id' => $empresaB->id])->save();
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/rmas-alertas')
            ->assertOk()
            ->assertDontSee($rmaB->descricao);
    }
}
