<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\AssistenciaTecnica;
use App\Models\Company;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UI-V1-CONTROLE-01 - Testes de backend para acoes do painel Controle V1.
 * Valida criacao de representante seguro, arquivamento sem inline JS, validacao e autorizacao.
 */
class ControleV1AcoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_adicionar_representante_fornecedor_com_sucesso(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($admin)->post('/rmas-controle/representante', [
            'nome' => 'Distribuidora Alpha',
            'tipo' => 'fornecedor',
        ]);

        $response->assertRedirect(route('rmas.controle.index'));
        $response->assertSessionHas('status', 'Fornecedor cadastrado com sucesso.');
        $response->assertSessionHas('painel_aberto', 'representante');

        $this->assertDatabaseHas('fornecedores', [
            'nome' => 'Distribuidora Alpha',
        ]);
    }

    public function test_adicionar_representante_fabricante_com_sucesso(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($admin)->post('/rmas-controle/representante', [
            'nome' => 'Logitech Brasil',
            'tipo' => 'fabricante',
        ]);

        $response->assertRedirect(route('rmas.controle.index'));
        $response->assertSessionHas('status', 'Fabricante cadastrado com sucesso.');
        $response->assertSessionHas('painel_aberto', 'representante');

        $this->assertDatabaseHas('fabricantes', [
            'nome' => 'Logitech Brasil',
        ]);
    }

    public function test_adicionar_representante_assistencia_com_sucesso(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($admin)->post('/rmas-controle/representante', [
            'nome' => 'Tech Repair POA',
            'tipo' => 'assistencia_tecnica',
        ]);

        $response->assertRedirect(route('rmas.controle.index'));
        $response->assertSessionHas('status', 'Assistência técnica cadastrada com sucesso.');
        $response->assertSessionHas('painel_aberto', 'representante');

        $this->assertDatabaseHas('assistencias_tecnicas', [
            'nome' => 'Tech Repair POA',
        ]);
    }

    public function test_adicionar_representante_valida_tipo_e_nome(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($admin)->post('/rmas-controle/representante', [
            'nome' => '',
            'tipo' => 'tipo_invalido',
        ]);

        $response->assertSessionHasErrors(['nome', 'tipo']);
    }

    public function test_adicionar_representante_bloqueia_usuario_sem_permissao(): void
    {
        $leitor = User::factory()->create([
            'papel' => Papel::Leitura,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($leitor)->post('/rmas-controle/representante', [
            'nome' => 'Invasor',
            'tipo' => 'fornecedor',
        ]);

        $response->assertForbidden();
    }

    public function test_arquivar_rma_com_sucesso(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $rma = Rma::factory()->create([
            'tenant_id' => $admin->tenant_id,
            'status' => Status::Entrada,
        ]);

        $response = $this->actingAs($admin)->post('/rmas-controle/arquivar', [
            'numero' => $rma->id,
        ]);

        $response->assertRedirect(route('rmas.controle.index'));
        $response->assertSessionHas('status', "RMA {$rma->id} arquivado com sucesso.");
        $response->assertSessionHas('painel_aberto', 'arquivar');

        $this->assertEquals(Status::Arquivado, $rma->fresh()->status);
    }

    public function test_arquivar_rma_inexistente_retorna_erro_amigavel_sem_stack(): void
    {
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($admin)->post('/rmas-controle/arquivar', [
            'numero' => 99999,
        ]);

        $response->assertRedirect(route('rmas.controle.index'));
        $response->assertSessionHasErrors(['numero' => 'RMA 99999 não encontrado.']);
        $response->assertSessionHas('painel_aberto', 'arquivar');
    }

    public function test_arquivar_rma_de_outro_tenant_retorna_nao_encontrado(): void
    {
        $outroTenant = Company::factory()->create();
        $admin = User::factory()->create([
            'papel' => Papel::SuperAdministrador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $rmaOutroTenant = Rma::factory()->make([
            'status' => Status::Entrada,
        ]);
        $rmaOutroTenant->forceFill(['tenant_id' => $outroTenant->id])->save();

        $response = $this->actingAs($admin)->post('/rmas-controle/arquivar', [
            'numero' => $rmaOutroTenant->id,
        ]);

        $response->assertRedirect(route('rmas.controle.index'));
        $response->assertSessionHasErrors(['numero' => "RMA {$rmaOutroTenant->id} não encontrado."]);
        $this->assertNotEquals(Status::Arquivado, $rmaOutroTenant->fresh()->status);
    }
}
