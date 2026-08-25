<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerDetalheDoRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_ve_detalhe_de_rma_existente(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create(['descricao' => 'Notebook não liga']);

        $response = $this->actingAs($usuario)->get("/rmas/{$rma->id}");

        $response->assertOk();
        $response->assertSee('Notebook não liga');
    }

    public function test_rma_inexistente_devolve_404(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($usuario)->get('/rmas/999999');

        $response->assertNotFound();
    }
}
