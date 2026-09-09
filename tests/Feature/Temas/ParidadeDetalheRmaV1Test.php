<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR-DET-V1-01 - caracterizacao do detalhe RMA Tema V1 restaurado
 * (BOLETIM DE DEFEITO com grupos de 4 colunas).
 */
class ParidadeDetalheRmaV1Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function detalhe_v1_restaura_boletim_de_defeito_com_grupos_criticos(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante Detalhe V1']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor Detalhe V1']);
        $cliente = Cliente::factory()->create(['nome' => 'Cliente Detalhe V1']);

        $rma = Rma::factory()->create([
            'descricao' => 'RMA detalhe V1 caracterizacao',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'cliente_id' => $cliente->id,
            'modelo' => 'MODELO V1',
            'sn' => 'SN-V1-001',
            'os' => 'OS-V1-001',
            'pn' => 'PN-V1-001',
            'snid' => 'SNID-V1-001',
            'nf_remessa' => 'NFREMESSA-001',
            'nf_retorno_numero' => 'NFRETORNO-001',
            'nf_devolucao_de_venda' => 'NFDEVOL-001',
            'rastreio_ida' => 'RASTREIO-IDA-001',
            'destinatario_email_legado' => 'destinatario@example.test',
        ]);

        $response = $this->actingAs($usuario)
            ->get("/v1/rma/{$rma->id}")
            ->assertOk();

        $response->assertViewIs('temas.v1.rma.show');
        $response->assertSeeText('BOLETIM DE DEFEITO');
        foreach ([
            'NUMERO DO BD',
            'FABRICANTE',
            'DESCRICAO',
            'MODELO',
            'OS',
            'ORIGEM',
            'P/N',
            'TEMPO',
            'CLIENTE',
            'RASTREIO ENCAMINHADO',
            'RASTREIO RETORNO',
            'NF REMESSA',
            'NF RETORNO',
            'DESTINATARIO',
            'EMAIL DO DESTINATARIO',
            'PROTOCOLO',
            'DEFEITO RECLAMADO',
            'DATA RECEBIDO',
            'DATA CONCLUIDO',
        ] as $grupo) {
            $response->assertSeeText($grupo);
        }

        $response->assertSee('value="RMA detalhe V1 caracterizacao"', false);
        $response->assertSee('>Fabricante Detalhe V1<', false);
        $response->assertSee('value="MODELO V1"', false);
        $response->assertSee('value="SN-V1-001"', false);
        $response->assertSee('value="PN-V1-001"', false);
        $response->assertSee('value="SNID-V1-001"', false);
        $response->assertSee('value="NFREMESSA-001"', false);
        $response->assertSee('value="NFRETORNO-001"', false);
        $response->assertSee('value="destinatario@example.test"', false);
    }
}
