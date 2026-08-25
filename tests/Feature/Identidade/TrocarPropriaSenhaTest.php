<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrocarPropriaSenhaTest extends TestCase
{
    use RefreshDatabase;

    public function test_senha_atual_correta_troca_e_persiste(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'password' => Hash::make('senha-antiga'),
        ]);

        $response = $this->actingAs($usuario)->put('/perfil/senha', [
            'senha_atual' => 'senha-antiga',
            'nova_senha' => 'senha-nova-123',
            'nova_senha_confirmation' => 'senha-nova-123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('senha-nova-123', $usuario->fresh()->password));
    }

    public function test_senha_atual_errada_nega(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'password' => Hash::make('senha-antiga'),
        ]);

        $response = $this->actingAs($usuario)->put('/perfil/senha', [
            'senha_atual' => 'senha-errada',
            'nova_senha' => 'senha-nova-123',
            'nova_senha_confirmation' => 'senha-nova-123',
        ]);

        $response->assertSessionHasErrors('senha_atual');
        $this->assertTrue(Hash::check('senha-antiga', $usuario->fresh()->password));
    }

    /**
     * Esta é a prova de que a regressão de TEMA V2 (RN-21, "SET ... SET ..." inválido)
     * não foi herdada: um único UPDATE, via Eloquent, sempre válido — se a V3
     * replicasse o SQL quebrado, esta troca simplesmente não persistiria.
     */
    public function test_a_troca_de_senha_persiste_de_fato_no_banco(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'password' => Hash::make('senha-antiga'),
        ]);

        $this->actingAs($usuario)->put('/perfil/senha', [
            'senha_atual' => 'senha-antiga',
            'nova_senha' => 'senha-nova-123',
            'nova_senha_confirmation' => 'senha-nova-123',
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $usuario->id,
            'password' => Hash::make('senha-antiga'),
        ]);
    }
}
