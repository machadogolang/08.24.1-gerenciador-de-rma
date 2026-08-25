<?php

namespace Tests\Feature\Migracao;

use App\Models\Cliente;
use App\Models\ModificacaoDeRma;
use App\Models\Rma;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class MigrarLegadoComandoTest extends MigracaoTestCase
{
    private function inserirFixtureCompleta(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'anotacao' => '',
            'permissao' => 2,
            'app' => '15.8.1',
            'data_de_cadastro' => '2019-05-01',
        ]);

        DB::connection('rma_legacy')->table('fabricante')->insert([
            'nome' => 'Seagate',
            'data_de_cadastro' => '2019-05-01',
        ]);

        DB::connection('rma_legacy')->table('bd')->insert([
            'numero' => 3001,
            'descricao' => 'HD externo',
            'fabricante' => 'Seagate',
            'defeito' => 'não liga',
            'status' => 'entrada',
            'entrada' => '2019-05-01 09:00:00',
            'dtaalt' => '2019-05-01 09:00:00',
            'creditodisponivel' => 0,
            'cliente' => 'Cliente Fixture',
        ]);

        DB::connection('rma_legacy')->table('log')->insert([
            'email' => 'ana@example.com',
            'data' => '2019-05-01 08:00:00',
            'ip' => '10.0.0.1',
            'navegador' => 'Chrome',
            'retorno' => 'permitido',
        ]);

        DB::connection('rma_legacy')->table('modificacao')->insert([
            'numero' => 3001,
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'dta' => '2019-05-02 10:00:00',
        ]);
    }

    public function test_comando_roda_os_8_passos_em_ordem_e_grava_tudo(): void
    {
        Storage::fake('local');
        $this->inserirFixtureCompleta();

        $this->artisan('rma:migrar-legado')->assertSuccessful();

        $this->assertSame(1, User::query()->where('email', 'ana@example.com')->count());
        $this->assertSame(1, Cliente::query()->where('nome', 'Cliente Fixture')->count());
        $this->assertSame(1, Rma::query()->where('numero_legado', 3001)->count());
        $this->assertSame(1, TentativaDeAcesso::query()->where('email_informado', 'ana@example.com')->count());
        $this->assertSame(1, ModificacaoDeRma::query()->count());

        Storage::disk('local')->assertExists(
            collect(Storage::disk('local')->files('migracao'))->first()
        );
    }

    public function test_somente_roda_um_unico_importador(): void
    {
        $this->inserirFixtureCompleta();

        $this->artisan('rma:migrar-legado', ['--somente' => 'usuarios'])->assertSuccessful();

        $this->assertSame(1, User::query()->where('email', 'ana@example.com')->count());
        $this->assertSame(0, Rma::query()->count());
    }

    public function test_dry_run_nao_grava_nada_e_nao_salva_relatorio(): void
    {
        Storage::fake('local');
        $this->inserirFixtureCompleta();

        $this->artisan('rma:migrar-legado', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Rma::query()->count());
        $this->assertEmpty(Storage::disk('local')->files('migracao'));
    }

    public function test_rodar_duas_vezes_e_idempotente_ponta_a_ponta(): void
    {
        $this->inserirFixtureCompleta();

        $this->artisan('rma:migrar-legado')->assertSuccessful();
        $this->artisan('rma:migrar-legado')->assertSuccessful();

        $this->assertSame(1, User::query()->where('email', 'ana@example.com')->count());
        $this->assertSame(1, Rma::query()->where('numero_legado', 3001)->count());
        $this->assertSame(1, ModificacaoDeRma::query()->count());
    }
}
