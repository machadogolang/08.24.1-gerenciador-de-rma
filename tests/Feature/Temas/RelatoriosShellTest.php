<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FRONT-003/UI-03 - RCD/RPEC/RMPE renderizam dentro do shell do tema ativo com
 * `relatorio-print` no body (impressão limpa) e contrato `.acao` nos filtros.
 */
class RelatoriosShellTest extends TestCase
{
    use RefreshDatabase;

    public static function temasProvider(): array
    {
        return [
            'v1' => [TemaPreferido::V1],
            'v2' => [TemaPreferido::V2],
        ];
    }

    #[DataProvider('temasProvider')]
    public function test_rcd_renderiza_no_shell_do_tema(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => $tema,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA relatorio RCD shell',
            'status' => Status::Concluido,
            'credito_disponivel' => true,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rcd'));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.relatorios.rcd");
        $response->assertSee('relatorio-print', false);
        $response->assertSeeText('RMA relatorio RCD shell');

        if ($tema === TemaPreferido::V2) {
            $response->assertSee('relatorio-tabela', false);
        } else {
            // PAR14-REL-RCD-001 - V1 renderiza as colunas do Legacy.
            $response->assertSeeText('PROTOCOLO');
        }
    }

    #[DataProvider('temasProvider')]
    public function test_rpec_renderiza_no_shell_do_tema_com_filtro(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => $tema,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA relatorio RPEC shell',
            'marcarestoque' => true,
            'status' => Status::Recebido,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rpec'));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.relatorios.rpec");
        $response->assertSee('relatorio-print', false);
        $response->assertSeeText('RMA relatorio RPEC shell');

        if ($tema === TemaPreferido::V2) {
            $response->assertSee('class="acao acao--secundaria">Filtrar</button>', false);
        } else {
            // PAR14-REL-RPEC-001 - V1 renderiza as 10 colunas do Legacy.
            $response->assertSeeText('ORIGEM');
            $response->assertSeeText('DESTINATARIO');
        }
    }

    #[DataProvider('temasProvider')]
    public function test_rmpe_renderiza_no_shell_do_tema_com_filtro_e_intervalo(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => $tema,
        ]);
        Rma::factory()->create([
            'descricao' => 'RMA relatorio RMPE shell',
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => '123',
            'encaminhado_em' => '2026-05-10 10:00:00',
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.relatorios.rmpe', [
            'data_inicio' => '2026-05-01',
            'data_fim' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.relatorios.rmpe");
        $response->assertSee('relatorio-print', false);
        $response->assertSeeText('RMA relatorio RMPE shell');

        if ($tema === TemaPreferido::V2) {
            $response->assertSee('class="acao acao--secundaria">Filtrar</button>', false);
        } else {
            // PAR14-REL-RMPE-001 - V1 renderiza as colunas do Legacy.
            $response->assertSeeText('ENCAMINHADO');
            $response->assertSeeText('VALOR');
        }
    }
}
