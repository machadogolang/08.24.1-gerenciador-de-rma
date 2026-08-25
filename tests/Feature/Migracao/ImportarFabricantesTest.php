<?php

namespace Tests\Feature\Migracao;

use App\Models\Fabricante;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarFabricantes;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarFabricantesTest extends MigracaoTestCase
{
    public function test_caso_feliz_migra_dados_com_observacao_unica(): void
    {
        DB::connection('rma_legacy')->table('fabricante')->insert([
            'nome' => 'Seagate',
            'email' => 'contato@seagate.com',
            'email2' => 'suporte@seagate.com',
            'politicadegarantia' => '12 meses',
            'observacao' => 'nota única',
            'uf' => 'SP',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarFabricantes)->executar($this->novoRelatorio());

        $fabricante = Fabricante::query()->where('nome', 'Seagate')->first();

        $this->assertNotNull($fabricante);
        $this->assertSame('suporte@seagate.com', $fabricante->email_secundario);
        $this->assertSame('12 meses', $fabricante->politica_de_garantia);
        $this->assertSame('nota única', $fabricante->observacao);
    }

    public function test_dedup_por_nome_normalizado_case_insensitive(): void
    {
        Fabricante::query()->create(['nome' => 'Seagate']);

        DB::connection('rma_legacy')->table('fabricante')->insert([
            'nome' => 'seagate',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarFabricantes)->executar($this->novoRelatorio());

        $this->assertSame(1, Fabricante::query()->count());
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        DB::connection('rma_legacy')->table('fabricante')->insert([
            'nome' => 'Western Digital',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarFabricantes)->executar($this->novoRelatorio());
        (new ImportarFabricantes)->executar($this->novoRelatorio());

        $this->assertSame(1, Fabricante::query()->count());
    }
}
