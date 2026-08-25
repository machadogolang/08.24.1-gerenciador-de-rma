<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriarRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_cria_rma_com_cliente_novo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->post('/rmas', [
            'descricao' => 'Notebook não liga',
            'defeito' => 'Não liga',
            'cliente_nome' => 'Cliente Totalmente Novo',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', ['nome' => 'Cliente Totalmente Novo']);
        $cliente = Cliente::query()->where('nome', 'Cliente Totalmente Novo')->firstOrFail();
        $this->assertDatabaseHas('rmas', [
            'descricao' => 'Notebook não liga',
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_operador_cria_rma_com_cliente_existente_reaproveita(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $existente = Cliente::factory()->create(['nome' => 'Cliente Já Cadastrado']);

        $response = $this->actingAs($operador)->post('/rmas', [
            'descricao' => 'HD com clique',
            'defeito' => 'Clique',
            'cliente_nome' => '  cliente   JÁ CADASTRADO  ',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseHas('rmas', [
            'descricao' => 'HD com clique',
            'cliente_id' => $existente->id,
        ]);
    }

    public function test_normalizacao_hgst_roda_na_criacao(): void
    {
        // RN-13: "fabricante == HGST" é normalizado para "Hitachi" internamente (não é
        // o campo `origem` que vira "Hitachi" — ver `Dominio\Rma::comNormalizacaoDeGravacao`
        // no design.md). Prova de ponta a ponta: fabricante "HGST" + origem "Hitachi"
        // (já igual ao nome pós-normalização) colapsa para "Unknown" via RN-14 — só
        // acontece se a conversão HGST→Hitachi realmente rodou antes da comparação.
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'HGST']);

        $response = $this->actingAs($operador)->post('/rmas', [
            'descricao' => 'HD Hitachi',
            'defeito' => 'Não detecta',
            'fabricante_id' => $fabricante->id,
            'origem' => 'Hitachi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rmas', [
            'descricao' => 'HD Hitachi',
            'origem' => 'Unknown',
        ]);
    }

    public function test_usuario_de_leitura_nao_pode_criar_rma(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($leitura)->post('/rmas', [
            'descricao' => 'Não deveria existir',
            'defeito' => 'X',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('rmas', ['descricao' => 'Não deveria existir']);
    }
}
