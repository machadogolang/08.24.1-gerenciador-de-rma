<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Fabricante;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditarRmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_edita_campos_do_nucleo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['descricao' => 'Descrição antiga', 'defeito' => 'Defeito antigo']);

        $response = $this->actingAs($operador)->put("/rmas/{$rma->id}", [
            'descricao' => 'Descrição nova',
            'defeito' => 'Defeito novo',
        ]);

        $response->assertRedirect(route('rmas.show', $rma->id));
        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'descricao' => 'Descrição nova',
            'defeito' => 'Defeito novo',
        ]);
    }

    public function test_edicao_reaplica_normalizacao_rn13_rn14(): void
    {
        // Mesma prova de ponta a ponta usada em CriarRmaTest::test_normalizacao_hgst_roda_na_criacao,
        // agora para o caminho de edição — RN-13/RN-14 devem rodar de novo a cada
        // gravação, não só na criação (ver Dominio\Rma::comNormalizacaoDeGravacao).
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'HGST']);
        $rma = Rma::factory()->create(['descricao' => 'HD antigo', 'defeito' => 'Não detecta']);

        $response = $this->actingAs($operador)->put("/rmas/{$rma->id}", [
            'descricao' => 'HD Hitachi',
            'defeito' => 'Não detecta',
            'fabricante_id' => $fabricante->id,
            'origem' => 'Hitachi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'descricao' => 'HD Hitachi',
            'origem' => 'Unknown',
        ]);
    }

    public function test_usuario_de_leitura_nao_pode_editar_rma(): void
    {
        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create(['descricao' => 'Original']);

        $response = $this->actingAs($leitura)->put("/rmas/{$rma->id}", [
            'descricao' => 'Não deveria mudar',
            'defeito' => 'X',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('rmas', ['id' => $rma->id, 'descricao' => 'Original']);
    }
}
