<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnotacaoPessoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_salva_e_recupera_a_anotacao_do_proprio_usuario(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador, 'anotacao' => null]);

        $response = $this->actingAs($usuario)->put('/perfil/anotacao', [
            'anotacao' => 'Lembrar de ligar para o fornecedor X.',
        ]);

        $response->assertRedirect();
        $this->assertSame('Lembrar de ligar para o fornecedor X.', $usuario->fresh()->anotacao);

        $paginaDePerfil = $this->actingAs($usuario)->get('/perfil');
        $paginaDePerfil->assertSeeText('Lembrar de ligar para o fornecedor X.');
    }

    public function test_anotacao_e_pessoal_nao_afeta_outro_usuario(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador, 'anotacao' => null]);
        $outro = User::factory()->create(['papel' => Papel::Operador, 'anotacao' => null]);

        $this->actingAs($usuario)->put('/perfil/anotacao', [
            'anotacao' => 'Nota do usuário 1',
        ]);

        $this->assertNull($outro->fresh()->anotacao);
    }
}
