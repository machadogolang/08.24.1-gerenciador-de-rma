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

    /**
     * ARQ-003 (`INV-RMA-10`) — Supervisor não pode se autopromover a
     * SuperAdministrador por URL direta.
     */
    public function test_supervisor_nao_pode_promover_a_si_proprio_a_superadministrador(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);

        $response = $this->actingAs($supervisor)->put("/usuarios/{$supervisor->id}", [
            'papel' => Papel::SuperAdministrador->name,
        ]);

        $response->assertForbidden();
        $this->assertSame(Papel::Supervisor, $supervisor->fresh()->papel);
    }

    /** ARQ-003 — Supervisor não pode promover outro usuário a SuperAdministrador. */
    public function test_supervisor_nao_pode_promover_outro_usuario_a_superadministrador(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $alvo = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($supervisor)->put("/usuarios/{$alvo->id}", [
            'papel' => Papel::SuperAdministrador->name,
        ]);

        $response->assertForbidden();
        $this->assertSame(Papel::Operador, $alvo->fresh()->papel);
    }

    /** ARQ-003 — Supervisor não pode alterar o papel de um SuperAdministrador existente. */
    public function test_supervisor_nao_pode_alterar_papel_de_superadministrador(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);
        $superAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador]);

        $response = $this->actingAs($supervisor)->put("/usuarios/{$superAdmin->id}", [
            'papel' => Papel::Operador->name,
        ]);

        $response->assertForbidden();
        $this->assertSame(Papel::SuperAdministrador, $superAdmin->fresh()->papel);
    }

    /** ARQ-003 — SuperAdministrador continua podendo gerenciar outro SuperAdministrador. */
    public function test_superadministrador_pode_alterar_papel_de_outro_superadministrador(): void
    {
        $superAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador]);
        $outroSuperAdmin = User::factory()->create(['papel' => Papel::SuperAdministrador]);

        $response = $this->actingAs($superAdmin)->put("/usuarios/{$outroSuperAdmin->id}", [
            'papel' => Papel::Supervisor->name,
        ]);

        $response->assertRedirect();
        $this->assertSame(Papel::Supervisor, $outroSuperAdmin->fresh()->papel);
    }
}
