<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T3-15 - Relatorios no Tema V3:
 * Hub com os 3 cards de navegacao e metricas consolidadas + relatorios RCD, RPEC e RMPE.
 */
class RelatoriosV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    public function test_hub_de_relatorios_v3_renderiza_com_cards_e_painel(): void
    {
        $usuario = $this->usuario();
        Rma::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($usuario)->get('/v3/relatorios');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.relatorios.index');
        $response->assertSeeText('Relatórios Operacionais e Fiscais');
        $response->assertSeeText('Créditos Disponíveis');
        $response->assertSeeText('Produtos para Contagem');
        $response->assertSeeText('Produtos Encaminhados');
        $response->assertSeeText('Situação Geral dos RMAs');
        $response->assertSeeText('Resolução');
        $response->assertSeeText('Origem dos Produtos');
    }

    public function test_relatorio_rcd_v3_renderiza_com_creditos_disponiveis(): void
    {
        $usuario = $this->usuario();
        Rma::factory()->create([
            'status' => Status::Concluido,
            'credito_disponivel' => true,
            'solucao' => Solucao::GeradoCredito,
            'descricao' => 'Item com credito disponivel V3',
        ]);

        $response = $this->actingAs($usuario)->get('/v3/relatorios/rcd');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.relatorios.rcd');
        $response->assertSeeText('Item com credito disponivel V3');
        $response->assertSee('data-v3-filtro-tabela', false);
    }

    public function test_relatorio_rpec_v3_renderiza_com_filtro_de_status(): void
    {
        $usuario = $this->usuario();
        Rma::factory()->create([
            'marcarestoque' => true,
            'status' => Status::Recebido,
            'descricao' => 'Item em contagem de estoque V3',
        ]);

        $response = $this->actingAs($usuario)->get('/v3/relatorios/rpec');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.relatorios.rpec');
        $response->assertSeeText('Item em contagem de estoque V3');
        $response->assertSeeText('Filtrar por Status');
    }

    public function test_relatorio_rmpe_v3_renderiza_com_produtos_encaminhados(): void
    {
        $usuario = $this->usuario();
        Rma::factory()->create([
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => 'NFREM-V3-999',
            'descricao' => 'Item encaminhado para conserto V3',
        ]);

        $response = $this->actingAs($usuario)->get('/v3/relatorios/rmpe');

        $response->assertOk();
        $response->assertViewIs('temas.v3.rma.relatorios.rmpe');
        $response->assertSeeText('Item encaminhado para conserto V3');
    }

    public function test_salvar_informacao_adicional_do_relatorio_v3(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)
            ->put('/v3/relatorios/RCRD/informacao-adicional', [
                'informacao_adicional' => 'Nota operacional salva via Tema V3',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('relatorio_informacoes_adicionais', [
            'codigo' => 'RCRD',
            'informacao_adicional' => 'Nota operacional salva via Tema V3',
        ]);
    }
}
