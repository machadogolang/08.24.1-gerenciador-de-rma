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
use App\Rma\Dominio\Prioridade;
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
        $response->assertSee('class="Tabelinha-Table tabela-alerta-abertos-nao-encaminhados"', false);
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

    public function test_alerta_de_prioridade_alta_renderiza_tabela_historica_com_entrada(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante Prioridade QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor Prioridade QA']);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'prioridade' => Prioridade::Alta,
            'created_at' => now()->subDays(7),
            'origem' => 'Mercado Livre',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'descricao' => 'Produto prioritario ficticio',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="prioridade-alta-sem-encaminhar"', false);
        $response->assertSeeText('ENTRADA');
        $response->assertSeeText('M LIVRE');
        $response->assertSeeText('Fornecedor Prioridade QA');
        $response->assertSeeText('Fabricante Prioridade QA');
        $response->assertDontSeeText('Mercado Livre');
    }

    public function test_alerta_sem_numero_de_serie_renderiza_a_tabela_historica_com_recebido(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante sem SN QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor sem SN QA']);
        Rma::factory()->create([
            'status' => Status::Recebido,
            'sn' => null,
            'recebido_em' => now()->subDays(3),
            'origem' => 'Mercado Livre',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'descricao' => 'Produto sem numero de serie QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="sem-numero-de-serie"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-abertos-nao-encaminhados"', false);
        $response->assertSeeInOrder([
            'RECEBIDO', 'T', 'ORIGEM', 'NF C', 'NF V', 'FORNECEDOR',
            'FABRICANTE', 'DESCRICAO', 'MODELO', 'OS', 'A',
        ]);
        $response->assertSeeText('Mercado Livre');
        $response->assertSeeText('Fornecedor sem SN QA');
        $response->assertSeeText('Fabricante sem SN QA');
        $response->assertSeeText('Produto sem numero de serie QA');
    }

    public function test_alerta_sem_nota_fiscal_renderiza_a_tabela_historica(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante sem NF QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor sem NF QA']);
        Rma::factory()->create([
            'status' => Status::Recebido,
            'nfcompra' => null,
            'nfvenda' => null,
            'sn' => 'SN-SEM-NF-123',
            'recebido_em' => now()->subDays(2),
            'origem' => 'Mercado Livre',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'descricao' => 'Produto sem nota fiscal QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="sem-nota-fiscal"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-sem-nota"', false);
        $response->assertSeeInOrder([
            'RECEBIDO', 'T', 'ORIGEM', 'FORNECEDOR',
            'FABRICANTE', 'DESCRICAO', 'MODELO', 'S/N', 'OS', 'A',
        ]);
        $response->assertSeeText('M LIVRE');
        $response->assertSeeText('Fornecedor sem NF QA');
        $response->assertSeeText('Fabricante sem NF QA');
        $response->assertSeeText('Produto sem nota fiscal QA');
        $response->assertSeeText('SN-SEM-NF-123');
    }

    public function test_alerta_prazo_destinatario_estourado_renderiza_a_tabela_historica(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante Destinatario QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Destinatario Fornecedor QA']);
        Rma::factory()->create([
            'status' => Status::Encaminhado,
            'encaminhado_em' => now()->subDays(35),
            'origem' => 'Mercado Livre',
            'fabricante_id' => $fabricante->id,
            'destinatario_type' => Fornecedor::class,
            'destinatario_id' => $fornecedor->id,
            'protocolo' => 'PROT-DEST-123',
            'descricao' => 'Produto prazo destinatario QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="prazo-destinatario-estourado"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-prazo-destinatario"', false);
        $response->assertSeeInOrder([
            'ENCAMINHADO', 'T', 'ORIGEM', 'FABRICANTE',
            'DESCRICAO', 'MODELO', 'PROTOCOLO', 'DESTINATARIO', 'OS', 'A',
        ]);
        $response->assertSeeText('M LIVRE');
        $response->assertSeeText('Fabricante Destinatario QA');
        $response->assertSeeText('Destinatario Fornecedor QA');
        $response->assertSeeText('PROT-DEST-123');
        $response->assertSeeText('Produto prazo destinatario QA');
    }

    public function test_alerta_recebidos_sem_encaminhar_30_dias_renderiza_a_tabela_historica(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante 30 Dias QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor 30 Dias QA']);
        Rma::factory()->create([
            'status' => Status::Recebido,
            'recebido_em' => now()->subDays(35),
            'origem' => 'Mercado Livre',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
            'sn' => 'SN-30-DIAS-123',
            'descricao' => 'Produto 30 dias sem encaminhar QA',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="recebidos-sem-encaminhar-30-dias"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-sem-nota"', false);
        $response->assertSeeInOrder([
            'RECEBIDO', 'T', 'ORIGEM', 'FORNECEDOR',
            'FABRICANTE', 'DESCRICAO', 'MODELO', 'S/N', 'OS', 'A',
        ]);
        $response->assertSeeText('Mercado Livre');
        $response->assertSeeText('Fabricante 30 Dias QA');
        $response->assertSeeText('Fornecedor 30 Dias QA');
        $response->assertSeeText('SN-30-DIAS-123');
        $response->assertSeeText('Produto 30 dias sem encaminhar QA');
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

    public function test_alerta_nao_vai_dar_garantia_renderiza_a_tabela_historica(): void
    {
        // CP12-05G — listar_naovaidargarantia.php: 11 colunas ENTRADA|ORIGEM|NF C|T C|NF V|...
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante NaoGarantia QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor NaoGarantia QA']);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'nfvenda_emissao' => now()->subDays(400)->toDateString(),
            'nfcompra' => 'NFC-NAOGAR-001',
            'nfvenda' => 'NFV-NAOGAR-001',
            'descricao' => 'Produto nao vai dar garantia QA',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="nao-vai-dar-garantia"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-nao-vai-dar-garantia"', false);
        $response->assertSeeInOrder([
            'ENTRADA', 'ORIGEM', 'NF C', 'T C', 'NF V',
            'FORNECEDOR', 'FABRICANTE', 'DESCRICAO', 'MODELO', 'OS', 'A',
        ]);
        $response->assertSeeText('Fabricante NaoGarantia QA');
        $response->assertSeeText('Fornecedor NaoGarantia QA');
        $response->assertSeeText('NFC-NAOGAR-001');
        $response->assertSeeText('Produto nao vai dar garantia QA');
    }

    public function test_alerta_nf_retorno_pendente_de_lancar_renderiza_a_tabela_historica(): void
    {
        // CP12-05H — listar_nfpendentelancar.php: 11 colunas CONCLUIDO|T|ORIGEM|NF C|NF V|...
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante NfRetorno QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor NfRetorno QA']);
        Rma::factory()->create([
            'status' => Status::Concluido,
            'lancadoretorno' => \App\Rma\Dominio\StatusDeLancamento::Pendente,
            'concluido_em' => now()->subDays(5),
            'nfcompra' => 'NFC-NFRET-001',
            'nfvenda' => 'NFV-NFRET-001',
            'descricao' => 'Produto nf retorno pendente QA',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="nf-retorno-pendente-de-lancar"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-nf-retorno-pendente"', false);
        $response->assertSeeInOrder([
            'CONCLUIDO', 'T', 'ORIGEM', 'NF C', 'NF V',
            'FORNECEDOR', 'FABRICANTE', 'DESCRICAO', 'MODELO', 'OS', 'A',
        ]);
        $response->assertSeeText('Fabricante NfRetorno QA');
        $response->assertSeeText('Fornecedor NfRetorno QA');
        $response->assertSeeText('NFC-NFRET-001');
        $response->assertSeeText('Produto nf retorno pendente QA');
    }

    public function test_alerta_garantia_fornecedor_expirada_renderiza_a_tabela_historica(): void
    {
        // CP12-05I — listar_pgarantiafornecedorexpirado.php: 11 colunas ENTRADA|ORIGEM|NF C|T C|NF V|...
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante GarExpir QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor GarExpir QA']);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'nfcompra_emissao' => now()->subDays(400)->toDateString(),
            'nfcompra' => 'NFC-GAREXP-001',
            'nfvenda' => 'NFV-GAREXP-001',
            'descricao' => 'Produto garantia expirada QA',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="garantia-fornecedor-expirada"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-garantia-fornecedor-expirada"', false);
        $response->assertSeeInOrder([
            'ENTRADA', 'ORIGEM', 'NF C', 'T C', 'NF V',
            'FORNECEDOR', 'FABRICANTE', 'DESCRICAO', 'MODELO', 'OS', 'A',
        ]);
        $response->assertSeeText('Fabricante GarExpir QA');
        $response->assertSeeText('Fornecedor GarExpir QA');
        $response->assertSeeText('NFC-GAREXP-001');
        $response->assertSeeText('Produto garantia expirada QA');
    }

    public function test_alerta_garantia_fornecedor_expirando_em_30_dias_renderiza_a_tabela_historica(): void
    {
        // CP12-05J — listar_pmenosde30.php: 11 colunas ENTRADA|ORIGEM|NF C|T E|NF V|...
        // T E = dias restantes (janela: 336 < dias_decorridos < 365 → emissão entre -364d e -337d)
        $usuario = User::factory()->create(['papel' => Papel::Operador]);
        $fabricante = Fabricante::factory()->create(['nome' => 'Fabricante GarExpir30 QA']);
        $fornecedor = Fornecedor::factory()->create(['nome' => 'Fornecedor GarExpir30 QA']);
        Rma::factory()->create([
            'status' => Status::Entrada,
            'nfcompra_emissao' => now()->subDays(350)->toDateString(),
            'nfcompra' => 'NFC-GAREX30-001',
            'nfvenda' => 'NFV-GAREX30-001',
            'descricao' => 'Produto garantia expirando 30 dias QA',
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/rma');

        $response->assertOk();
        $response->assertSee('data-alerta-tipo="garantia-fornecedor-expirando-30-dias"', false);
        $response->assertSee('class="Tabelinha-Table tabela-alerta-garantia-fornecedor-expirando"', false);
        $response->assertSeeInOrder([
            'ENTRADA', 'ORIGEM', 'NF C', 'T E', 'NF V',
            'FORNECEDOR', 'FABRICANTE', 'DESCRICAO', 'MODELO', 'OS', 'A',
        ]);
        $response->assertSeeText('Fabricante GarExpir30 QA');
        $response->assertSeeText('Fornecedor GarExpir30 QA');
        $response->assertSeeText('NFC-GAREX30-001');
        $response->assertSeeText('Produto garantia expirando 30 dias QA');
    }
}
