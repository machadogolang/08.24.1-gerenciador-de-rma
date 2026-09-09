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
 * PAR-DET-V1-EDIT-01/ACTION-01/STOCK-01 - persistencia do detalhe V1 em linha.
 */
class EditarDetalheV1Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function detalhe_v1_persiste_campos_fiscais_logisticos_e_controles(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['descricao' => 'Detalhe V1', 'defeito' => 'Defeito V1']);

        $response = $this->actingAs($operador)->put("/v1/rma/{$rma->id}", [
            'descricao' => 'Detalhe V1 editado',
            'defeito' => 'Defeito V1 editado',
            'os' => 'OS-EDIT',
            'pn' => 'PN-EDIT',
            'snid' => 'SNID-EDIT',
            'protocolo' => 'PROTO-EDIT',
            'snretorno' => 'SNR-EDIT',
            'nfcompra' => 'NFC-EDIT',
            'nfcompra_chave' => 'CHAVE-COMPRA-EDIT',
            'nfvenda_chave' => 'CHAVE-VENDA-EDIT',
            'nf_remessa' => 'NFREMESSA-EDIT',
            'nf_remessa_chave' => 'CHAVE-REMESSA-EDIT',
            'nf_retorno_numero' => 'NFRETORNO-EDIT',
            'nf_retorno_chave' => 'CHAVE-RETORNO-EDIT',
            'rastreio_ida' => 'RASTREIO-IDA-EDIT',
            'rastreio_retorno' => 'RASTREIO-RETORNO-EDIT',
            'nf_devolucao_de_venda' => 'NFDEVOL-EDIT',
            'nf_entrada_cliente_legado' => 'NFENTRADA-EDIT',
            'nf_retorno_cliente_legado' => 'NFSAIDA-EDIT',
            'destinatario_email_legado' => 'dest-edit@example.test',
            'valor' => '123,45',
            'marcarestoque' => '0',
            'acao' => 'salvar',
        ]);

        $response->assertRedirect("/v1/rma/{$rma->id}");
        $this->assertDatabaseHas('rmas', [
            'id' => $rma->id,
            'descricao' => 'Detalhe V1 editado',
            'os' => 'OS-EDIT',
            'pn' => 'PN-EDIT',
            'snid' => 'SNID-EDIT',
            'protocolo' => 'PROTO-EDIT',
            'snretorno' => 'SNR-EDIT',
            'nfcompra' => 'NFC-EDIT',
            'nfcompra_chave' => 'CHAVE-COMPRA-EDIT',
            'nfvenda_chave' => 'CHAVE-VENDA-EDIT',
            'nf_remessa' => 'NFREMESSA-EDIT',
            'nf_remessa_chave' => 'CHAVE-REMESSA-EDIT',
            'nf_retorno_numero' => 'NFRETORNO-EDIT',
            'nf_retorno_chave' => 'CHAVE-RETORNO-EDIT',
            'rastreio_ida' => 'RASTREIO-IDA-EDIT',
            'rastreio_retorno' => 'RASTREIO-RETORNO-EDIT',
            'nf_devolucao_de_venda' => 'NFDEVOL-EDIT',
            'nf_entrada_cliente_legado' => 'NFENTRADA-EDIT',
            'nf_retorno_cliente_legado' => 'NFSAIDA-EDIT',
            'destinatario_email_legado' => 'dest-edit@example.test',
            'valor' => 123.45,
            'marcarestoque' => false,
        ]);
    }

    #[Test]
    public function rodape_do_detalhe_v1_pode_receber_rma(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = Rma::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($operador)->put("/v1/rma/{$rma->id}", [
            'descricao' => $rma->descricao,
            'defeito' => $rma->defeito,
            'acao' => 'receber',
        ]);

        $response->assertRedirect("/v1/rma/{$rma->id}");
        $this->assertDatabaseHas('rmas', ['id' => $rma->id, 'status' => 'Recebido']);
        $this->assertNotNull($rma->fresh()->recebido_em);
    }
}
