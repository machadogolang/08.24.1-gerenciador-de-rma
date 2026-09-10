<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR15-REL-001..007 - hub estatistico de Relatorios do TEMA V2 (fonte
 * `15.8.1/page/relatorios.php`) e correcao do menu historico (item unico
 * "Relatorios", PAR15-REL-008/009).
 */
class PainelRelatoriosV2Test extends TestCase
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
    public function painel_v2_agrega_situacao_resolucao_origem_e_dados_do_sistema(): void
    {
        Rma::factory()->create(['status' => Status::Entrada, 'origem' => 'Cliente', 'solucao' => null]);
        Rma::factory()->create(['status' => Status::Concluido, 'origem' => 'Loja', 'solucao' => Solucao::Reparo]);
        Rma::factory()->create(['status' => Status::Concluido, 'origem' => 'Loja', 'solucao' => Solucao::SemGarantia]);

        $response = $this->actingAs($this->adminV2())->get('/v2/relatorios');

        $response->assertOk();
        $response->assertViewIs('temas.v2.rma.relatorios.index');
        $response->assertSeeText('Situacao');
        $response->assertSeeText('Resolucao');
        $response->assertSeeText('Origem');
        $response->assertSeeText('Dados do Sistema');
        $response->assertSeeText('Quantidade de RMA: 3');
        $response->assertSeeText('Reparo');
        $response->assertSeeText('Sem garantia');
        $response->assertSeeText('Cliente');
    }

    #[Test]
    public function menu_do_tema_v2_tem_um_unico_item_relatorios(): void
    {
        $html = $this->actingAs($this->adminV2())->get('/v2/relatorios')->assertOk()->getContent();

        $this->assertStringContainsString('>Relatorios</a>', $html);
        $this->assertStringNotContainsString('Relatorio RCD', $html);
        $this->assertStringNotContainsString('Relatorio RPEC', $html);
        $this->assertStringNotContainsString('Relatorio RMPE', $html);
    }

    #[Test]
    public function tema_v1_mantem_a_organizacao_propria_de_relatorios(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $html = $this->actingAs($admin)->get('/v1/rma')->assertOk()->getContent();

        $this->assertStringContainsString('Relatório RPEC', $html);
        $this->assertStringContainsString('Relatório RMPE', $html);
    }
}
