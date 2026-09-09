<?php

namespace Tests\Feature\Tenant;

use App\Identidade\Dominio\Papel;
use App\Models\Company;
use App\Models\Fabricante;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P6 - busca por contraparte não vaza fabricante/RMA de outro tenant.
 */
class BuscaContrapartesIsolamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_busca_texto_nao_retorna_rma_de_fabricante_de_outra_empresa(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B busca']);
        $fabricanteB = Fabricante::factory()->make(['nome' => 'Fabricante Sigiloso Busca']);
        $fabricanteB->forceFill(['tenant_id' => $empresaB->id])->save();
        $rmaB = Rma::factory()->make(['descricao' => 'RMA sigiloso da busca']);
        $rmaB->forceFill(['tenant_id' => $empresaB->id, 'fabricante_id' => $fabricanteB->id])->save();

        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($usuario)
            ->get('/rmas?tipo=texto&valor=' . rawurlencode('Fabricante Sigiloso'));

        $response->assertOk();
        $response->assertDontSee('RMA sigiloso da busca');
    }
}
