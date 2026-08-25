<?php

namespace Database\Factories;

use App\Compartilhado\Uf;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

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
            'telefone' => fake()->numerify('(##) #####-####'),
            'telefone2' => null,
            'cep' => fake()->numerify('#####-###'),
            'logradouro' => fake()->streetName(),
            'numero' => fake()->buildingNumber(),
            'complemento' => null,
            'bairro' => fake()->citySuffix(),
            'cidade' => fake()->city(),
            'uf' => fake()->randomElement(Uf::cases()),
            'observacao' => null,
        ];
    }
}
