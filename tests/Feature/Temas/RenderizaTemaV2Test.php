<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Cliente;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 8 - smoke: cada tela principal renderiza sem erro no TEMA V2, tanto pela rota
 * prefixada (`/v2/...`) quanto pelo fluxo normal (usuário com `tema_preferido` = V2).
 * O painel de RMAs (`temas/v2/rma/index.blade.php`) é o único com os 7 tab-panes -
 * confirma que todos aparecem no mesmo HTML (ver design.md "Mecanismo de navegação
 * por tema").
 */
class RenderizaTemaV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_com_tema_v2_e_redirecionado_para_view_do_tema_v2_apos_login(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V2,
        ]);

        $response = $this->actingAs($usuario)->get('/usuarios');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.usuarios');
    }

    public function test_painel_de_rmas_v2_renderiza_com_os_7_tab_panes(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create(['descricao' => 'RMA tema V2']);

        $response = $this->actingAs($usuario)->get('/v2/rma?tipo=texto&valor=RMA');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.index');
        $response->assertSeeText('RMA tema V2');

        foreach (['inicio', 'pesquisar', 'novo_rma', 'entrada', 'recebido', 'encaminhado', 'concluido'] as $painel) {
            $response->assertSee('id="' . $painel . '"', false);
        }
    }

    public function test_novo_rma_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);

        $response = $this->actingAs($usuario)->get('/v2/rma/create');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.create');
    }

    public function test_tabela_compartilhada_de_prioridade_tambem_renderiza_no_tema_v2(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'prioridade' => Prioridade::Alta,
            'descricao' => 'Prioridade compartilhada QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="prioridade-alta-sem-encaminhar"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-abertos-nao-encaminhados"', false);
        $response->assertSeeText('Prioridade compartilhada QA');
    }

    public function test_tabela_compartilhada_sem_numero_de_serie_tambem_renderiza_no_tema_v2(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Recebido,
            'sn' => null,
            'descricao' => 'Sem numero de serie compartilhado QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="sem-numero-de-serie"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-abertos-nao-encaminhados"', false);
        $response->assertSeeText('Sem numero de serie compartilhado QA');
    }

    public function test_tabela_compartilhada_sem_nota_fiscal_tambem_renderiza_no_tema_v2(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Recebido,
            'nfcompra' => null,
            'nfvenda' => null,
            'descricao' => 'Sem nota fiscal compartilhado QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="sem-nota-fiscal"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-sem-nota"', false);
        $response->assertSeeText('Sem nota fiscal compartilhado QA');
    }

    public function test_tabela_compartilhada_prazo_destinatario_estourado_tambem_renderiza_no_tema_v2(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Encaminhado,
            'encaminhado_em' => now()->subDays(35),
            'descricao' => 'Prazo destinatario compartilhado QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="prazo-destinatario-estourado"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-prazo-destinatario"', false);
        $response->assertSeeText('Prazo destinatario compartilhado QA');
    }

    public function test_tabela_compartilhada_recebidos_sem_encaminhar_30_dias_tambem_renderiza_no_tema_v2(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Recebido,
            'recebido_em' => now()->subDays(35),
            'descricao' => 'Recebido mais de 30 dias compartilhado QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="recebidos-sem-encaminhar-30-dias"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-sem-nota"', false);
        $response->assertSeeText('Recebido mais de 30 dias compartilhado QA');
    }

    public function test_detalhe_de_rma_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = Rma::factory()->create(['descricao' => 'Detalhe tema V2']);

        $response = $this->actingAs($usuario)->get("/v2/rma/{$rma->id}");

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.show');
        $response->assertSeeText('Detalhe tema V2');
    }

    public function test_clientes_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Cliente::factory()->create(['nome' => 'Cliente tema V2']);

        $response = $this->actingAs($usuario)->get('/v2/parceiros/clientes');

        $response->assertOk();
        $response->assertViewIs('temas.v2.parceiros.index');
        $response->assertSeeText('Cliente tema V2');
    }

    public function test_perfil_v2_renderiza(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador, 'anotacao' => 'Nota V2']);

        $response = $this->actingAs($usuario)->get('/v2/perfil');

        $response->assertOk();
        $response->assertViewIs('temas.v2.identidade.perfil');
        $response->assertSeeText('Nota V2');
    }

    public function test_alerta_nao_vai_dar_garantia_renderiza_a_tabela_historica(): void
    {
        // CP12-05G V2 - mesmo _centro_de_avisos.blade.php compartilhado
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'nfvenda_emissao' => now()->subDays(400)->toDateString(),
            'descricao' => 'Produto nao vai dar garantia V2 QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="nao-vai-dar-garantia"', false);
    }

    public function test_alerta_nf_retorno_pendente_de_lancar_renderiza_a_tabela_historica(): void
    {
        // CP12-05H V2
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Concluido,
            'lancadoretorno' => \App\Rma\Dominio\StatusDeLancamento::Pendente,
            'concluido_em' => now()->subDays(5),
            'descricao' => 'Produto nf retorno pendente V2 QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="nf-retorno-pendente-de-lancar"', false);
    }

    public function test_alerta_garantia_fornecedor_expirada_renderiza_a_tabela_historica(): void
    {
        // CP12-05I V2
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'nfcompra_emissao' => now()->subDays(400)->toDateString(),
            'descricao' => 'Produto garantia expirada V2 QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="garantia-fornecedor-expirada"', false);
    }

    public function test_alerta_garantia_fornecedor_expirando_em_30_dias_renderiza_a_tabela_historica(): void
    {
        // CP12-05J V2
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'nfcompra_emissao' => now()->subDays(350)->toDateString(),
            'descricao' => 'Produto garantia expirando 30 dias V2 QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v2/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="garantia-fornecedor-expirando-30-dias"', false);
    }
}
