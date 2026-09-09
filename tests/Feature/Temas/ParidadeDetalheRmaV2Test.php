<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\StatusDeLancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR-DET-V2-01 - caracterizacao do detalhe RMA Tema V2 em leitura com
 * cabecalho operacional e grupos do Legacy 15.8.1.
 */
class ParidadeDetalheRmaV2Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function detalhe_v2_restaura_cabecalho_e_grupos_criticos(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create([
            'descricao' => 'RMA detalhe V2 caracterizacao',
            'modelo' => 'MODELO V2',
            'sn' => 'SN-V2-001',
            'pn' => 'PN-V2-001',
            'snid' => 'SNID-V2-001',
            'prioridade' => Prioridade::Alta,
            'lancadoretorno' => StatusDeLancamento::NfDevolucao,
            'nf_remessa' => 'NFREMESSA-V2',
            'nf_retorno_numero' => 'NFRETORNO-V2',
            'nf_devolucao_de_venda' => 'NFDEVOL-V2',
            'rastreio_ida' => 'RASTREIO-V2-IDA',
            'destinatario_email_legado' => 'destinatario-v2@example.test',
        ]);

        $response = $this->actingAs($usuario)
            ->get("/v2/rma/{$rma->id}")
            ->assertOk();

        $response->assertViewIs('temas.v2.rma.show');
        $response->assertSeeText('NUMERO DO BD');
        foreach ([
            'Que produto é?',
            'Modelo',
            'Fabricante',
            'S/N',
            'SNID',
            'P/N',
            'OS',
            'Origem',
            'Prioridade',
            'PROTOCOLO',
            'Defeito reclamado',
            'E um produto do estoque ?',
            'Empresa',
            'E credito disponivel ?',
            'Entrada',
            'Recebido',
            'Encaminhado',
            'Concluido',
            'NF de Venda',
            'Quem e o Cliente ?',
            'NF de Compra',
            'Qual o Fornecedor ?',
            'NF de Remessa',
            'Valor do produto',
            'Qual o destinatario ?',
            'NF de Retorno',
            'NF lancada no estoque ?',
            'O que foi feito / resultado ?',
            'Informacao adicional',
        ] as $grupo) {
            $response->assertSeeText($grupo);
        }

        $response->assertSeeText('RMA detalhe V2 caracterizacao');
        $response->assertSeeText('MODELO V2');
        $response->assertSeeText('SN-V2-001');
        $response->assertSeeText('NFREMESSA-V2');
        $response->assertSeeText('NFRETORNO-V2');
        $response->assertSeeText('NF DE DEVOLUCAO');
        $response->assertSeeText('destinatario-v2@example.test');
    }
}
