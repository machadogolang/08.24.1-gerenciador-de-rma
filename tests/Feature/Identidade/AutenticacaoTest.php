<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_valido_autentica_e_registra_permitido(): void
    {
        $usuario = User::factory()->create([
            'email' => 'operador@rma.local',
            'password' => Hash::make('senha-correta'),
            'papel' => Papel::Operador,
        ]);

        $response = $this->post('/login', [
            'email' => 'operador@rma.local',
            'password' => 'senha-correta',
        ]);

        $this->assertAuthenticatedAs($usuario);
        $response->assertRedirect();

        $this->assertDatabaseHas('tentativas_de_acesso', [
            'user_id' => $usuario->id,
            'email_informado' => 'operador@rma.local',
            'resultado' => ResultadoDeAcesso::Permitido->name,
        ]);
    }

    public function test_papel_bloqueado_nega_antes_de_checar_senha_e_registra_bloqueado(): void
    {
        $usuario = User::factory()->create([
            'email' => 'bloqueado@rma.local',
            'password' => Hash::make('senha-correta'),
            'papel' => Papel::Bloqueado,
        ]);

        $response = $this->post('/login', [
            'email' => 'bloqueado@rma.local',
            'password' => 'senha-errada-qualquer',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('tentativas_de_acesso', [
            'user_id' => $usuario->id,
            'email_informado' => 'bloqueado@rma.local',
            'resultado' => ResultadoDeAcesso::Bloqueado->name,
        ]);
    }

    public function test_senha_errada_nega_e_registra_negado(): void
    {
        $usuario = User::factory()->create([
            'email' => 'operador2@rma.local',
            'password' => Hash::make('senha-correta'),
            'papel' => Papel::Operador,
        ]);

        $response = $this->post('/login', [
            'email' => 'operador2@rma.local',
            'password' => 'senha-errada',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('tentativas_de_acesso', [
            'user_id' => $usuario->id,
            'email_informado' => 'operador2@rma.local',
            'resultado' => ResultadoDeAcesso::Negado->name,
        ]);
    }

    public function test_email_inexistente_nega_com_mensagem_generica_sem_enumerar(): void
    {
        $response = $this->post('/login', [
            'email' => 'nao-existe@rma.local',
            'password' => 'qualquer-coisa',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('tentativas_de_acesso', [
            'user_id' => null,
            'email_informado' => 'nao-existe@rma.local',
            'resultado' => ResultadoDeAcesso::Negado->name,
        ]);

        // Mensagem genérica fixa: não pode revelar se o e-mail existe ou não. Ambos os
        // cenários (e-mail inexistente / senha errada) devem produzir exatamente a
        // mesma mensagem de validação.
        $mensagemGenerica = 'As credenciais informadas não conferem.';
        $response->assertSessionHasErrors(['email' => $mensagemGenerica]);

        $usuario = User::factory()->create([
            'email' => 'existe@rma.local',
            'password' => Hash::make('senha-correta'),
            'papel' => Papel::Operador,
        ]);

        $response2 = $this->post('/login', [
            'email' => 'existe@rma.local',
            'password' => 'senha-errada',
        ]);

        $response2->assertSessionHasErrors(['email' => $mensagemGenerica]);
    }

    public function test_logout_desautentica(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $this->actingAs($usuario)->post('/logout');

        $this->assertGuest();
    }
}
