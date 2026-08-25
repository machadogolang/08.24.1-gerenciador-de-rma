<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GerenciarUsuariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_nao_ve_superadministrador_na_listagem(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor, 'name' => 'Sup']);
        $superAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador, 'name' => 'Root']);

        $response = $this->actingAs($supervisor)->get('/usuarios');

        $response->assertOk();
        $response->assertSeeText('Sup');
        $response->assertDontSeeText('Root');
    }

    public function test_superadministrador_ve_todos_incluindo_outros_superadministradores(): void
    {
        $superAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador, 'name' => 'RootUm']);
        $outroSuperAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador, 'name' => 'RootDois']);

        $response = $this->actingAs($superAdmin)->get('/usuarios');

        $response->assertOk();
        $response->assertSeeText('RootUm');
        $response->assertSeeText('RootDois');
    }

    public function test_supervisor_pode_trocar_o_papel_de_um_usuario(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($supervisor)->put("/usuarios/{$alvo->id}", [
            'papel' => Papel::Operador->name,
        ]);

        $response->assertRedirect();
        $this->assertSame(Papel::Operador, $alvo->fresh()->papel);
    }

    public function test_usuario_sem_permissao_nao_pode_trocar_papel(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($operador)->put("/usuarios/{$alvo->id}", [
            'papel' => Papel::Operador->name,
        ]);

        $response->assertForbidden();
        $this->assertSame(Papel::Leitura, $alvo->fresh()->papel);
    }
}
