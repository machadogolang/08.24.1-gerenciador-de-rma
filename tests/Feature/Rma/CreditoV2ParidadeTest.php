<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-CREDIT-001/002/003/004 - a tela de Creditos do TEMA V2 reproduz o contrato
 * real do Legacy (`15.8.1/page/credito.php`): tabela com 11 colunas, cabecalho sempre
 * visivel, "Nenhum produto" quando vazio, filtro real `creditodisponivel = 1` e o
 * menu morto (Disponiveis/Pendentes/Usados) ausente.
 */
class CreditoV2ParidadeTest extends TestCase
{
    use RefreshDatabase;

    private function adminV2(): User
    {
        return User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    #[Test]
    public function pagina_v2_usa_as_colunas_e_o_estado_vazio_do_legacy(): void
    {
        $response = $this->actingAs($this->adminV2())->get('/v2/creditos');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.credito.index');
        foreach (['DATA', 'NF C', 'FABRICANTE', 'DESCRICAO', 'MODELO', 'NF R', 'PROTOCOLO', 'DESTINATARIO', 'OS', 'VALOR', 'A'] as $coluna) {
            $response->assertSeeText($coluna);
        }
        $response->assertSeeText('Creditos');
        $response->assertSeeText('Nenhum produto');

        // Menu morto do Legacy (subp inexistentes) nao e reproduzido.
        $response->assertDontSeeText('Pendentes');
        $response->assertDontSeeText('Usados');
    }

    #[Test]
    public function pagina_v2_lista_somente_rmas_com_credito_disponivel(): void
    {
        Rma::factory()->create(['descricao' => 'Com credito', 'credito_disponivel' => true, 'solucao' => Solucao::GeradoCredito]);
        Rma::factory()->create(['descricao' => 'Sem credito', 'credito_disponivel' => false]);

        $response = $this->actingAs($this->adminV2())->get('/v2/creditos');

        $response->assertOk();
        // A sidebar tambem lista descricoes; a prova e na CELULA da tabela de creditos.
        $response->assertSee('<td class="Tabelinha-TD"><div>Com credito</div></td>', false);
        $response->assertDontSee('<td class="Tabelinha-TD"><div>Sem credito</div></td>', false);
    }

    #[Test]
    public function credito_v1_mantem_o_painel_de_relatorios_do_14_6_1(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($admin)->get('/rmas-credito');

        $response->assertOk();
        $response->assertViewIs('temas.v1.rma.credito.index');
        $response->assertSeeText('RCD');
        $response->assertSeeText('RELATORIO DE CREDITOS DISPONIVEIS');
        $response->assertSee(route('rmas.relatorios.rcd'), false);
    }
}
