<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Fabricante;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `LEG-RMA-041` — RMAs relacionados ao mesmo destinatário/fabricante/fornecedor,
 * excluindo o próprio RMA de referência. Paginado.
 */
class BoletinsRelacionadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_rma_relacionado_pelo_mesmo_fabricante_e_exclui_o_proprio(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create();
        $referencia = RmaEloquent::factory()->create(['fabricante_id' => $fabricante->id]);
        $relacionado = RmaEloquent::factory()->create(['fabricante_id' => $fabricante->id]);
        $naoRelacionado = RmaEloquent::factory()->create();

        $response = $this->actingAs($operador)->get("/rmas/{$referencia->id}/boletins-relacionados");

        $response->assertOk();
        $response->assertViewHas('relacionados', function ($relacionados) use ($referencia, $relacionado, $naoRelacionado) {
            return $relacionados->contains('id', $relacionado->id)
                && ! $relacionados->contains('id', $referencia->id)
                && ! $relacionados->contains('id', $naoRelacionado->id);
        });
    }

    public function test_pagina_os_resultados(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create();
        $referencia = RmaEloquent::factory()->create(['fabricante_id' => $fabricante->id]);
        RmaEloquent::factory()->count(25)->create(['fabricante_id' => $fabricante->id]);

        $response = $this->actingAs($operador)->get("/rmas/{$referencia->id}/boletins-relacionados");

        $response->assertOk();
        $response->assertViewHas('relacionados', function ($relacionados) {
            return $relacionados->count() === 20 && $relacionados->total() === 25;
        });
    }

    public function test_rma_inexistente_404(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($operador)->get('/rmas/999999/boletins-relacionados');

        $response->assertNotFound();
    }

    public function test_rma_sem_nenhuma_referencia_nao_casa_com_outro_tambem_sem_referencia(): void
    {
        // Prova de regressão do desvio documentado em BoletinsRelacionados: o
        // pseudocódigo original do design.md usaria orWhere(coluna, null), que o
        // Query Builder traduz para "coluna IS NULL" — dois RMAs sem
        // destinatario/fabricante/fornecedor casariam entre si por engano.
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $referencia = RmaEloquent::factory()->create([
            'fabricante_id' => null,
            'fornecedor_id' => null,
            'destinatario_id' => null,
        ]);
        $outroTambemSemReferencia = RmaEloquent::factory()->create([
            'fabricante_id' => null,
            'fornecedor_id' => null,
            'destinatario_id' => null,
        ]);

        $response = $this->actingAs($operador)->get("/rmas/{$referencia->id}/boletins-relacionados");

        $response->assertOk();
        $response->assertViewHas('relacionados', function ($relacionados) use ($outroTambemSemReferencia) {
            return $relacionados->total() === 0
                && ! $relacionados->contains('id', $outroTambemSemReferencia->id);
        });
    }

    public function test_rma_relacionado_apenas_por_fornecedor(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fornecedor = \App\Models\Fornecedor::factory()->create();
        $referencia = RmaEloquent::factory()->create(['fornecedor_id' => $fornecedor->id]);
        $relacionado = RmaEloquent::factory()->create(['fornecedor_id' => $fornecedor->id]);

        $response = $this->actingAs($operador)->get("/rmas/{$referencia->id}/boletins-relacionados");

        $response->assertOk();
        $response->assertViewHas('relacionados', function ($relacionados) use ($relacionado) {
            return $relacionados->contains('id', $relacionado->id);
        });
    }
}
