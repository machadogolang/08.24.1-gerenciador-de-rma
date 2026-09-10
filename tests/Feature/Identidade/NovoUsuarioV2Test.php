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
    public function tema_v1_nao_recebe_a_superficie_de_novo_usuario(): void
    {
        $adminV1 = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $this->actingAs($adminV1)->get('/usuarios/novo')->assertRedirect(route('identidade.usuarios.index'));
    }
}
