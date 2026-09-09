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
 * T3-12 - formularios Novo/Editar RMA do Tema V3, ocultos sob `/v3`, usando os
 * mesmos controllers/casos de uso de V1/V2 e preservando Policy/tenant.
 */
class FormulariosRmaV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(Papel $papel): User
    {
        return User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    #[Test]
    public function novo_rma_v3_renderiza_formulario_em_secoes(): void
    {
        $response = $this->actingAs($this->usuario(Papel::Operador))
            ->get('/v3/rmas/novo')
            ->assertOk();

        $response->assertViewIs('temas.v3.rma.create');
        foreach ([
            'Identificacao',
            'Origem e parceiros',
            'Fiscal',
            'Operacao',
            'Observacoes',
        ] as $secao) {
            $response->assertSeeText($secao);
        }
        $response->assertSee('name="descricao"', false);
        $response->assertSee('name="modelo"', false);
        $response->assertSee('name="origem"', false);
        $response->assertSee('name="fabricante_id"', false);
        $response->assertSee('name="fornecedor_id"', false);
        $response->assertSee('name="nfvenda"', false);
        $response->assertSee('name="nfcompra"', false);
        $response->assertSee('name="prioridade"', false);
        $response->assertSee('name="defeito"', false);
        $response->assertSee('name="observacao"', false);
        $response->assertSee('form-v3__acoes', false);
        $response->assertSee('Salvar', false);
    }

    #[Test]
    public function criacao_pelo_formulario_v3_persiste_e_redireciona_para_o_detalhe(): void
    {
        $usuario = $this->usuario(Papel::Operador);

        $response = $this->actingAs($usuario)
            ->post('/v3/rmas', [
                'descricao' => 'Novo RMA V3 formulario',
                'modelo' => 'MODELO V3 FORM',
                'sn' => 'SN-V3-FORM',
                'origem' => 'Loja',
                'empresa' => 'Cellsystem',
                'prioridade' => 'alta',
                'defeito' => 'Defeito do formulario V3',
                'observacao' => 'Observacao do formulario V3.',
            ])
            ->assertRedirect();

        $rma = Rma::query()->latest('id')->first();
        $this->assertNotNull($rma);
        $this->assertStringEndsWith("/v3/rma/{$rma->id}", $response->headers->get('Location'));
        $this->assertSame('Novo RMA V3 formulario', $rma->descricao);
        $this->assertSame('MODELO V3 FORM', $rma->modelo);
        $this->assertSame('SN-V3-FORM', $rma->sn);
        $this->assertSame(Prioridade::Alta, $rma->prioridade);
    }

    #[Test]
    public function edicao_pelo_formulario_v3_persiste_apos_put(): void
    {
        $usuario = $this->usuario(Papel::Operador);
        $rma = Rma::factory()->create([
            'descricao' => 'RMA edicao V3',
            'defeito' => 'Defeito original',
        ]);

        $this->actingAs($usuario)
            ->get("/v3/rma/{$rma->id}/editar")
            ->assertOk()
            ->assertViewIs('temas.v3.rma.edit');

        $this->actingAs($usuario)
            ->put("/v3/rma/{$rma->id}", [
                'descricao' => 'RMA edicao V3 atualizado',
                'modelo' => 'MODELO EDITADO V3',
                'origem' => 'Cliente',
                'prioridade' => 'media',
                'marcarestoque' => '0',
                'credito_disponivel' => '1',
                'defeito' => 'Defeito atualizado V3',
                'observacao' => 'Observacao editada no formulario V3.',
                'acao' => 'salvar',
            ])
            ->assertRedirect("/v3/rma/{$rma->id}");

        $this->assertSame('RMA edicao V3 atualizado', $rma->fresh()->descricao);
        $this->assertSame('MODELO EDITADO V3', $rma->fresh()->modelo);
        $this->assertSame(Prioridade::Media, $rma->fresh()->prioridade);
        $this->assertFalse($rma->fresh()->marcarestoque);
        $this->assertTrue($rma->fresh()->credito_disponivel);
    }

    #[Test]
    public function leitura_nao_consegue_criar_nem_editar_pela_policy(): void
    {
        $usuario = $this->usuario(Papel::Leitura);

        $this->actingAs($usuario)
            ->get('/v3/rmas/novo')
            ->assertForbidden();

        $rma = Rma::factory()->create();
        $this->actingAs($usuario)
            ->put("/v3/rma/{$rma->id}", [
                'descricao' => 'Tentativa leitura V3',
                'defeito' => 'Tentativa',
                'acao' => 'salvar',
            ])
            ->assertForbidden();
    }
}
