<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Fabricante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * VIS-V1-002/003/004 — painel "Novo" do TEMA V1 (`#JS-Novo`), fonte real
 * `legacy-source/14.6.1/menujs-top/novo.php` + `inc/menuright.php`. Cobre o que o
 * `ParidadeVisualTemaV1.spec.ts` (Playwright) não cobre: presença/ausência de campos
 * específicos e contrato HTML — geometria pixel a pixel fica com o Playwright.
 */
class PainelNovoTemaV1Test extends TestCase
{
    use RefreshDatabase;

    private function usuarioV1(): User
    {
        return User::factory()->create(['papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V1]);
    }

    public function test_painel_novo_aparece_oculto_em_qualquer_pagina_do_tema_v1(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.entrada'));

        $response->assertOk();
        // Não navega: a URL de destino é a mesma rota consultada (Entrada), o painel
        // só entra oculto no HTML — a prova de "não navegar" de fato é end-to-end via
        // Playwright (evento de clique real), isto aqui prova que o DOM contém o
        // painel oculto em QUALQUER página, pré-condição para o clique funcionar.
        $response->assertSee('id="JS-Novo"', false);
        $response->assertSee('style="display:none;"', false);
        $response->assertSee('id="menu-novo"', false);
    }

    public function test_painel_novo_nao_duplica_id_na_rota_fallback(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.create'));

        $response->assertOk();
        $ocorrencias = substr_count($response->getContent(), 'id="JS-Novo"');
        $this->assertSame(1, $ocorrencias, 'Só deve existir um elemento #JS-Novo no HTML (sem duplicar o do layout compartilhado).');
    }

    public function test_titulo_novo_rma_fica_visualmente_oculto_mas_presente_no_dom(): void
    {
        // VIS-V1-007 — o legado não tem heading "Novo RMA"; o H1 continua no DOM
        // (acessibilidade) mas com classe `sr-only` só nesta rota.
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.create'));

        $response->assertOk();
        $response->assertSee('titulo-v1 sr-only', false);
    }

    public function test_titulo_continua_visivel_nas_demais_paginas(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.entrada'));

        $response->assertOk();
        $response->assertDontSee('sr-only', false);
    }

    public function test_campos_do_grupo_a_e_pn_snid_estao_presentes_no_formulario(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.create'));

        $response->assertOk();
        foreach (['descricao', 'origem', 'nfcompra', 'nfcompra_emissao', 'snid', 'fabricante_id', 'nfvenda', 'nfvenda_emissao', 'sn', 'modelo', 'pn', 'os', 'empresa', 'defeito', 'cliente_nome', 'observacao', 'marcarestoque'] as $campo) {
            $response->assertSee('name="'.$campo.'"', false);
        }
    }

    /**
     * VIS-V1-003 — `Fornecedor` não existe em `menujs-top/novo.php` (só entrou no
     * formulário moderno); não deve aparecer na reconstrução do painel V1.
     */
    public function test_fornecedor_nao_aparece_no_painel_novo_do_tema_v1(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.create'));

        $response->assertOk();
        $response->assertDontSee('name="fornecedor_id"', false);
    }

    public function test_select_de_fabricante_lista_fabricantes_cadastrados_em_qualquer_pagina(): void
    {
        $usuario = $this->usuarioV1();
        Fabricante::factory()->create(['nome' => 'Fabricante Via Composer']);

        $response = $this->actingAs($usuario)->get(route('rmas.encaminhados'));

        $response->assertOk();
        $response->assertSee('Fabricante Via Composer');
    }

    public function test_geometria_minima_do_runtime_esta_presente(): void
    {
        $usuario = $this->usuarioV1();

        $response = $this->actingAs($usuario)->get(route('rmas.create'));

        $response->assertOk();
        $response->assertSee('class="tablenovo"', false);
        $response->assertSee('CRIAR BD', false);
        $response->assertSee('O ITEM E DO ESTOQUE', false);
    }
}
