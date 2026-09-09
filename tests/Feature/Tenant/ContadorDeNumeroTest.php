<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\ContadorDeRma;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Aplicacao\ReservarNumeroDeRma;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContadorDeNumeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresas_diferentes_tem_sequencias_independentes(): void
    {
        $empresaA = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        $reservar = app(ReservarNumeroDeRma::class);

        $this->assertSame(1, $reservar->reservar($empresaA->id));
        $this->assertSame(2, $reservar->reservar($empresaA->id));
        $this->assertSame(1, $reservar->reservar($empresaB->id));
        $this->assertSame(2, $reservar->reservar($empresaB->id));
    }

    public function test_contador_persiste_e_reabre_do_ultimo_valor(): void
    {
        $empresaA = Company::query()->where('nome', 'CellSystem')->sole();
        $reservar = app(ReservarNumeroDeRma::class);

        $reservar->reservar($empresaA->id);
        $reservar->reservar($empresaA->id);

        $this->assertSame(3, $reservar->reservar($empresaA->id));
        $this->assertSame(3, ContadorDeRma::query()->where('company_id', $empresaA->id)->value('proximo_numero') - 1);
    }

    public function test_contador_possui_uma_linha_por_empresa(): void
    {
        $empresaA = Company::query()->where('nome', 'CellSystem')->sole();
        $empresaB = Company::factory()->create(['nome' => 'Empresa B']);
        app(ReservarNumeroDeRma::class)->reservar($empresaA->id);
        app(ReservarNumeroDeRma::class)->reservar($empresaB->id);

        $this->assertSame(2, ContadorDeRma::query()->count());
    }

    public function test_rmas_criados_na_mesma_empresa_recebem_sequencia(): void
    {
        $usuario = User::factory()->create();

        foreach ([1, 2] as $indice) {
            $this->actingAs($usuario)->post('/rmas', [
                'descricao' => 'RMA sequencial '.$indice,
                'defeito' => 'Teste',
                'cliente_nome' => 'Cliente sequencial '.$indice,
            ])->assertRedirect();
        }

        $numeros = RmaEloquent::query()->withoutGlobalScopes()
            ->where('descricao', 'like', 'RMA sequencial %')
            ->orderBy('numero_da_empresa')
            ->pluck('numero_da_empresa')
            ->all();

        $this->assertSame([1, 2], $numeros);
    }
}
