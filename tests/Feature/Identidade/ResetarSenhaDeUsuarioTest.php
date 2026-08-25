<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Aplicacao\ResetarSenhaDeUsuario;
use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ResetarSenhaDeUsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_ator_sem_permissao_de_gerenciar_recebe_403(): void
    {
        $ator = User::factory()->create(['papel' => Papel::Operador]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura, 'password' => Hash::make('antiga')]);

        $this->expectException(HttpException::class);

        app(ResetarSenhaDeUsuario::class)->resetar($ator, $alvo, 'nova-senha-123');
    }

    public function test_ator_valido_troca_a_senha_do_alvo(): void
    {
        $ator = User::factory()->create(['papel' => Papel::Supervisor]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura, 'password' => Hash::make('antiga')]);

        app(ResetarSenhaDeUsuario::class)->resetar($ator, $alvo, 'nova-senha-123');

        $this->assertTrue(Hash::check('nova-senha-123', $alvo->fresh()->password));
    }

    public function test_rota_de_reset_exige_papel_que_pode_gerenciar_usuarios(): void
    {
        $ator = User::factory()->create(['papel' => Papel::Operador]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura, 'password' => Hash::make('antiga')]);

        $response = $this->actingAs($ator)->post("/usuarios/{$alvo->id}/resetar-senha", [
            'nova_senha' => 'nova-senha-123',
            'nova_senha_confirmation' => 'nova-senha-123',
        ]);

        $response->assertForbidden();
        $this->assertTrue(Hash::check('antiga', $alvo->fresh()->password));
    }

    public function test_rota_de_reset_funciona_para_supervisor(): void
    {
        $ator = User::factory()->create(['papel' => Papel::Supervisor]);
        $alvo = User::factory()->create(['papel' => Papel::Leitura, 'password' => Hash::make('antiga')]);

        $response = $this->actingAs($ator)->post("/usuarios/{$alvo->id}/resetar-senha", [
            'nova_senha' => 'nova-senha-123',
            'nova_senha_confirmation' => 'nova-senha-123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('nova-senha-123', $alvo->fresh()->password));
    }
}
