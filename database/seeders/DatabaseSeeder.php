<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('Os dados de QA nao podem ser semeados em producao.');
        }

        $this->call(UserSeeder::class);
        $this->call(QaSeeder::class);
    }
}
