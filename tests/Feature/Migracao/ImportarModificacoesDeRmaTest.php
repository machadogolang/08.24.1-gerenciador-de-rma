<?php

namespace Tests\Feature\Migracao;

use App\Models\ModificacaoDeRma;
use App\Rma\Dominio\AcaoDeModificacao;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarModificacoesDeRma;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarRmas;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarUsuarios;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarModificacoesDeRmaTest extends MigracaoTestCase
{
    private function migrarUsuarioEBd(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'anotacao' => '',
            'permissao' => 2,
            'app' => '15.8.1',
            'data_de_cadastro' => '2019-05-01',
        ]);
        (new ImportarUsuarios)->executar($this->novoRelatorio());

        DB::connection('rma_legacy')->table('bd')->insert([
            'numero' => 2001,
            'descricao' => 'Roteador',
            'defeito' => 'não conecta',
            'status' => 'entrada',
            'entrada' => '2019-05-01 09:00:00',
            'dtaalt' => '2019-05-01 09:00:00',
            'creditodisponivel' => 0,
        ]);
        (new ImportarRmas)->executar($this->novoRelatorio());
    }

    public function test_disponivel_confirma_que_a_tabela_da_fase_7_existe(): void
    {
        $this->assertTrue((new ImportarModificacoesDeRma)->disponivel());
    }

    public function test_caso_feliz_grava_acao_edicao_e_snapshot_json(): void
    {
        $this->migrarUsuarioEBd();

        DB::connection('rma_legacy')->table('modificacao')->insert([
            'numero' => 2001,
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'dta' => '2019-05-02 10:00:00',
            'descricao' => 'Roteador editado',
            'app' => '15.8.1',
            'so' => 'Windows',
            'fabricante' => 'TP-Link',
            'modelo' => 'X1',
            'sn' => 'SN1',
            'ip' => '10.0.0.1',
            'navegador' => 'Chrome',
        ]);

        (new ImportarModificacoesDeRma)->executar($this->novoRelatorio());

        $modificacao = ModificacaoDeRma::query()->first();

        $this->assertNotNull($modificacao);
        $this->assertSame(AcaoDeModificacao::Edicao, $modificacao->acao);
        $this->assertSame('Roteador editado', $modificacao->estado_apos['descricao']);
    }

    public function test_modificacao_orfa_sem_rma_correspondente_e_descartada_e_reportada(): void
    {
        $this->migrarUsuarioEBd();

        DB::connection('rma_legacy')->table('modificacao')->insert([
            'numero' => 9999,
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'dta' => '2019-05-02 10:00:00',
        ]);

        $relatorio = $this->novoRelatorio();
        (new ImportarModificacoesDeRma)->executar($relatorio);

        $this->assertSame(0, ModificacaoDeRma::query()->count());
        $this->assertNotEmpty($relatorio->anomalias());
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        $this->migrarUsuarioEBd();

        DB::connection('rma_legacy')->table('modificacao')->insert([
            'numero' => 2001,
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'dta' => '2019-05-02 10:00:00',
        ]);

        (new ImportarModificacoesDeRma)->executar($this->novoRelatorio());
        (new ImportarModificacoesDeRma)->executar($this->novoRelatorio());

        $this->assertSame(1, ModificacaoDeRma::query()->count());
    }
}
