<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\StatusDeLancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR-V2-DETAIL-02 - o detalhe RMA do Tema V2 e formulario operacional editavel
 * (Legacy 15.8.1/page/rma.php), nao uma pagina de leitura. Usuario sem Policy de
 * escrita enxerga os mesmos grupos com controles desabilitados; escrita real passa
 * pelo caso de uso moderno (EditarRma), CSRF e PUT.
 */
class ParidadeDetalheRmaV2Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(TemaPreferido $tema, Papel $papel): User
    {
        return User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => $tema,
        ]);
    }

    private function criarRma(): Rma
    {
        return Rma::factory()->create([
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
    }

    #[Test]
    public function detalhe_v2_exibe_formulario_operacional_com_controles_reais(): void
    {
        $usuario = $this->usuario(TemaPreferido::V2, Papel::Leitura);
        $rma = $this->criarRma();

        $response = $this->actingAs($usuario)
            ->get("/v2/rma/{$rma->id}")
            ->assertOk();

        $response->assertViewIs('temas.v2.rma.show');
        $response->assertSee('detalhe-rma-v2__form', false);
        $response->assertSee('name="descricao"', false);
        $response->assertSee('name="modelo"', false);
        $response->assertSee('name="fabricante_id"', false);
        $response->assertSee('name="sn"', false);
        $response->assertSee('name="origem"', false);
        $response->assertSee('name="prioridade"', false);
        $response->assertSee('name="marcarestoque"', false);
        $response->assertSee('name="credito_disponivel"', false);
        $response->assertSee('name="nfvenda"', false);
        $response->assertSee('name="nfcompra"', false);
        $response->assertSee('name="nf_remessa"', false);
        $response->assertSee('name="nf_retorno_numero"', false);
        $response->assertSee('name="lancadoretorno"', false);
        $response->assertSee('name="solucao"', false);
        $response->assertSee('name="observacao"', false);
        $response->assertSee('value="SN-V2-001"', false);
        $response->assertSee('value="NFREMESSA-V2"', false);
        $response->assertSee('value="NFRETORNO-V2"', false);
        $response->assertSee('disabled', false);
        $response->assertSee('Somente leitura');
        $response->assertDontSee('SALVAR</button>', false);
    }

    #[Test]
    public function operador_altera_salva_e_o_reload_persiste_campos_do_detalhe_v2(): void
    {
        $usuario = $this->usuario(TemaPreferido::V2, Papel::Operador);
        $rma = $this->criarRma();

        $resposta = $this->actingAs($usuario)
            ->put("/v2/rma/{$rma->id}", [
                'descricao' => 'Descricao editada V2',
                'modelo' => 'MODELO EDITADO V2',
                'fabricante_id' => null,
                'fornecedor_id' => null,
                'sn' => 'SN-EDITADO-V2',
                'os' => 'OS-EDITADA',
                'origem' => 'Loja',
                'empresa' => 'Cellsystem',
                'cliente_nome' => '',
                'defeito' => 'Defeito editado V2',
                'observacao' => 'Observacao editada e persistida no detalhe V2.',
                'prioridade' => 'normal',
                'marcarestoque' => '0',
                'credito_disponivel' => '1',
                'nfvenda' => 'NFV-EDITADA',
                'nfvenda_emissao' => '01/09/2026',
                'nfcompra' => 'NFC-EDITADA',
                'nfcompra_emissao' => '02/09/2026',
                'lancadoretorno' => 'sim',
                'acao' => 'salvar',
            ])
            ->assertRedirect("/v2/rma/{$rma->id}");

        $this->assertSame('Descricao editada V2', $rma->fresh()->descricao);
        $this->assertSame('MODELO EDITADO V2', $rma->fresh()->modelo);
        $this->assertSame('SN-EDITADO-V2', $rma->fresh()->sn);
        $this->assertSame('OS-EDITADA', $rma->fresh()->os);
        $this->assertSame('Defeito editado V2', $rma->fresh()->defeito);
        $this->assertSame(Prioridade::Media, $rma->fresh()->prioridade);
        $this->assertFalse($rma->fresh()->marcarestoque);
        $this->assertTrue($rma->fresh()->credito_disponivel);
        $this->assertSame(StatusDeLancamento::Sim, $rma->fresh()->lancadoretorno);

        $this->actingAs($usuario)
            ->get("/v2/rma/{$rma->id}")
            ->assertOk()
            ->assertSee('value="MODELO EDITADO V2"', false)
            ->assertSee('value="SN-EDITADO-V2"', false)
            ->assertSee('value="OS-EDITADA"', false)
            ->assertSee('Observacao editada e persistida no detalhe V2.', false);
    }

    #[Test]
    public function usuario_de_leitura_nao_consegue_alterar_pela_policy(): void
    {
        $usuario = $this->usuario(TemaPreferido::V2, Papel::Leitura);
        $rma = $this->criarRma();

        $this->actingAs($usuario)
            ->put("/v2/rma/{$rma->id}", [
                'descricao' => 'Tentativa de leitura',
                'defeito' => 'Tentativa',
                'acao' => 'salvar',
            ])
            ->assertForbidden();

        $this->assertSame('RMA detalhe V2 caracterizacao', $rma->fresh()->descricao);
    }
}
