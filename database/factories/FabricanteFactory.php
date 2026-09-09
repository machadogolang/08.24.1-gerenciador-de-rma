<?php

namespace Database\Factories;

use App\Compartilhado\Uf;
use App\Models\Fabricante;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fabricante>
 */
class FabricanteFactory extends Factory
{
    protected $model = Fabricante::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->company(),
            'representante' => fake()->name(),
            'cpf_cnpj' => fake()->numerify('##############'),
            'email' => fake()->safeEmail(),
            'email_secundario' => null,
            'telefone' => fake()->numerify('(##) #####-####'),
            'telefone2' => null,
            'cep' => fake()->numerify('#####-###'),
            'logradouro' => fake()->streetName(),
            'numero' => fake()->buildingNumber(),
            'complemento' => null,
            'bairro' => fake()->citySuffix(),
            'cidade' => fake()->city(),
            'uf' => fake()->randomElement(Uf::cases()),
            'www' => fake()->url(),
            'frete' => null,
            'cfop' => null,
            'observacao' => null,
            'politica_de_garantia' => null,
        ];
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
