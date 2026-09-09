<?php

namespace Tests\Feature\Tenant;

use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ParceirosIsolamentoTest extends TestCase
{
    use RefreshDatabase;

    public static function parceiroProvider(): array
    {
        return [
            'cliente' => [Cliente::class, 'clientes'],
            'fabricante' => [Fabricante::class, 'fabricantes'],
            'fornecedor' => [Fornecedor::class, 'fornecedores'],
            'assistencia-tecnica' => [AssistenciaTecnica::class, 'assistencias-tecnicas'],
        ];
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    #[DataProvider('parceiroProvider')]
    public function test_empresa_a_nao_lista_parceiro_da_empresa_b(string $modelo, string $rota): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de parceiros']);
        $registro = $this->criarRegistro($modelo, $empresaB, 'Parceiro sigiloso B');
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/parceiros/'.$rota)
            ->assertOk()
            ->assertDontSee($registro->nome);
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    #[DataProvider('parceiroProvider')]
    public function test_empresa_a_nao_abre_edicao_de_parceiro_da_empresa_b(string $modelo, string $rota): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de parceiros']);
        $registro = $this->criarRegistro($modelo, $empresaB, 'Parceiro sigiloso B');
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->get('/parceiros/'.$rota.'/'.$registro->id.'/edit')
            ->assertNotFound();
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    #[DataProvider('parceiroProvider')]
    public function test_empresa_a_nao_atualiza_parceiro_da_empresa_b(string $modelo, string $rota): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B de parceiros']);
        $registro = $this->criarRegistro($modelo, $empresaB, 'Parceiro sigiloso B');
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->put('/parceiros/'.$rota.'/'.$registro->id, ['nome' => 'Tentativa de edicao'])
            ->assertNotFound();
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    #[DataProvider('parceiroProvider')]
    public function test_empresa_a_cria_parceiro_no_proprio_tenant(string $modelo, string $rota): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->post('/parceiros/'.$rota, ['nome' => 'Parceiro da empresa A'])
            ->assertRedirect();

        $criado = $modelo::query()->withoutGlobalScopes()->where('nome', 'Parceiro da empresa A')->sole();
        $this->assertSame($cell->id, $criado->tenant_id);
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    private function criarRegistro(string $modelo, Company $empresa, string $nome): Model
    {
        $registro = $modelo::factory()->make(['nome' => $nome]);
        $registro->forceFill(['tenant_id' => $empresa->id])->save();

        return $registro;
    }
}
