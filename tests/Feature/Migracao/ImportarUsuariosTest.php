<?php

namespace Tests\Feature\Migracao;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarUsuarios;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarUsuariosTest extends MigracaoTestCase
{
    public function test_caso_feliz_traduz_permissao_e_app_corretamente(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Ana Operadora',
            'email' => 'ana@example.com',
            'anotacao' => 'nota livre',
            'permissao' => 2,
            'app' => '15.8.1',
            'data_de_cadastro' => '2019-05-01',
        ]);

        (new ImportarUsuarios)->executar($this->novoRelatorio());

        $user = User::query()->where('email', 'ana@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(Papel::Operador, $user->papel);
        $this->assertSame(TemaPreferido::V2, $user->tema_preferido);
        $this->assertSame('Ana Operadora', $user->name);
        $this->assertNotNull($user->password);
    }

    public function test_permissao_fora_do_dominio_vira_anomalia_e_usuario_bloqueado(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Fulano',
            'email' => 'fulano@example.com',
            'anotacao' => '',
            'permissao' => 99,
            'app' => '14.6.1',
            'data_de_cadastro' => '2019-05-01',
        ]);

        $relatorio = $this->novoRelatorio();
        (new ImportarUsuarios)->executar($relatorio);

        $user = User::query()->where('email', 'fulano@example.com')->first();

        $this->assertSame(Papel::Bloqueado, $user->papel);
        $this->assertCount(1, $relatorio->anomalias());
        $this->assertSame('usuario', $relatorio->anomalias()[0]['tabela']);
    }

    public function test_app_vazio_cai_no_fallback_tema_v1(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'SemTema',
            'email' => 'semtema@example.com',
            'anotacao' => '',
            'permissao' => 1,
            'app' => '',
            'data_de_cadastro' => '2019-05-01',
        ]);

        (new ImportarUsuarios)->executar($this->novoRelatorio());

        $user = User::query()->where('email', 'semtema@example.com')->first();

        $this->assertSame(TemaPreferido::V1, $user->tema_preferido);
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Ana Operadora',
            'email' => 'ana@example.com',
            'anotacao' => '',
            'permissao' => 2,
            'app' => '15.8.1',
            'data_de_cadastro' => '2019-05-01',
        ]);

        (new ImportarUsuarios)->executar($this->novoRelatorio());
        (new ImportarUsuarios)->executar($this->novoRelatorio());

        $this->assertSame(1, User::query()->where('email', 'ana@example.com')->count());
    }

    /**
     * ARQ-002 (`INV-RMA-10`) — dry-run roda a tradução inteira (inclusive detecção de
     * anomalia), mas nunca pode gravar usuário nem disparar e-mail real de redefinição
     * de senha (efeito não-transacional, não desfeito pelo rollback da transação).
     */
    public function test_dry_run_nao_grava_usuario_nem_envia_email_de_redefinicao(): void
    {
        Notification::fake();

        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Ana Operadora',
            'email' => 'ana@example.com',
            'anotacao' => '',
            'permissao' => 2,
            'app' => '15.8.1',
            'data_de_cadastro' => '2019-05-01',
        ]);

        $relatorio = $this->novoRelatorio();
        (new ImportarUsuarios)->executar($relatorio, dryRun: true);

        $this->assertSame(0, User::query()->count());
        $this->assertSame(1, $relatorio->contagemDestino()['users']);
        Notification::assertNothingSent();
    }

    /** ARQ-002 — dry-run também detecta anomalia de permissão sem gravar nada. */
    public function test_dry_run_detecta_anomalia_de_permissao_sem_gravar(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Fulano',
            'email' => 'fulano@example.com',
            'anotacao' => '',
            'permissao' => 99,
            'app' => '14.6.1',
            'data_de_cadastro' => '2019-05-01',
        ]);

        $relatorio = $this->novoRelatorio();
        (new ImportarUsuarios)->executar($relatorio, dryRun: true);

        $this->assertCount(1, $relatorio->anomalias());
        $this->assertSame(0, User::query()->count());
    }
}
