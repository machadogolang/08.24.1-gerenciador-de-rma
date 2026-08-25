<?php

namespace Tests\Unit\Rma;

use App\Rma\Dominio\Rma;
use Tests\TestCase;

class RmaTest extends TestCase
{
    private function rma(?string $origem): Rma
    {
        return new Rma(
            id: null,
            descricao: 'Descrição',
            fabricanteId: null,
            fornecedorId: null,
            modelo: null,
            sn: null,
            os: null,
            origem: $origem,
            empresa: null,
            clienteId: null,
            defeito: 'Defeito',
            observacao: null,
        );
    }

    public function test_fabricante_hgst_normaliza_para_hitachi_e_zera_origem_igual_ao_fabricante(): void
    {
        $rma = $this->rma('Hitachi');

        $normalizado = $rma->comNormalizacaoDeGravacao('HGST', null, null, null);

        $this->assertSame('Unknown', $normalizado->origem);
    }

    public function test_origem_igual_ao_fabricante_vira_unknown(): void
    {
        $rma = $this->rma('Western Digital');

        $normalizado = $rma->comNormalizacaoDeGravacao('Western Digital', null, null, null);

        $this->assertSame('Unknown', $normalizado->origem);
    }

    public function test_origem_igual_ao_fornecedor_vira_unknown(): void
    {
        $rma = $this->rma('Distribuidora XPTO');

        $normalizado = $rma->comNormalizacaoDeGravacao(null, 'Distribuidora XPTO', null, null);

        $this->assertSame('Unknown', $normalizado->origem);
    }

    public function test_origem_igual_ao_cliente_vira_cliente(): void
    {
        $rma = $this->rma('João da Silva');

        $normalizado = $rma->comNormalizacaoDeGravacao(null, null, 'João da Silva', null);

        $this->assertSame('Cliente', $normalizado->origem);
    }

    public function test_origem_igual_a_empresa_vira_cliente(): void
    {
        $rma = $this->rma('Empresa ABC');

        $normalizado = $rma->comNormalizacaoDeGravacao(null, null, null, 'Empresa ABC');

        $this->assertSame('Cliente', $normalizado->origem);
    }

    public function test_origem_cellsystem_vira_loja(): void
    {
        $this->assertSame('Loja', $this->rma('CELLSYSTEM')->comNormalizacaoDeGravacao(null, null, null, null)->origem);
        $this->assertSame('Loja', $this->rma('Cellsystem')->comNormalizacaoDeGravacao(null, null, null, null)->origem);
    }

    public function test_origem_leilao_receita_e_receita_federal_viram_leilao(): void
    {
        $this->assertSame('Leilão', $this->rma('Leilao')->comNormalizacaoDeGravacao(null, null, null, null)->origem);
        $this->assertSame('Leilão', $this->rma('Receita')->comNormalizacaoDeGravacao(null, null, null, null)->origem);
        $this->assertSame('Leilão', $this->rma('Receita Federal')->comNormalizacaoDeGravacao(null, null, null, null)->origem);
    }

    public function test_origem_fora_do_dominio_conhecido_permanece_inalterada(): void
    {
        $rma = $this->rma('Correios');

        $normalizado = $rma->comNormalizacaoDeGravacao('Seagate', 'Fornecedor X', 'Cliente Y', 'Empresa Z');

        $this->assertSame('Correios', $normalizado->origem);
    }

    public function test_normalizacao_nao_muda_o_objeto_original_metodo_puro(): void
    {
        $rma = $this->rma('CELLSYSTEM');

        $rma->comNormalizacaoDeGravacao(null, null, null, null);

        $this->assertSame('CELLSYSTEM', $rma->origem);
    }
}
