<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PermissaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_nao_autenticado_e_redirecionado_para_login(): void
    {
        $response = $this->get('/usuarios');

        $response->assertRedirect('/login');
    }

    public function test_usuario_autenticado_acessa_o_perfil(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $response = $this->actingAs($usuario)->get('/perfil');

        $response->assertOk();
    }

    #[DataProvider('papeisQuePodemGerenciar')]
    public function test_policy_gerenciar_reflete_pode_gerenciar_usuarios(Papel $papel, bool $esperado): void
    {
        $usuario = User::factory()->create(['papel' => $papel]);

        $this->assertSame($esperado, $usuario->papel->podeGerenciarUsuarios());
        $this->assertSame($esperado, Gate::forUser($usuario)->allows('gerenciar', User::class));
    }

    public static function papeisQuePodemGerenciar(): array
    {
        return [
            'Bloqueado não gerencia' => [Papel::Bloqueado, false],
            'Leitura não gerencia' => [Papel::Leitura, false],
            'Operador não gerencia' => [Papel::Operador, false],
            'Supervisor gerencia' => [Papel::Supervisor, true],
            'SuperAdministrador gerencia' => [Papel::SuperAdministrador, true],
        ];
    }

    public function test_usuario_sem_permissao_recebe_403_na_listagem_de_usuarios(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($usuario)->get('/usuarios');

        $response->assertForbidden();
    }

    public function test_supervisor_acessa_a_listagem_de_usuarios(): void
    {
        $supervisor = User::factory()->create(['papel' => Papel::Supervisor]);

        $response = $this->actingAs($supervisor)->get('/usuarios');

        $response->assertOk();
    }
}
