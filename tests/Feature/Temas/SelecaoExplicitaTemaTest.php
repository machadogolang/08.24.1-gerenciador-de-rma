<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelecaoExplicitaTemaTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(TemaPreferido $tema = TemaPreferido::V1): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
    }

    public function test_alternar_tema_sem_parametro_mantem_comportamento_binario_v1_v2(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $response = $this->actingAs($usuario)->post('/tema/alternar');

        $response->assertSessionHas('tema_preferido', 'v2');
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);

        $response2 = $this->actingAs($usuario->fresh())->post('/tema/alternar');
        $response2->assertSessionHas('tema_preferido', 'v1');
        $this->assertSame(TemaPreferido::V1, $usuario->fresh()->tema_preferido);
    }

    public function test_selecao_explicita_define_v1_no_usuario_e_sessao(): void
    {
        $usuario = $this->usuario(TemaPreferido::V2);

        $response = $this->actingAs($usuario)->post('/tema/alternar', [
            'tema' => 'v1',
        ]);

        $response->assertSessionHas('tema_preferido', 'v1');
        $this->assertSame(TemaPreferido::V1, $usuario->fresh()->tema_preferido);
    }

    public function test_selecao_explicita_define_v2_no_usuario_e_sessao(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $response = $this->actingAs($usuario)->post('/tema/alternar', [
            'tema' => 'v2',
        ]);

        $response->assertSessionHas('tema_preferido', 'v2');
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);
    }

    public function test_selecao_explicita_v3_grava_sessao_e_redireciona_ao_dashboard_v3(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $response = $this->actingAs($usuario)->post('/tema/alternar', [
            'tema' => 'v3',
        ]);

        $response->assertSessionHas('tema_preferido', 'v3');
        $response->assertRedirect(route('v3.dashboard'));
    }

    public function test_selecao_explicita_com_valor_invalido_falha_validacao(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $response = $this->actingAs($usuario)->post('/tema/alternar', [
            'tema' => 'invalido',
        ]);

        $response->assertSessionHasErrors(['tema']);
    }

    public function test_redirecionamento_do_v3_para_v1_e_v2(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $responseV1 = $this->actingAs($usuario)
            ->from('/v3/perfil')
            ->post('/tema/alternar', ['tema' => 'v1']);

        $responseV1->assertRedirect(route('rmas.entrada'));

        $responseV2 = $this->actingAs($usuario)
            ->from('/v3/perfil')
            ->post('/tema/alternar', ['tema' => 'v2']);

        $responseV2->assertRedirect(route('rmas.index'));
    }

    public function test_perfil_v3_exibe_seletor_explicito_de_temas(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $response = $this->actingAs($usuario)->get('/v3/perfil');

        $response->assertOk();
        $response->assertSeeText('Seleção Explícita de Tema Visual');
        $response->assertSeeText('Tema V1 (14.6.1)');
        $response->assertSeeText('Tema V2 (15.8.1)');
        $response->assertSeeText('Tema V3 (Console)');
        $response->assertSee('Ativar Tema V1');
        $response->assertSee('Ativar Tema V2');
    }
}
