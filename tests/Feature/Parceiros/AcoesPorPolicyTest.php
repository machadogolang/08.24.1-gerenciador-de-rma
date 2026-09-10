<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UX-001/UX-002 (P8) - apresentacao consciente de Policy e confirmacao de remocao.
 * A autorizacao real continua no controller/Policies; aqui e so a superficie.
 */
class AcoesPorPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_leitura_ve_o_registro_mas_nao_as_acoes_de_escrita(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Fornecedor::factory()->create(['nome' => 'Fornecedor Visivel QA']);

        $response = $this->actingAs($usuario)->get('/parceiros/fornecedores');

        $response->assertOk();
        $response->assertSeeText('Fornecedor Visivel QA');
        $response->assertSeeText('Ver');
        $response->assertDontSee('acao--perigo', false);
        $response->assertDontSee('>Editar</a>', false);
    }

    public function test_operador_ve_acoes_de_escrita_com_confirmacao_de_remocao(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Fornecedor::factory()->create(['nome' => 'Fornecedor Editavel QA']);

        $response = $this->actingAs($usuario)->get('/parceiros/fornecedores');

        $response->assertOk();
        $response->assertSee('acao--perigo', false);
        $response->assertSee('data-confirmar-remocao', false);
    }
}
