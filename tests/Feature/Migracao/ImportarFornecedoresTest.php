<?php

namespace Tests\Feature\Migracao;

use App\Models\Fornecedor;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarFornecedores;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarFornecedoresTest extends MigracaoTestCase
{
    public function test_caso_feliz_concatena_observacoes_sgv_fr(): void
    {
        DB::connection('rma_legacy')->table('fornecedor')->insert([
            'nome' => 'Distribuidora XPTO',
            'email' => 'contato@xpto.com',
            'observacaoSGV' => 'nota sgv',
            'observacaoFR' => 'nota fr',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarFornecedores)->executar($this->novoRelatorio());

        $fornecedor = Fornecedor::query()->where('nome', 'Distribuidora XPTO')->first();

        $this->assertNotNull($fornecedor);
        $this->assertSame("SGV: nota sgv\nFR: nota fr", $fornecedor->observacao);
    }

    public function test_dedup_case_insensitive_atualiza_em_vez_de_duplicar(): void
    {
        DB::connection('rma_legacy')->table('fornecedor')->insert([
            'nome' => 'DISTRIBUIDORA XPTO',
            'email' => 'novo@xpto.com',
            'data_de_cadastro' => '2018-01-01',
        ]);

        Fornecedor::query()->create(['nome' => 'Distribuidora Xpto', 'email' => 'antigo@xpto.com']);

        (new ImportarFornecedores)->executar($this->novoRelatorio());

        $this->assertSame(1, Fornecedor::query()->count());
        $this->assertSame('novo@xpto.com', Fornecedor::query()->first()->email);
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        DB::connection('rma_legacy')->table('fornecedor')->insert([
            'nome' => 'Fornecedor Único',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarFornecedores)->executar($this->novoRelatorio());
        (new ImportarFornecedores)->executar($this->novoRelatorio());

        $this->assertSame(1, Fornecedor::query()->count());
    }
}
