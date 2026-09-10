<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR14-REL-RPEC/RCD/RMPE-001 - a folha de relatorio do TEMA V1 volta ao contrato do
 * Legacy `14.6.1/page/relatorios.php`: colunas historicas, totalizadores e informacao
 * adicional persistida (save -> reload).
 */
class RelatoriosV1ParidadeTest extends TestCase
{
    use RefreshDatabase;

    private function adminV1(): User
    {
        return User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    #[Test]
    public function rpec_v1_mostra_colunas_totais_e_persiste_informacao_adicional(): void
    {
        $admin = $this->adminV1();
        Rma::factory()->create([
            'descricao' => 'RPEC linha',
            'marcarestoque' => true,
            'status' => Status::Recebido,
            'modelo' => 'MOD-1',
            'empresa' => 'Cellsystem',
            'origem' => 'Mercado Livre',
            'valor' => 150.5,
            'os' => 'OS9',
        ]);

        $response = $this->actingAs($admin)->get(route('rmas.relatorios.rpec'));

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.relatorios.rpec');
        $response->assertSeeText('RPEC - RELACAO DOS PRODUTOS EM ESTOQUE PARA CONTAGEM');
        $response->assertSeeText('ENTRADA');
        $response->assertSeeText('ORIGEM');
        $response->assertSeeText('DESTINATARIO');
        $response->assertSeeText('M LIVRE');
        $response->assertSeeText('Valor Total: R$ 150.50');
        $response->assertSeeText('Quantidade Total de produtos: 1');
        $response->assertSeeText('INFORMACAO ADICIONAL');

        $this->actingAs($admin)
            ->put(route('rmas.relatorios.informacao-adicional.update', ['codigo' => 'RPEC']), [
                'informacao_adicional' => 'Conferido em 10/09',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('relatorio_informacoes_adicionais', [
            'codigo' => 'RPEC',
            'informacao_adicional' => 'Conferido em 10/09',
        ]);

        $this->actingAs($admin)
            ->get(route('rmas.relatorios.rpec'))
            ->assertSeeText('Conferido em 10/09');
    }

    #[Test]
    public function rcd_v1_mostra_colunas_do_legacy_e_totais(): void
    {
        $admin = $this->adminV1();
        Rma::factory()->create([
            'descricao' => 'RCD linha',
            'status' => Status::Concluido,
            'credito_disponivel' => true,
            'protocolo' => 'PROTO-1',
            'valor' => 10.0,
        ]);

        $response = $this->actingAs($admin)->get(route('rmas.relatorios.rcd'));

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.relatorios.rcd');
        $response->assertSeeText('RCD - RELATORIO DE CREDITOS DISPONIVEIS');
        $response->assertSeeText('CONCLUIDO');
        $response->assertSeeText('PROTOCOLO');
        $response->assertSeeText('Valor Total: R$ 10.00');
    }

    #[Test]
    public function rmpe_v1_mostra_colunas_do_legacy_e_totais(): void
    {
        $admin = $this->adminV1();
        Rma::factory()->create([
            'descricao' => 'RMPE linha',
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => '321',
            'encaminhado_em' => '2026-05-10 10:00:00',
            'valor' => 20.0,
        ]);

        $response = $this->actingAs($admin)->get(route('rmas.relatorios.rmpe', [
            'data_inicio' => '2026-05-01',
            'data_fim' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.relatorios.rmpe');
        $response->assertSeeText('RMPE - RELACAO DOS PRODUTOS ENCAMINHADOS PELO RMA');
        $response->assertSeeText('ENCAMINHADO');
        $response->assertSeeText('NF R');
        $response->assertSeeText('Valor Total: R$ 20.00');
    }
}
