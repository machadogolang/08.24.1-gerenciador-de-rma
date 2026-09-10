<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-USR-007 - Novo Usuario do TEMA V2 (fonte `15.8.1/subp/novo_usuario.php`):
 * nome/e-mail/senha/permissao com Policy, CSRF, validacao, vinculo `company_user` do
 * tenant corrente e sem SHA1.
 */
class NovoUsuarioV2Test extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return $extra + [
            'name' => 'Novo Usuario',
            'email' => 'novo@teste.local',
            'password' => 'senha-segura-1',
            'papel' => Papel::Operador->name,
        ];
    }

    private function supervisor(): User
    {
        return User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    #[Test]
    public function supervisor_cadastra_usuario_com_vinculo_no_tenant(): void
    {
        $response = $this->actingAs($this->supervisor())->post('/v2/usuarios', $this->payload());

        $response->assertRedirect(route('identidade.usuarios.index'));

        $novo = User::query()->where('email', 'novo@teste.local')->first();
        $this->assertNotNull($novo);
        $this->assertSame('Novo Usuario', $novo->name);
        $this->assertSame(Papel::Operador, $novo->papel);
        $this->assertNotSame('senha-segura-1', $novo->password);
        $this->assertTrue(Hash::check('senha-segura-1', $novo->password));

        $this->assertSame(1, CompanyUser::query()->where('user_id', $novo->id)->count());
    }

    #[Test]
    public function supervisor_nao_pode_criar_superadministrador(): void
    {
        $response = $this->actingAs($this->supervisor())->post('/v2/usuarios', $this->payload([
            'papel' => Papel::SuperAdministrador->name,
        ]));

        $response->assertForbidden();
        $this->assertNull(User::query()->where('email', 'novo@teste.local')->first());
    }

    #[Test]
    public function tela_preserva_o_contrato_visual_do_15_8_1(): void
    {
        // PAR15-USR-009 - icones do breadcrumb/label e os TRES rotulos historicos
        // do 15.8.1, sem sufixo tecnico do enum, com `Leitura` selecionado.
        $response = $this->actingAs($this->supervisor())->get('/v2/usuarios/novo');

        $response->assertOk();
        $response->assertSee('images/rma/novo_usuario.png', false);
        $response->assertSee('images/rma/nome.png', false);
        $response->assertSee('Quem voce quer cadastrar?', false);

        foreach (['Bloqueado', 'Leitura', 'Leitura e modificacao'] as $rotulo) {
            $response->assertSee($rotulo, false);
        }

        // Nenhum rotulo historico vem acompanhado do nome tecnico do enum.
        $response->assertDontSee('Leitura e modificacao (Operador)', false);
        $response->assertDontSee('Leitura e modificacao (Supervisor)', false);
    }

    #[Test]
    public function supervisor_nao_recebe_a_opcao_superadministrador(): void
    {
        $response = $this->actingAs($this->supervisor())->get('/v2/usuarios/novo');

        $response->assertOk();
        $response->assertSee(Papel::Supervisor->name, false);
        $response->assertDontSee('value="SuperAdministrador"', false);
    }

    #[Test]
    public function superadministrador_recebe_todos_os_papeis(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($admin)->get('/v2/usuarios/novo');

        $response->assertOk();

        foreach (['Bloqueado', 'Leitura', 'Operador', 'Supervisor', 'SuperAdministrador'] as $papel) {
            $response->assertSee('value="'.$papel.'"', false);
        }
    }

    #[Test]
    public function email_duplicado_e_rejeitado(): void
    {
        User::factory()->create(['email' => 'novo@teste.local']);

        $response = $this->actingAs($this->supervisor())->post('/v2/usuarios', $this->payload());

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function operador_nao_pode_cadastrar_usuario(): void
    {
        $operador = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $this->actingAs($operador)->post('/v2/usuarios', $this->payload())->assertForbidden();
    }

    #[Test]
    public function tema_v1_recebe_superficie_de_novo_usuario_na_estetica_v1(): void
    {
        $adminV1 = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($adminV1)->get('/v1/usuarios/novo');
        $response->assertOk();
        $response->assertViewIs('temas.v1.identidade.usuarios-novo');
        $response->assertSee('Novo usuario');
        $response->assertSee('CADASTRAR USUÁRIO');
    }
}
