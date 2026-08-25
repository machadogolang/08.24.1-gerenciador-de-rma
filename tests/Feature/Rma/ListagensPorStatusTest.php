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

    public function test_concluidos_marca_sem_garantia_como_tabelinha_tr3_e_demais_como_tr1_tr2(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create([
            'descricao' => 'RMA concluido sem garantia',
            'status' => Status::Concluido,
            'solucao' => Solucao::SemGarantia,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA concluido normal um',
            'status' => Status::Concluido,
            'solucao' => Solucao::GeradoCredito,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA concluido normal dois',
            'status' => Status::Concluido,
            'solucao' => Solucao::GeradoCredito,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.concluidos'));

        $response->assertOk();
        $response->assertSee('Tabelinha-TR3', false);
        $response->assertSee('Tabelinha-TR1', false);
        $response->assertSee('Tabelinha-TR2', false);
        $response->assertDontSee('TrZebrada', false);
    }

    /**
     * VIS-V1-001/CP3A-01/CP3A-03 — `Concluido` reproduz a composição histórica
     * (`legacy-source/14.6.1/page/concluidos.php`): ícone 50×50 + texto de contexto,
     * sem o `<h1>` artificial que o layout do TEMA V1 injeta por padrão nas demais telas.
     */
    public function test_concluidos_nao_tem_h1_artificial_e_reproduz_cabecalho_historico(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA concluido', 'status' => Status::Concluido]);

        $response = $this->actingAs($usuario)->get(route('rmas.concluidos'));

        $response->assertOk();
        $response->assertSee('images/tema-v1/concluido.png', false);
        $response->assertSee('title-comicone', false);
        $response->assertSee('colgroup', false);

        // #CONTEUDO é o miolo histórico da tela (o layout também injeta um
        // <h1 class="titulo-v1"> sempre presente no DOM, mas fora de #CONTEUDO,
        // para o painel de sessão oculto por CSS — isso não é o H1 artificial do
        // achado 7). Isolamos #CONTEUDO até a tabela para confirmar que nenhum
        // <h1> foi injetado ali.
        $conteudo = $response->getContent();
        $inicio = strpos($conteudo, 'id="CONTEUDO"');
        $fimTabela = strpos($conteudo, '<table', $inicio);
        $this->assertStringNotContainsString('<h1', substr($conteudo, $inicio, $fimTabela - $inicio));
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
