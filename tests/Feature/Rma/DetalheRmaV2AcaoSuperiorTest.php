<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-RMA-DET-001 - o detalhe V2 tem o select de ciclo de vida no TOPO e no RODAPE
 * (fonte `15.8.1/page/rma.php`, blocos `selectacaoup` e `selectacaodown`). O
 * controller resolve a acao pelo bloco clicado (`okup`/`okdown`), sem duplicar regra
 * de dominio - `executarAcaoDoDetalhe` continua o unico despachante.
 */
class DetalheRmaV2AcaoSuperiorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function select_do_topo_executa_a_acao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->put("/v2/rma/{$rma->id}", [
            'descricao' => $rma->descricao,
            'defeito' => $rma->defeito,
            'selectacaoup' => 'receber',
            'okup' => 'OK',
        ]);

        $response->assertRedirect("/v2/rma/{$rma->id}");
        $this->assertDatabaseHas('rmas', ['id' => $rma->id, 'status' => 'Recebido']);
    }

    #[Test]
    public function select_do_rodape_executa_a_acao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->put("/v2/rma/{$rma->id}", [
            'descricao' => $rma->descricao,
            'defeito' => $rma->defeito,
            'selectacaodown' => 'receber',
            'okdown' => 'OK',
        ]);

        $response->assertRedirect("/v2/rma/{$rma->id}");
        $this->assertDatabaseHas('rmas', ['id' => $rma->id, 'status' => 'Recebido']);
    }

    #[Test]
    public function detalhe_v2_exibe_os_dois_selects_de_ciclo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->get("/v2/rma/{$rma->id}");

        $response->assertOk();
        $response->assertSee('name="selectacaoup"', false);
        $response->assertSee('name="selectacaodown"', false);
        $response->assertSee('name="okup"', false);
        $response->assertSee('name="okdown"', false);
    }
}
