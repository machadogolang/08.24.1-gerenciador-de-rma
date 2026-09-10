<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Fabricante;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UF-07 (GAP-V1-01 / CAP-RMA-004) - Fila dedicada de Recebidos no Tema V1.
 * Valida capacidade, autorizacao, descobribilidade e apresentacao V1.
 */
class RecebidosV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_recebidos_v1_lista_apenas_status_recebido(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $fabricante = Fabricante::factory()->create(['nome' => 'Kingston']);

        $rmaRecebido = Rma::factory()->create([
            'descricao' => 'SSD 240GB Kingston',
            'status' => Status::Recebido,
            'fabricante_id' => $fabricante->id,
            'recebido_em' => now(),
        ]);

        $rmaEntrada = Rma::factory()->create([
            'descricao' => 'Memoria RAM DDR4',
            'status' => Status::Entrada,
        ]);

        $rmaEncaminhado = Rma::factory()->create([
            'descricao' => 'Placa Mae Asus',
            'status' => Status::Encaminhado,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.recebidos'));

        $response->assertOk();
        $response->assertSee('SSD 240GB Kingston');
        $response->assertDontSee('Memoria RAM DDR4');
        $response->assertDontSee('Placa Mae Asus');
    }

    public function test_recebidos_v1_apresenta_layout_e_tabela_v1(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        Rma::factory()->create([
            'descricao' => 'Item Recebido Teste',
            'status' => Status::Recebido,
            'recebido_em' => now(),
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.recebidos'));

        $response->assertOk();
        $response->assertSee('images/tema-v1/recebido.png');
        $response->assertSee('Tabelinha-Table');
        $response->assertSee('Item Recebido Teste');
        $response->assertSee('Os bds recebidos abaixo estao em analise para encaminhamento');
    }

    public function test_recebidos_v1_exibe_mensagem_quando_vazio(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.recebidos'));

        $response->assertOk();
        $response->assertSee('Nenhum RMA encontrado.');
    }

    public function test_menu_superior_topo_v1_contem_link_para_recebidos(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.index'));

        $response->assertOk();
        $response->assertSee(route('rmas.recebidos'));
        $response->assertSee('Recebido');
    }

    public function test_v1_rmas_recebidos_url_qa_retorna_200(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V2, // mesmo com V2 preferido, /v1 forca V1
        ]);

        Rma::factory()->create([
            'descricao' => 'Item QA V1',
            'status' => Status::Recebido,
            'recebido_em' => now(),
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rmas-recebidos');

        $response->assertOk();
        $response->assertSee('Tabelinha-Table');
        $response->assertSee('images/tema-v1/recebido.png');
    }
}
