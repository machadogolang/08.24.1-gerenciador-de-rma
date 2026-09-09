<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscarRmasTest extends TestCase
{
    use RefreshDatabase;

    public function test_busca_por_texto(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'HD com clique estranho']);
        Rma::factory()->create(['descricao' => 'Fonte queimada']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=texto&valor=clique');

        $response->assertOk();
        $response->assertSee('HD com clique estranho');
        $response->assertDontSee('Fonte queimada');
    }

    public function test_busca_por_serial(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA com serial', 'sn' => 'ABC123']);
        Rma::factory()->create(['descricao' => 'RMA com outro serial', 'sn' => 'XYZ999']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=serial&valor=ABC123');

        $response->assertOk();
        $response->assertSee('RMA com serial');
        $response->assertDontSee('RMA com outro serial');
    }

    public function test_busca_vazia_nao_lista_nada(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'Não deveria aparecer']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=texto&valor=');

        $response->assertOk();
        $response->assertDontSee('Não deveria aparecer');
    }

    public function test_busca_por_nota_fiscal_consulta_campos_fiscais_reais(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'NF compra localizada', 'nfcompra' => 'NF-9981']);
        Rma::factory()->create(['descricao' => 'NF venda localizada', 'nfvenda' => 'NF-9981']);
        Rma::factory()->create(['descricao' => 'NF remessa localizada', 'nf_remessa' => 'NF-9981']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=nota_fiscal&valor=9981');

        $response->assertOk();
        $response->assertSee('NF compra localizada');
        $response->assertSee('NF venda localizada');
        $response->assertSee('NF remessa localizada');
    }

    public function test_busca_por_nota_fiscal_nao_casa_apenas_os(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'So OS tem o numero', 'os' => '9981']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=nota_fiscal&valor=9981');

        $response->assertOk();
        $response->assertDontSee('So OS tem o numero');
    }

    public function test_busca_por_os(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'OS localizada', 'os' => 'OS-1234']);
        Rma::factory()->create(['descricao' => 'RMA sem essa OS', 'os' => 'OS-9999']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=os&valor=OS-1234');

        $response->assertOk();
        $response->assertSee('OS localizada');
        $response->assertDontSee('RMA sem essa OS');
    }

    public function test_campo_nf_do_tema_v1_mapeia_para_nota_fiscal(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'NF pelo campo NF', 'nfcompra' => 'NF-555']);
        Rma::factory()->create(['descricao' => 'OS com mesmo numero', 'os' => 'NF-555']);

        $response = $this->actingAs($usuario)->get('/rmas?campo=NF&valor=NF-555');

        $response->assertOk();
        $response->assertSee('NF pelo campo NF');
        $response->assertDontSee('OS com mesmo numero');
    }

    public function test_campo_os_do_tema_v1_mapeia_para_os(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'OS pelo campo OS', 'os' => 'OS-777']);
        Rma::factory()->create(['descricao' => 'NF com mesmo numero', 'nfcompra' => 'OS-777']);

        $response = $this->actingAs($usuario)->get('/rmas?campo=os&valor=OS-777');

        $response->assertOk();
        $response->assertSee('OS pelo campo OS');
        $response->assertDontSee('NF com mesmo numero');
    }
}
