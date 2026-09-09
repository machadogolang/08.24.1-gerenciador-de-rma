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
 * FRONT-003/UI-02C - contrato semântico do partial de ciclo de vida
 * (`rma._acoes_de_transicao`), compartilhado por V1/V2. Não muda rotas/CSRF.
 */
class ContratoVisualAcoesCicloDeVidaTest extends TestCase
{
    use RefreshDatabase;

    public static function transicaoEsperadaProvider(): array
    {
        return [
            'v1-entrada' => [TemaPreferido::V1, Status::Entrada, 'Receber', true, false],
            'v1-recebido' => [TemaPreferido::V1, Status::Recebido, 'Encaminhar', true, true],
            'v1-encaminhado' => [TemaPreferido::V1, Status::Encaminhado, 'Concluir', true, true],
            'v1-concluido' => [TemaPreferido::V1, Status::Concluido, null, false, false],
            'v1-arquivado' => [TemaPreferido::V1, Status::Arquivado, null, false, false],
            'v2-entrada' => [TemaPreferido::V2, Status::Entrada, 'Receber', true, false],
            'v2-recebido' => [TemaPreferido::V2, Status::Recebido, 'Encaminhar', true, true],
            'v2-encaminhado' => [TemaPreferido::V2, Status::Encaminhado, 'Concluir', true, true],
            'v2-concluido' => [TemaPreferido::V2, Status::Concluido, null, false, false],
            'v2-arquivado' => [TemaPreferido::V2, Status::Arquivado, null, false, false],
        ];
    }

    #[DataProvider('transicaoEsperadaProvider')]
    public function test_acoes_de_ciclo_de_vida_usam_contrato_semantico(
        TemaPreferido $tema,
        Status $status,
        ?string $acaoPrincipal,
        bool $arquivarVisivel,
        bool $reverterVisivel,
    ): void {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
        $rma = Rma::factory()->create([
            'status' => $status,
            'descricao' => 'RMA ciclo de vida contrato',
        ]);

        $response = $this->actingAs($usuario)
            ->get("/{$tema->value}/rma/{$rma->id}")
            ->assertOk();

        // PAR-RES-C-01 - no V1 o bloco avancado continua (partial de ciclo); no V2
        // o formulario unico do 15.8.1 e a fonte das acoes (select SALVAR/RECEBER/...),
        // sem partial `.acao` extra.
        if ($tema === TemaPreferido::V1) {
            $response->assertSee('detalhe-bd-acoes-avancadas', false);
            $response->assertSee('class="acao acao--primaria">Salvar solução</button>', false);

            if ($acaoPrincipal !== null) {
                $response->assertSee("class=\"acao acao--operacional\">{$acaoPrincipal}</button>", false);
            }
            if ($arquivarVisivel) {
                $response->assertSee('class="acao acao--operacional">Arquivar</button>', false);
            } else {
                $response->assertDontSee('Arquivar', false);
            }
            if ($reverterVisivel) {
                $response->assertSee('class="acao acao--operacional">Reverter para Entrada</button>', false);
            } else {
                $response->assertDontSee('Reverter para Entrada', false);
            }
            $response->assertSee('action="' . route('rmas.solucao', $rma->id) . '"', false);
        } else {
            $response->assertDontSee('detalhe-bd-acoes-avancadas', false);
            $response->assertDontSee('Salvar solução</button>', false);
            $response->assertSee('<select name="acao"', false);
            $response->assertSee('option value="salvar">SALVAR', false);
        }
    }
}
