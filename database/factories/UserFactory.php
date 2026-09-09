<?php

namespace Database\Factories;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
            'anotacao' => null,
        ];
    }

    /**
     * EVO-SAAS-001 (S4) - todo usuário de teste nasce vinculado ao tenant semente
     * CellSystem, preservando o papel do usuário no vínculo. Isso mantém a suíte atual
     * autenticável quando o middleware de tenant passa a falhar sem vínculo.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $usuario): void {
            $cell = Company::query()->firstOrCreate(
                ['nome' => 'CellSystem'],
                ['documento' => null, 'ativa' => true],
            );

            $usuario->empresas()->syncWithoutDetaching([
                $cell->id => ['papel' => $usuario->papel, 'ativo' => true],
            ]);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Define o papel do usuário gerado.
     */
    public function papel(Papel $papel): static
    {
        return $this->state(fn (array $attributes) => [
            'papel' => $papel,
        ]);
    }
}
