<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * UF-19 - Teste de Contrato Funcional entre Temas (V1 e V2):
 * Garante que cada capacidade canonica unificada responde HTTP 200 sob ambos
 * os temas, protegendo contra esquecimento ou regressao assimetrica.
 */
class CapabilityContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: Papel}>
     */
    public static function capacidadesProvider(): array
    {
        return [
            'perfil-senha' => ['identidade.perfil.show', Papel::Operador],
            'auditoria-acesso' => ['identidade.historico-de-acesso.index', Papel::Supervisor],
            'auditoria-modificacoes' => ['rmas.historico.index', Papel::Supervisor],
            'creditos' => ['rmas.credito.index', Papel::Operador],
            'hub-relatorios' => ['rmas.relatorios.index', Papel::Operador],
            'relatorio-rcd' => ['rmas.relatorios.rcd', Papel::Operador],
            'relatorio-rpec' => ['rmas.relatorios.rpec', Papel::Operador],
            'relatorio-rmpe' => ['rmas.relatorios.rmpe', Papel::Operador],
            'transporte-porto-alegre' => ['rmas.logistica.frete-porto-alegre', Papel::Operador],
            'ajuda' => ['rmas.ajuda', Papel::Operador],
            'usuarios' => ['identidade.usuarios.index', Papel::Supervisor],
            'parceiros-clientes' => ['parceiros.clientes.index', Papel::Operador],
            'parceiros-fornecedores' => ['parceiros.fornecedores.index', Papel::Operador],
            'parceiros-fabricantes' => ['parceiros.fabricantes.index', Papel::Operador],
            'parceiros-assistencias' => ['parceiros.assistencias-tecnicas.index', Papel::Operador],
        ];
    }

    #[DataProvider('capacidadesProvider')]
    public function test_capacidade_esta_disponivel_no_tema_v1(string $rota, Papel $papel): void
    {
        $usuario = User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get(route($rota));

        $response->assertOk();
        $this->assertStringContainsString('temas.v1.', $response->original->name());
    }

    #[DataProvider('capacidadesProvider')]
    public function test_capacidade_esta_disponivel_no_tema_v2(string $rota, Papel $papel): void
    {
        $usuario = User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($usuario)->get(route($rota));

        $response->assertOk();
        $this->assertStringContainsString('temas.v2.', $response->original->name());
    }

    public function test_fila_recebidos_disponivel_em_ambos_os_temas(): void
    {
        $operadorV1 = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
        $operadorV2 = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        // V1: listagem dedicada
        $resV1 = $this->actingAs($operadorV1)->get(route('rmas.recebidos'));
        $resV1->assertOk();
        $resV1->assertViewIs('temas.v1.rma.recebidos');

        // V2: painel de Recebidos presente na interface principal
        $resV2 = $this->actingAs($operadorV2)->get(route('v2.rmas.index'));
        $resV2->assertOk();
        $resV2->assertSee('id="recebido"', false);
    }
}
