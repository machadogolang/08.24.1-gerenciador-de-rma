<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-SEC-001 e PAR15-NOTE-001:
 * Validação do contrato visual e comportamental de Trocar Senha e Anotações no Tema V2.
 */
class ParidadeVisualPerfilAnotacoesV2Test extends TestCase
{
    use RefreshDatabase;

    private function operadorV2(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    #[Test]
    public function tela_alterar_senha_v2_preserva_contrato_visual_e_icone_do_15_8_1(): void
    {
        $usuario = $this->operadorV2();

        $response = $this->actingAs($usuario)->get(route('v2.identidade.perfil.senha'));

        $response->assertOk();
        $response->assertSee('images/rma/senha2.png', false);
        $response->assertSee('images/rma/editar.png', false);
        $response->assertSee('Alterar senha');
        $response->assertSee('Nova Senha');
        $response->assertSee('Confirmar nova senha');
        $response->assertSee('Cadastrar');
    }

    #[Test]
    public function tela_anotacoes_v2_preserva_estrutura_historica_e_autosave(): void
    {
        $usuario = $this->operadorV2();

        $response = $this->actingAs($usuario)->get(route('v2.identidade.anotacoes.index'));

        $response->assertOk();
        $response->assertSee('QUADRO DE ANOTACOES');
        $response->assertSee('class="anotacao"', false);
        $response->assertSee('data-anotacao-autosave', false);
        $response->assertSee('data-anotacao-url', false);
        $response->assertSee('Salvar anotacao');
    }
}
