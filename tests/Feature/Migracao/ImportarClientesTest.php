<?php

namespace Tests\Feature\Migracao;

use App\Models\Cliente;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarClientes;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarClientesTest extends MigracaoTestCase
{
    public function test_caso_feliz_migra_dados_basicos_e_concatena_observacoes(): void
    {
        DB::connection('rma_legacy')->table('cliente')->insert([
            'nome' => 'Cliente Exemplo',
            'representante' => 'Fulano',
            'cpfcnpj' => '123',
            'email' => 'cliente@example.com',
            'fone' => '5100000000',
            'uf' => 'RS',
            'observacaoSGV' => 'nota sgv',
            'observacaoFR' => 'nota fr',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarClientes)->executar($this->novoRelatorio());

        $cliente = Cliente::query()->where('nome', 'Cliente Exemplo')->first();

        $this->assertNotNull($cliente);
        $this->assertSame('cliente@example.com', $cliente->email);
        $this->assertSame("SGV: nota sgv\nFR: nota fr", $cliente->observacao);
    }

    public function test_observacao_so_com_uma_parte_nao_grava_a_outra_vazia(): void
    {
        DB::connection('rma_legacy')->table('cliente')->insert([
            'nome' => 'Só SGV',
            'observacaoSGV' => 'nota sgv',
            'observacaoFR' => null,
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarClientes)->executar($this->novoRelatorio());

        $cliente = Cliente::query()->where('nome', 'Só SGV')->first();

        $this->assertSame('SGV: nota sgv', $cliente->observacao);
    }

    public function test_dedup_por_nome_normalizado_nao_duplica_variacao_de_espaco(): void
    {
        DB::connection('rma_legacy')->table('cliente')->insert([
            'nome' => 'Cliente  Duplicado ',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarClientes)->executar($this->novoRelatorio());
        (new ImportarClientes)->executar($this->novoRelatorio());

        $this->assertSame(1, Cliente::query()->count());
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        DB::connection('rma_legacy')->table('cliente')->insert([
            'nome' => 'Cliente A',
            'data_de_cadastro' => '2018-01-01',
        ]);
        DB::connection('rma_legacy')->table('cliente')->insert([
            'nome' => 'Cliente B',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarClientes)->executar($this->novoRelatorio());
        $relatorio = $this->novoRelatorio();
        (new ImportarClientes)->executar($relatorio);

        $this->assertSame(2, Cliente::query()->count());
        $this->assertSame(2, $relatorio->contagemOrigem()['cliente']);
    }
}
