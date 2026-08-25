<?php

namespace Database\Seeders;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cria um usuário por papel, para QA manual (login real de cada nível de acesso).
     * Senha padrão para todos: "password".
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('O UserSeeder de QA nao pode ser executado em producao.');
        }

        foreach (Papel::cases() as $papel) {
            User::query()->updateOrCreate(
                ['email' => sprintf('%s@rma.local', strtolower($papel->name))],
                [
                    'name' => $papel->name,
                    'password' => Hash::make('password'),
                    'papel' => $papel,
                    'tema_preferido' => TemaPreferido::V1,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
