<?php

namespace Tests\Feature\Migracao;

use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Models\TentativaDeAcesso;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarLogsDeAcesso;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarUsuarios;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarLogsDeAcessoTest extends MigracaoTestCase
{
    public function test_caso_feliz_traduz_retorno_e_faz_soft_match_por_email(): void
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

        DB::connection('rma_legacy')->table('log')->insert([
            'email' => 'ana@example.com',
            'nome' => 'Ana',
            'data' => '2019-05-01 08:00:00',
            'sistema_operacional' => 'Windows',
            'ip' => '10.0.0.1',
            'navegador' => 'Chrome',
            'retorno' => 'permitido',
            'app' => '15.8.1',
        ]);

        (new ImportarLogsDeAcesso)->executar($this->novoRelatorio());

        $tentativa = TentativaDeAcesso::query()->where('email_informado', 'ana@example.com')->first();

        $this->assertNotNull($tentativa);
        $this->assertSame(ResultadoDeAcesso::Permitido, $tentativa->resultado);
        $this->assertNotNull($tentativa->user_id);
    }

    public function test_email_sem_usuario_correspondente_fica_sem_user_id(): void
    {
        DB::connection('rma_legacy')->table('log')->insert([
            'email' => 'fantasma@example.com',
            'data' => '2019-05-01 08:00:00',
            'ip' => '10.0.0.1',
            'navegador' => 'Chrome',
            'retorno' => 'negado',
        ]);

        (new ImportarLogsDeAcesso)->executar($this->novoRelatorio());

        $tentativa = TentativaDeAcesso::query()->where('email_informado', 'fantasma@example.com')->first();

        $this->assertNotNull($tentativa);
        $this->assertNull($tentativa->user_id);
    }

    public function test_retorno_fora_do_dominio_vira_anomalia_e_nao_migra_a_linha(): void
    {
        DB::connection('rma_legacy')->table('log')->insert([
            'email' => 'x@example.com',
            'data' => '2019-05-01 08:00:00',
            'ip' => '10.0.0.1',
            'navegador' => 'Chrome',
            'retorno' => 'lixo',
        ]);

        $relatorio = $this->novoRelatorio();
        (new ImportarLogsDeAcesso)->executar($relatorio);

        $this->assertSame(0, TentativaDeAcesso::query()->count());
        $this->assertNotEmpty($relatorio->anomalias());
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        DB::connection('rma_legacy')->table('log')->insert([
            'email' => 'x@example.com',
            'data' => '2019-05-01 08:00:00',
            'ip' => '10.0.0.1',
            'navegador' => 'Chrome',
            'retorno' => 'permitido',
        ]);

        (new ImportarLogsDeAcesso)->executar($this->novoRelatorio());
        (new ImportarLogsDeAcesso)->executar($this->novoRelatorio());

        $this->assertSame(1, TentativaDeAcesso::query()->count());
    }
}
