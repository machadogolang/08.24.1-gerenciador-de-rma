<?php

namespace Tests\Feature\Seeders;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_base_ficticia_deterministica_para_qa_local(): void
    {
        (new DatabaseSeeder)->run();

        $this->assertSame(5, User::query()->count());
        $this->assertSame(30, Cliente::query()->count());
        $this->assertSame(10, Fabricante::query()->count());
        $this->assertSame(10, Fornecedor::query()->count());
        $this->assertSame(5, AssistenciaTecnica::query()->count());
        $this->assertSame(60, Rma::query()->count());

        $supervisor = User::query()->where('email', 'supervisor@rma.local')->sole();
        $this->assertSame(Papel::Supervisor, $supervisor->papel);
        $this->assertTrue(Hash::check('password', $supervisor->password));

        $primeiroRma = Rma::query()->oldest('id')->firstOrFail();
        $this->assertSame('Ficticio QA 001', $primeiroRma->descricao);
        $this->assertNotNull($primeiroRma->cliente_id);
        $this->assertNotNull($primeiroRma->fabricante_id);
        $this->assertNotNull($primeiroRma->fornecedor_id);
        $this->assertNotNull($primeiroRma->destinatario_id);

        // CP13 (fase 2 V1) — pelo menos 1 registro com `solucao=PendenteCredito`
        // pra Aguardando Crédito nunca ficar vazio no seed padrão.
        $this->assertSame(1, Rma::query()->where('solucao', \App\Rma\Dominio\Solucao::PendenteCredito)->count());
    }

    public function test_recusa_semeadura_de_qa_em_producao(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Os dados de QA nao podem ser semeados em producao.');
        (new DatabaseSeeder)->run();
    }
}
