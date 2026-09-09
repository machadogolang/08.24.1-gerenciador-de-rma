<?php

namespace Tests\Feature\Tenant;

use App\Identidade\Dominio\Papel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSementeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_cria_tenant_cell_system_deterministico(): void
    {
        $cell = Company::query()->where('nome', 'CellSystem')->sole();

        $this->assertTrue($cell->ativa);
    }

    public function test_user_seeder_preserva_papel_no_vinculo_cell_system(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $usuario = User::query()->where('papel', Papel::Supervisor)->firstOrFail();
        $vinculo = $usuario->empresas()->first();

        $this->assertSame('CellSystem', $vinculo->nome);
        $this->assertSame(Papel::Supervisor, $vinculo->pivot->papel);
    }
}
