<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T3-14 - Usuarios/admin no Tema V3:
 * Listagem, acoes contextuais (trocar papel, resetar senha) e criacao de novo usuario.
 */
class UsuariosV3Test extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    private function operador(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    public function test_listagem_de_usuarios_renderiza_em_v3_para_superadministrador(): void
    {
        $admin = $this->superAdmin();
        $alvo = User::factory()->create(['name' => 'Usuario Alvo V3']);

        $response = $this->actingAs($admin)->get('/v3/usuarios');

        $response->assertOk();
        $response->assertViewIs('temas.v3.identidade.usuarios');
        $response->assertSeeText('Administração - Usuários');
        $response->assertSeeText('Usuario Alvo V3');
        $response->assertSee('data-v3-tabela-usuarios', false);
        $response->assertSee('class="cartoes-usuario cartoes-parceiro"', false);
    }

    public function test_operador_comum_nao_acessa_listagem_de_usuarios_v3(): void
    {
        $operador = $this->operador();

        $response = $this->actingAs($operador)->get('/v3/usuarios');

        $response->assertForbidden();
    }

    public function test_formulario_de_novo_usuario_v3_renderiza(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get('/v3/usuarios/novo');

        $response->assertOk();
        $response->assertViewIs('temas.v3.identidade.usuarios-novo');
        $response->assertSeeText('Novo Usuário');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="papel"', false);
    }

    public function test_cadastrar_novo_usuario_via_v3_persiste(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post('/v3/usuarios', [
            'name' => 'Novo Operador V3 QA',
            'email' => 'novo-v3-qa@rma.local',
            'password' => 'senhaSegura123',
            'papel' => Papel::Operador->name,
        ]);

        $response->assertRedirect(route('identidade.usuarios.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'novo-v3-qa@rma.local',
            'name' => 'Novo Operador V3 QA',
        ]);
    }

    public function test_alterar_papel_de_usuario_via_v3_persiste(): void
    {
        $admin = $this->superAdmin();
        $alvo = User::factory()->create([
            'papel' => Papel::Leitura,
        ]);

        $response = $this->actingAs($admin)->put("/v3/usuarios/{$alvo->id}", [
            'papel' => Papel::Operador->name,
        ]);

        $response->assertSessionHas('status', 'Papel atualizado.');
        $this->assertSame(Papel::Operador, $alvo->fresh()->papel);
    }

    public function test_resetar_senha_de_usuario_via_v3(): void
    {
        $admin = $this->superAdmin();
        $alvo = User::factory()->create();

        $response = $this->actingAs($admin)->post("/v3/usuarios/{$alvo->id}/resetar-senha", [
            'nova_senha' => 'novaSenhaForte123',
            'nova_senha_confirmation' => 'novaSenhaForte123',
        ]);

        $response->assertSessionHas('status', 'Senha do usuário redefinida.');
    }
}
