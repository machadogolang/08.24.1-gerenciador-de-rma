<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T3-13 - Parceiros no Tema V3 sob prefixo /v3:
 * Listagem, abas/segmentos, busca/filtro, detalhe com RMAs associados e formularios em secoes.
 */
class ParceirosV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(Papel $papel = Papel::Operador): User
    {
        return User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    public function test_atalho_v3_parceiros_redireciona_para_clientes(): void
    {
        $response = $this->actingAs($this->usuario())
            ->get('/v3/parceiros');

        $response->assertRedirect(route('v3.parceiros.clientes.index'));
    }

    public function test_listagem_de_parceiros_renderiza_com_tema_v3_e_abas_de_navegacao(): void
    {
        $usuario = $this->usuario();
        Cliente::factory()->create(['nome' => 'Cliente Alfa V3']);

        $response = $this->actingAs($usuario)
            ->get('/v3/parceiros/clientes');

        $response->assertOk();
        $response->assertViewIs('temas.v3.parceiros.index');
        $response->assertSeeText('Parceiros - Clientes');
        $response->assertSeeText('Cliente Alfa V3');
        $response->assertSee('data-v3-filtro-tabela', false);
        $response->assertSee('class="cartoes-parceiro"', false);
        $response->assertSee('class="tabela-v3"', false);

        // Verifica as abas de categorias
        $response->assertSee(route('v3.parceiros.clientes.index'), false);
        $response->assertSee(route('v3.parceiros.fornecedores.index'), false);
        $response->assertSee(route('v3.parceiros.fabricantes.index'), false);
        $response->assertSee(route('v3.parceiros.assistencias-tecnicas.index'), false);
    }

    public function test_listagens_de_todas_as_categorias_de_parceiros_respondem_em_v3(): void
    {
        $usuario = $this->usuario();
        Fornecedor::factory()->create(['nome' => 'Fornecedor Beta V3']);
        Fabricante::factory()->create(['nome' => 'Fabricante Gama V3']);
        AssistenciaTecnica::factory()->create(['nome' => 'Assistencia Delta V3']);

        $this->actingAs($usuario)
            ->get('/v3/parceiros/fornecedores')
            ->assertOk()
            ->assertViewIs('temas.v3.parceiros.index')
            ->assertSeeText('Fornecedor Beta V3');

        $this->actingAs($usuario)
            ->get('/v3/parceiros/fabricantes')
            ->assertOk()
            ->assertViewIs('temas.v3.parceiros.index')
            ->assertSeeText('Fabricante Gama V3');

        $this->actingAs($usuario)
            ->get('/v3/parceiros/assistencias-tecnicas')
            ->assertOk()
            ->assertViewIs('temas.v3.parceiros.index')
            ->assertSeeText('Assistencia Delta V3');
    }

    public function test_detalhe_de_parceiro_renderiza_secoes_e_rmas_vinculados(): void
    {
        $usuario = $this->usuario();
        $fornecedor = Fornecedor::factory()->create([
            'nome' => 'Fornecedor Detalhe V3',
            'representante' => 'Carlos Representante',
            'telefone' => '11999998888',
            'email' => 'carlos@fornecedor.com',
            'cidade' => 'Sao Paulo',
            'observacao' => 'Observacao detalhada do fornecedor V3',
        ]);

        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'fornecedor_id' => $fornecedor->id,
            'descricao' => 'RMA associado ao fornecedor V3',
            'modelo' => 'MODELO-FORN-01',
        ]);

        $response = $this->actingAs($usuario)
            ->get("/v3/parceiros/fornecedores/{$fornecedor->id}");

        $response->assertOk();
        $response->assertViewIs('temas.v3.parceiros.show');
        $response->assertSeeText('Fornecedor Detalhe V3');
        $response->assertSeeText('Carlos Representante');
        $response->assertSeeText('carlos@fornecedor.com');
        $response->assertSeeText('Identificacao');
        $response->assertSeeText('Contato');
        $response->assertSeeText('Endereco');
        $response->assertSeeText('RMAs Associados');
        $response->assertSeeText('RMA associado ao fornecedor V3');
        $response->assertSeeText('MODELO-FORN-01');
        $response->assertSee(route('v3.rmas.show', $rma->id), false);
    }

    public function test_formulario_de_criacao_e_persistencia_de_parceiro_v3(): void
    {
        $usuario = $this->usuario(Papel::Operador);

        // Visualizar formulario de criacao
        $response = $this->actingAs($usuario)
            ->get('/v3/parceiros/clientes/create');

        $response->assertOk();
        $response->assertViewIs('temas.v3.parceiros._form');
        $response->assertSeeText('Novo cliente');
        $response->assertSeeText('Identificacao');
        $response->assertSeeText('Contato');
        $response->assertSeeText('Endereco');
        $response->assertSee('name="nome"', false);

        // Submeter criacao
        $postResponse = $this->actingAs($usuario)
            ->post('/v3/parceiros/clientes', [
                'nome' => 'Novo Cliente Criado V3',
                'representante' => 'Joao Silva',
                'telefone' => '11988887777',
                'email' => 'joao@cliente.com',
                'cidade' => 'Campinas',
            ]);

        $postResponse->assertRedirect(route('v3.parceiros.clientes.index'));
        $this->assertDatabaseHas('clientes', [
            'nome' => 'Novo Cliente Criado V3',
            'cidade' => 'Campinas',
        ]);
    }

    public function test_formulario_de_edicao_e_atualizacao_de_parceiro_v3(): void
    {
        $usuario = $this->usuario(Papel::Operador);
        $cliente = Cliente::factory()->create([
            'nome' => 'Cliente Original V3',
            'cidade' => 'Santos',
        ]);

        // Visualizar formulario de edicao
        $response = $this->actingAs($usuario)
            ->get("/v3/parceiros/clientes/{$cliente->id}/edit");

        $response->assertOk();
        $response->assertViewIs('temas.v3.parceiros._form');
        $response->assertSeeText('Editar cliente: Cliente Original V3');
        $response->assertSee('value="Cliente Original V3"', false);

        // Submeter atualizacao
        $putResponse = $this->actingAs($usuario)
            ->put("/v3/parceiros/clientes/{$cliente->id}", [
                'nome' => 'Cliente Atualizado V3',
                'cidade' => 'Guaruja',
            ]);

        $putResponse->assertRedirect(route('v3.parceiros.clientes.index'));
        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'nome' => 'Cliente Atualizado V3',
            'cidade' => 'Guaruja',
        ]);
    }

    public function test_remocao_de_parceiro_v3_respeita_policy(): void
    {
        $operador = $this->usuario(Papel::Operador);
        $cliente = Cliente::factory()->create(['nome' => 'Cliente Para Remover V3']);

        $deleteResponse = $this->actingAs($operador)
            ->delete("/v3/parceiros/clientes/{$cliente->id}");

        $deleteResponse->assertRedirect(route('v3.parceiros.clientes.index'));
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }
}
