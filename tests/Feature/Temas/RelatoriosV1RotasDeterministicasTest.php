<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ADENDO P0/AD-10/AD-11 - URLs de QA deterministas para os relatorios fiscais do
 * TEMA V1 (`/v1/relatorios/{rcd,rpec,rmpe}`).
 *
 * Os tres caminhos usam o MESMO `RelatorioController` das rotas canonicas
 * (`/rmas-relatorios/*`); o prefixo `/v1` apenas forca `ResolverTemaAtivo` a
 * resolver a folha historica do V1. O usuario de prova tem `tema_preferido = V2`
 * para deixar explicito que o prefixo - e nao a preferencia - decide o tema.
 */
class RelatoriosV1RotasDeterministicasTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComPreferenciaV2(): User
    {
        return User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    public function test_v1_rcd_responde_200_na_folha_do_v1(): void
    {
        $usuario = $this->usuarioComPreferenciaV2();
        Rma::factory()->create([
            'descricao' => 'RMA de credito na URL deterministica',
            'status' => Status::Concluido,
            'credito_disponivel' => true,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/relatorios/rcd');

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.relatorios.rcd');
        $response->assertSee('RCD - RELATORIO DE CREDITOS DISPONIVEIS');
        $response->assertSee('RMA de credito na URL deterministica');
    }

    public function test_v1_rpec_responde_200_na_folha_do_v1(): void
    {
        $usuario = $this->usuarioComPreferenciaV2();
        Rma::factory()->create([
            'descricao' => 'RMA de estoque na URL deterministica',
            'status' => Status::Recebido,
            'marcarestoque' => true,
        ]);

        $response = $this->actingAs($usuario)->get('/v1/relatorios/rpec');

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.relatorios.rpec');
        $response->assertSee('RPEC - RELACAO DOS PRODUTOS EM ESTOQUE PARA CONTAGEM');
        $response->assertSee('RMA de estoque na URL deterministica');
    }

    public function test_v1_rmpe_responde_200_sem_query_string(): void
    {
        $usuario = $this->usuarioComPreferenciaV2();
        Rma::factory()->create([
            'descricao' => 'RMA encaminhado na URL deterministica',
            'status' => Status::Encaminhado,
            'marcarestoque' => true,
            'nf_remessa' => '321',
            'encaminhado_em' => '2026-02-03 09:00:00',
        ]);

        $response = $this->actingAs($usuario)->get('/v1/relatorios/rmpe');

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.relatorios.rmpe');
        $response->assertSee('RMPE - RELACAO DOS PRODUTOS ENCAMINHADOS PELO RMA');
        $response->assertSee('RMA encaminhado na URL deterministica');
    }

    public function test_rotas_v1_exigem_autenticacao(): void
    {
        foreach (['rcd', 'rpec', 'rmpe'] as $codigo) {
            $this->get("/v1/relatorios/{$codigo}")->assertRedirect(route('login'));
        }
    }
}
