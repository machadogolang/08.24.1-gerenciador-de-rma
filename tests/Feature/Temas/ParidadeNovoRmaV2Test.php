<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR-V2-NOVO-01 - aba Novo do Tema V2 com formulario operacional inline
 * (15.8.1/page/novo_rma.php) e rota /create usando o mesmo partial.
 */
class ParidadeNovoRmaV2Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);
    }

    #[Test]
    public function aba_novo_v2_exibe_formulario_inline_com_composicao_do_legacy(): void
    {
        $response = $this->actingAs($this->usuario())
            ->get('/v2/rma')
            ->assertOk();

        $response->assertViewIs('temas.v2.rma.index');
        $response->assertSee('id="novo_rma"', false);
        $response->assertSee('CRIAR BD', false);
        $response->assertSee('name="descricao"', false);
        $response->assertSee('name="fabricante_id"', false);
        $response->assertSee('name="origem"', false);
        $response->assertSee('name="prioridade"', false);
        $response->assertSee('name="nfvenda"', false);
        $response->assertSee('name="nfcompra"', false);
        $response->assertSee('id="cli"', false);
        $response->assertSee('id="outraorigem"', false);
        $response->assertSee('name="defeito"', false);
        $response->assertSee('name="observacao"', false);
        $response->assertDontSee('Abrir novo RMA', false);
    }

    #[Test]
    public function criacao_inline_pelo_painel_persiste_campos_e_redireciona_para_o_detalhe(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post('/v2/rma', [
                'descricao' => 'Novo RMA inline V2',
                'modelo' => 'MODELO INLINE V2',
                'sn' => 'SN-INLINE-V2',
                'os' => 'OS-INLINE',
                'origem' => 'Cliente',
                'empresa' => 'Cellsystem',
                'prioridade' => 'alta',
                'nfvenda' => 'NFV-INLINE',
                'nfvenda_emissao' => '05/09/2026',
                'nfvenda_chave' => 'CHAVE-NFV-INLINE',
                'cliente_nome' => 'Cliente Inline V2',
                'marcarestoque' => '1',
                'defeito' => 'Defeito do novo RMA inline V2',
                'observacao' => 'Observacao do novo RMA inline V2.',
            ])
            ->assertRedirect();

        $rma = Rma::query()->latest('id')->first();
        $this->assertNotNull($rma);
        $this->assertStringEndsWith("/v2/rma/{$rma->id}", $response->headers->get('Location'));
        $this->assertSame('Novo RMA inline V2', $rma->descricao);
        $this->assertSame('MODELO INLINE V2', $rma->modelo);
        $this->assertSame('SN-INLINE-V2', $rma->sn);
        $this->assertSame(Prioridade::Alta, $rma->prioridade);
        $this->assertSame('NFV-INLINE', $rma->nfvenda);
        $this->assertSame('CHAVE-NFV-INLINE', $rma->nfvenda_chave);
        $this->assertTrue($rma->marcarestoque);
    }
}
