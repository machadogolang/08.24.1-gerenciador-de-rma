<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 8 — smoke: cada tela principal renderiza sem erro no TEMA V1, tanto pela rota
 * prefixada (`/v1/...`, tema forçado por `ResolverTemaAtivo`) quanto pelo fluxo normal
 * (usuário com `tema_preferido` = V1 acessando a rota sem prefixo). Cobre também o
 * login-gateway compartilhado (não pertence a nenhum tema).
 */
class RenderizaTemaV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_login_gateway_compartilhado_renderiza(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertViewIs('identidade.login');
    }

    public function test_usuario_com_tema_v1_e_redirecionado_para_view_do_tema_v1_apos_login(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->get('/usuarios');

        $response->assertOk();
        $response->assertViewIs('temas.v1.identidade.usuarios');
    }

    public function test_painel_de_rmas_v1_renderiza_via_rota_prefixada(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create(['descricao' => 'RMA tema V1']);

        $response = $this->actingAs($usuario)->get('/v1/rma?tipo=texto&valor=RMA');

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.index');
        $response->assertSeeText('RMA tema V1');
    }

    /**
     * CP8 (fase 2 V1) — achado real: reaproveitar `$ocultarTituloVisual` (só devia
     * controlar o H1) pra também controlar se `#JS-Novo` é renderizado fez o painel
     * global "Novo" sumir da Página Inicial (regressão introduzida e corrigida na
     * mesma sessão que fechou CP6, achada testando o CP8). `$omitirPainelNovoGlobal`
     * é a flag própria, só para `/rmas/create`.
     */
    public function test_painel_novo_global_continua_presente_na_pagina_inicial(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('id="JS-Novo"', false);
    }

    public function test_pagina_inicial_v1_so_mostra_resultado_vazio_depois_de_busca(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $inicial = $this->actingAs($usuario)->get('/v1/rma');
        $inicial->assertOk();
        $inicial->assertDontSeeText('Nenhum RMA encontrado.');

        $buscaSemResultado = $this->actingAs($usuario)->get('/v1/rma?tipo=texto&valor=INEXISTENTE');
        $buscaSemResultado->assertOk();
        $buscaSemResultado->assertSeeText('Nenhum RMA encontrado.');
    }

    public function test_alerta_de_protocolo_renderiza_a_tabela_historica_em_vez_da_lista_generica(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor QA']);
        $rma = Rma::factory()->create([
            'status' => Status::Recebido,
            'recebido_em' => now()->subDays(5),
            'protocolo' => 'PROTOCOLO-QA',
            'nfcompra' => '123',
            'nfvenda' => '456',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'descricao' => 'Produto ficticio da tabela',
            'modelo' => 'MODELO-QA',
            'os' => '5901',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="protocolo-aberto-nao-encaminhado"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-protocolo"', false);
        $response->assertSeeInOrder([
            'RECEBIDO', 'T', 'ORIGEM', 'NF C', 'NF V', 'FORNECEDOR',
            'FABRICANTE', 'DESCRICAO', 'MODELO', 'OS', 'A',
        ]);
        $response->assertSeeText('Fornecedor QA');
        $response->assertSeeText('Fabricante QA');
        $response->assertSeeText('Produto ficticio da tabela');
        $response->assertSee(rota_tema('rmas.show', ['rma' => $rma->id]), false);
        $response->assertDontSee("#{$rma->id} — Produto ficticio da tabela", false);
    }

    public function test_novo_rma_v1_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($usuario)->get('/v1/rma/create');

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.create');
    }

    public function test_detalhe_de_rma_v1_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create(['descricao' => 'Detalhe tema V1']);

        $response = $this->actingAs($usuario)->get("/v1/rma/{$rma->id}");

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.show');
        $response->assertSeeText('Detalhe tema V1');
    }

    public function test_clientes_v1_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Cliente::factory()->create(['nome' => 'Cliente tema V1']);

        $response = $this->actingAs($usuario)->get('/v1/parceiros/clientes');

        $response->assertOk();
        $response->assertViewIs('temas.v1.parceiros.index');
        $response->assertSeeText('Cliente tema V1');
    }

    public function test_perfil_v1_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador, 'anotacao' => 'Nota V1']);

        $response = $this->actingAs($usuario)->get('/v1/perfil');

        $response->assertOk();
        $response->assertViewIs('temas.v1.identidade.perfil');
        $response->assertSeeText('Nota V1');
    }
}
