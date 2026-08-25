<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * VIS-V1-001 — as 4 listagens por status do menu superior do TEMA V1
 * (Entrada/Encaminhado/Aguardando credito/Concluido).
 */
class ListagensPorStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_entrada_lista_status_entrada_e_recebido_mas_nao_outros(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA em entrada', 'status' => Status::Entrada]);
        Rma::factory()->create(['descricao' => 'RMA recebido', 'status' => Status::Recebido]);
        Rma::factory()->create(['descricao' => 'RMA encaminhado', 'status' => Status::Encaminhado]);

        $response = $this->actingAs($usuario)->get(route('rmas.entrada'));

        $response->assertOk();
        $response->assertSee('RMA em entrada');
        $response->assertSee('RMA recebido');
        $response->assertDontSee('RMA encaminhado');
    }

    public function test_encaminhados_lista_apenas_status_encaminhado(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA encaminhado', 'status' => Status::Encaminhado]);
        Rma::factory()->create(['descricao' => 'RMA em entrada', 'status' => Status::Entrada]);

        $response = $this->actingAs($usuario)->get(route('rmas.encaminhados'));

        $response->assertOk();
        $response->assertSee('RMA encaminhado');
        $response->assertDontSee('RMA em entrada');
    }

    public function test_aguardando_credito_filtra_por_solucao_nao_por_status(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA pendente credito',
            'status' => Status::Concluido,
            'solucao' => Solucao::PendenteCredito,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA gerado credito',
            'status' => Status::Concluido,
            'solucao' => Solucao::GeradoCredito,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.aguardando-credito'));

        $response->assertOk();
        $response->assertSee('RMA pendente credito');
        $response->assertDontSee('RMA gerado credito');
    }

    public function test_concluidos_lista_apenas_status_concluido(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA concluido', 'status' => Status::Concluido]);
        Rma::factory()->create(['descricao' => 'RMA em entrada', 'status' => Status::Entrada]);

        $response = $this->actingAs($usuario)->get(route('rmas.concluidos'));

        $response->assertOk();
        $response->assertSee('RMA concluido');
        $response->assertDontSee('RMA em entrada');
    }

    public function test_entrada_aplica_a_mesma_regra_de_destaque_rn_11(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA sem garantia',
            'status' => Status::Entrada,
            'solucao' => Solucao::SemGarantia,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.entrada'));

        $response->assertOk();
        $response->assertSee('TrInconformidade');
    }

    public function test_header_do_tema_v1_mostra_os_4_atalhos(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($usuario)->get(route('rmas.index'));

        $response->assertOk();
        $response->assertSee(route('rmas.entrada'), false);
        $response->assertSee(route('rmas.encaminhados'), false);
        $response->assertSee(route('rmas.aguardando-credito'), false);
        $response->assertSee(route('rmas.concluidos'), false);
    }
}
