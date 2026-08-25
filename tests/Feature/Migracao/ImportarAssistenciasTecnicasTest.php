<?php

namespace Tests\Feature\Migracao;

use App\Models\AssistenciaTecnica;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarAssistenciasTecnicas;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarAssistenciasTecnicasTest extends MigracaoTestCase
{
    public function test_caso_feliz_migra_dados_basicos(): void
    {
        DB::connection('rma_legacy')->table('assistencia_tecnica')->insert([
            'nome' => 'Assistência Central',
            'email' => 'contato@central.com',
            'politicadegarantia' => '90 dias',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarAssistenciasTecnicas)->executar($this->novoRelatorio());

        $assistencia = AssistenciaTecnica::query()->where('nome', 'Assistência Central')->first();

        $this->assertNotNull($assistencia);
        $this->assertSame('90 dias', $assistencia->politica_de_garantia);
    }

    public function test_dedup_por_nome_normalizado(): void
    {
        DB::connection('rma_legacy')->table('assistencia_tecnica')->insert([
            'nome' => '  Assistência   Central  ',
            'data_de_cadastro' => '2018-01-01',
        ]);
        DB::connection('rma_legacy')->table('assistencia_tecnica')->insert([
            'nome' => 'assistência central',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarAssistenciasTecnicas)->executar($this->novoRelatorio());

        $this->assertSame(1, AssistenciaTecnica::query()->count());
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        DB::connection('rma_legacy')->table('assistencia_tecnica')->insert([
            'nome' => 'Assistência Única',
            'data_de_cadastro' => '2018-01-01',
        ]);

        (new ImportarAssistenciasTecnicas)->executar($this->novoRelatorio());
        (new ImportarAssistenciasTecnicas)->executar($this->novoRelatorio());

        $this->assertSame(1, AssistenciaTecnica::query()->count());
    }
}
