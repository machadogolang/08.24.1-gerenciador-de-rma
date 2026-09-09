<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * P5 — detalhe de parceiro com dados completos e RMAs associados.
 */
class DetalheDoParceiroTest extends TestCase
{
    use RefreshDatabase;

    public static function detalheProvider(): array
    {
        $temas = [TemaPreferido::V1, TemaPreferido::V2];
        $tipos = [
            'clientes' => Cliente::class,
            'fabricantes' => Fabricante::class,
            'fornecedores' => Fornecedor::class,
            'assistencias-tecnicas' => AssistenciaTecnica::class,
        ];

        $casos = [];
        foreach ($temas as $tema) {
            foreach ($tipos as $tipo => $modelo) {
                $casos["{$tema->value}-{$tipo}"] = [$tema, $tipo, $modelo];
            }
        }

        return $casos;
    }

    #[DataProvider('detalheProvider')]
    public function test_detalhe_renderiza_campos_e_rmas_associados(TemaPreferido $tema, string $tipo, string $modelo): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
        $parceiro = $modelo::factory()->create(['nome' => "Detalhe {$tipo} P5"]);
        $this->criarRmaAssociado($parceiro);

        $response = $this->actingAs($usuario)->get("/parceiros/{$tipo}/{$parceiro->id}");

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.parceiros.show");
        $response->assertSee('class="detalhe-parceiro"', false);
        $response->assertSeeText("Detalhe {$tipo} P5");
        $response->assertSeeText('RMAs associados');
        $response->assertSeeText('RMA associado ao parceiro P5');
        $response->assertSee('class="acao acao--secundaria acao--compacta">Ver</a>', false);
    }

    public function test_detalhe_nao_vaza_fornecedor_de_outro_tenant(): void
    {
        $empresaB = Company::factory()->create(['nome' => 'Empresa B parceiro show']);
        $fornecedor = Fornecedor::factory()->make(['nome' => 'Fornecedor sigiloso B']);
        $fornecedor->forceFill(['tenant_id' => $empresaB->id])->save();
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $this->actingAs($usuario)
            ->get("/parceiros/fornecedores/{$fornecedor->id}")
            ->assertNotFound();
    }

    private function criarRmaAssociado($parceiro): void
    {
        $dados = match (true) {
            $parceiro instanceof Cliente => ['cliente_id' => $parceiro->id],
            $parceiro instanceof Fabricante => ['fabricante_id' => $parceiro->id],
            $parceiro instanceof Fornecedor => ['fornecedor_id' => $parceiro->id],
            $parceiro instanceof AssistenciaTecnica => [
                'destinatario_type' => AssistenciaTecnica::class,
                'destinatario_id' => $parceiro->id,
            ],
        };

        Rma::factory()->create([
            ...$dados,
            'descricao' => 'RMA associado ao parceiro P5',
        ]);
    }
}
