<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Company;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rma>
 */
class RmaFactory extends Factory
{
    protected $model = Rma::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descricao' => fake()->sentence(4),
            'fabricante_id' => null,
            'fornecedor_id' => null,
            'modelo' => fake()->bothify('MODELO-####'),
            'sn' => fake()->bothify('SN########'),
            'os' => fake()->numerify('#####'),
            'origem' => fake()->randomElement(['Cliente', 'Loja', 'Leilão', 'Unknown']),
            'empresa' => null,
            'cliente_id' => null,
            'defeito' => fake()->sentence(3),
            'observacao' => null,
        ];
    }

    public function comFabricante(): self
    {
        return $this->state(fn () => ['fabricante_id' => Fabricante::factory()]);
    }

    public function comFornecedor(): self
    {
        return $this->state(fn () => ['fornecedor_id' => Fornecedor::factory()]);
    }

    public function comCliente(): self
    {
        return $this->state(fn () => ['cliente_id' => Cliente::factory()]);
    }

    /**
     * EVO-SAAS-001 (S5) — dados de teste/fixture nascem no tenant semente CellSystem
     * para continuarem visíveis quando o Global Scope estiver ativo (web autenticado).
     * Cenários de Empresa B devem sobrescrever com `forceFill(['tenant_id' => ...])`
     * antes de salvar.
     */
    public function configure(): static
    {
        return $this->afterMaking(function ($model): void {
            $cell = \App\Models\Company::query()->firstOrCreate(
                ['nome' => 'CellSystem'],
                ['documento' => null, 'ativa' => true],
            );

            $model->forceFill(['tenant_id' => $cell->id]);
        });
    }
}
