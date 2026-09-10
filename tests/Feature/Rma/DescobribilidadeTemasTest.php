<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UF-20 - Teste de Descobribilidade nos Temas:
 * Valida que cada capacidade unificada possui links/botoes alcancaveis na
 * interface de navegacao do tema respectivo (menus, cabeçalhos ou barras laterais).
 */
class DescobribilidadeTemasTest extends TestCase
{
    use RefreshDatabase;

    public function test_descobribilidade_no_tema_v1(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($supervisor)->get(route('rmas.index'));
        $response->assertOk();

        // 1. Topo V1 (#TOPO)
        $response->assertSee(route('rmas.index'), false);
        $response->assertSee(route('rmas.create'), false);
        $response->assertSee(route('rmas.entrada'), false);
        $response->assertSee(route('rmas.recebidos'), false); // UF-07 (GAP-V1-01)
        $response->assertSee(route('rmas.encaminhados'), false);
        $response->assertSee(route('rmas.aguardando-credito'), false);
        $response->assertSee(route('rmas.concluidos'), false);

        // 2. Menu de Sessão V1 (#JS-Sessao)
        $response->assertSee(rota_tema('parceiros.fornecedores.index'), false);
        $response->assertSee(rota_tema('parceiros.fabricantes.index'), false);
        $response->assertSee(rota_tema('parceiros.assistencias-tecnicas.index'), false);
        $response->assertSee(rota_tema('parceiros.clientes.index'), false);
        $response->assertSee(route('rmas.controle.index'), false);
        $response->assertSee(route('rmas.credito.index'), false);
        $response->assertSee(route('rmas.relatorios.rcd'), false);
        $response->assertSee(route('rmas.relatorios.rpec'), false);
        $response->assertSee(route('rmas.relatorios.rmpe'), false);
        $response->assertSee(route('rmas.relatorios.index'), false);
        $response->assertSee(rota_tema('rmas.logistica.frete-porto-alegre'), false); // UF-16
        $response->assertSee(rota_tema('rmas.ajuda'), false); // UF-18
        $response->assertSee(rota_tema('identidade.usuarios.index'), false);
    }

    public function test_descobribilidade_no_tema_v2(): void
    {
        $supervisor = User::factory()->create([
            'papel' => Papel::Supervisor,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($supervisor)->get(route('v2.rmas.index'));
        $response->assertOk();

        // 1. Navbar V2
        $response->assertSee('#inicio', false);
        $response->assertSee('#pesquisar', false);
        $response->assertSee('#novo_rma', false);
        $response->assertSee('#entrada', false);
        $response->assertSee('#recebido', false);
        $response->assertSee('#encaminhado', false);
        $response->assertSee('#concluido', false);

        // 2. Dropdown Menu V2
        $response->assertSee(route('rmas.credito.index'), false);
        $response->assertSee(rota_tema('parceiros.assistencias-tecnicas.index'), false);
        $response->assertSee(rota_tema('parceiros.fabricantes.index'), false);
        $response->assertSee(rota_tema('parceiros.fornecedores.index'), false);
        $response->assertSee(rota_tema('parceiros.clientes.index'), false);
        $response->assertSee(rota_tema('rmas.relatorios.index'), false);
        $response->assertSee(rota_tema('identidade.anotacoes.index'), false);
        $response->assertSee(rota_tema('identidade.controle.index'), false);
        $response->assertSee(rota_tema('identidade.usuarios.index'), false);
        $response->assertSee(rota_tema('rmas.logistica.frete-porto-alegre'), false); // UF-16
        $response->assertSee(rota_tema('rmas.ajuda'), false); // UF-18

        // 3. Relatorios V2: RCD, RPEC e RMPE sao alcancaveis na pagina de relatorios
        $responseRelatorios = $this->actingAs($supervisor)->get(route('v2.rmas.relatorios.index'));
        $responseRelatorios->assertOk();
        $responseRelatorios->assertSee(route('v2.rmas.relatorios.rcd'), false);
        $responseRelatorios->assertSee(route('v2.rmas.relatorios.rpec'), false);
        $responseRelatorios->assertSee(route('v2.rmas.relatorios.rmpe'), false);

        // 4. Painel Lateral V2: Transporte Porto Alegre alcancavel
        $response->assertSee('portoalegre_r', false);
    }
}
