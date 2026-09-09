<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PAR-RMA-003/P6 — modo texto alcança nomes de contrapartes reais
 * (fabricante/fornecedor/cliente/destinatário assistência técnica).
 */
class BuscarRmasContrapartesTest extends TestCase
{
    use RefreshDatabase;

    public function test_busca_texto_alcanca_nome_de_fabricante(): void
    {
        $this->executarBusca(
            $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante Alvo P6']),
            fn () => Rma::factory()->create(['fabricante_id' => $fabricante->id, 'descricao' => 'RMA do fabricante P6']),
            'Fabricante Alvo',
            'RMA do fabricante P6',
        );
    }

    public function test_busca_texto_alcanca_nome_de_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor Alvo P6']);
        $this->executarBusca(
            $fornecedor,
            fn () => Rma::factory()->create(['fornecedor_id' => $fornecedor->id, 'descricao' => 'RMA do fornecedor P6']),
            'Fornecedor Alvo',
            'RMA do fornecedor P6',
        );
    }

    public function test_busca_texto_alcanca_nome_de_cliente(): void
    {
        $this->executarBusca(
            $cliente = Cliente::factory()->create(['nome' => 'Cliente Alvo P6']),
            fn () => Rma::factory()->create(['cliente_id' => $cliente->id, 'descricao' => 'RMA do cliente P6']),
            'Cliente Alvo',
            'RMA do cliente P6',
        );
    }

    public function test_busca_texto_alcanca_nome_de_assistencia_destinataria(): void
    {
        $this->executarBusca(
            $assistencia = AssistenciaTecnica::factory()->create(['nome' => 'Assistencia Alvo P6']),
            fn () => Rma::factory()->create([
                'destinatario_type' => AssistenciaTecnica::class,
                'destinatario_id' => $assistencia->id,
                'descricao' => 'RMA da assistencia P6',
            ]),
            'Assistencia Alvo',
            'RMA da assistencia P6',
        );
    }

    private function executarBusca($contraparte, callable $criarRma, string $termo, string $esperado): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $criarRma();

        $response = $this->actingAs($usuario)->get('/rmas?tipo=texto&valor=' . rawurlencode($termo));

        $response->assertOk();
        $response->assertSee($esperado);
    }
}
