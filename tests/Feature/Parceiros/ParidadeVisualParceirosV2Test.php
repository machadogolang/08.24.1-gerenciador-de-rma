<?php

namespace Tests\Feature\Parceiros;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Rma\Dominio\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-PART-001..006 e PAR15-PART-DATA-001:
 * Prova que CREATE, EDIT e SHOW de parceiros são apresentações distintas e especializadas
 * nos Temas V1 e V2, com persistência de RG/IE e listagem nativa de RMAs associados.
 */
class ParidadeVisualParceirosV2Test extends TestCase
{
    use RefreshDatabase;

    private function operadorV2(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    private function operadorV1(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    #[Test]
    public function create_v2_usa_contrato_novo_parceiro_com_label_quem_voce_quer_cadastrar_e_botao_cadastrar(): void
    {
        $response = $this->actingAs($this->operadorV2())->get(route('v2.parceiros.clientes.create'));

        $response->assertOk();
        $response->assertSee('Quem voce quer cadastrar?');
        $response->assertSee('Cadastrar');
        $response->assertSee('images/rma/nome.png', false);
        $response->assertDontSee('Salvar');
    }

    #[Test]
    public function edit_v2_usa_contrato_ver_parceiro_com_label_nome_rgie_e_botao_salvar(): void
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'Cliente Teste V2',
            'rgie' => '12345678-9',
        ]);

        $response = $this->actingAs($this->operadorV2())->get(route('v2.parceiros.clientes.edit', $cliente));

        $response->assertOk();
        $response->assertSee("{$cliente->id} / {$cliente->nome}");
        $response->assertSee('name="rgie"', false);
        $response->assertSee('12345678-9');
        $response->assertSee('Salvar');
        $response->assertDontSee('Quem voce quer cadastrar?');
    }

    #[Test]
    public function edit_v2_persiste_campo_rgie(): void
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'Cliente Sem RG',
            'rgie' => null,
        ]);

        $response = $this->actingAs($this->operadorV2())->put(route('v2.parceiros.clientes.update', $cliente), [
            'nome' => 'Cliente Com RG Atualizado',
            'rgie' => '98765432-1',
            'cpf_cnpj' => '123.456.789-00',
        ]);

        $response->assertRedirect(route('v2.parceiros.clientes.index'));

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'nome' => 'Cliente Com RG Atualizado',
            'rgie' => '98765432-1',
        ]);
    }

    #[Test]
    public function show_v1_apresenta_layout_e_rmas_com_estetica_v1(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Cliente Show V1']);
        $rma = Rma::factory()->create([
            'cliente_id' => $cliente->id,
            'status' => Status::Entrada,
            'descricao' => 'Placa com defeito',
        ]);

        $response = $this->actingAs($this->operadorV1())->get(route('v1.parceiros.clientes.show', $cliente));

        $response->assertOk();
        $response->assertSee('detalhe-parceiro-v1', false);
        $response->assertSee('EDITAR');
        $response->assertSee('VOLTAR');
        $response->assertSee('RMAs associados');
        $response->assertSee($rma->descricao);
        $response->assertSee('Tabelinha-TR1', false);
    }

    #[Test]
    public function show_v2_apresenta_layout_e_rmas_com_estetica_v2(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Cliente Show V2']);
        $rma = Rma::factory()->create([
            'cliente_id' => $cliente->id,
            'status' => Status::Recebido,
            'descricao' => 'Memoria com erro',
        ]);

        $response = $this->actingAs($this->operadorV2())->get(route('v2.parceiros.clientes.show', $cliente));

        $response->assertOk();
        $response->assertSee('detalhe-parceiro-v2', false);
        $response->assertSee("{$cliente->id} / {$cliente->nome}");
        $response->assertSee('Editar');
        $response->assertSee('Voltar');
        $response->assertSee('RMAs associados');
        $response->assertSee($rma->descricao);
        $response->assertSee('TrZebrada1', false);
    }
}
