<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ADENDO P0/bloco C (AD-20..AD-23) - "Previa V3" segura.
 *
 * Regras provadas aqui:
 * 1. flag `temas.v3_preview_enabled` OFF nao mostra NENHUMA entrada para o V3;
 * 2. flag ON mostra a entrada discreta "Previa V3" nos shells V1 e V2;
 * 3. abrir o V3 pela previa NAO altera `tema_preferido` (nunca persiste V3);
 * 4. "Voltar ao sistema" no shell V3 sai do prefixo `/v3` e cai na rota canonica,
 *    que resolve de novo o tema persistido (V1 ou V2).
 */
class PreviaTemaV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(TemaPreferido $tema): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
    }

    public function test_flag_desligada_nao_expoe_a_previa_v3(): void
    {
        config(['temas.v3_preview_enabled' => false]);
        $usuario = $this->usuario(TemaPreferido::V1);

        $this->actingAs($usuario)
            ->get(route('rmas.entrada'))
            ->assertOk()
            ->assertDontSee('Previa V3');

        $this->actingAs($usuario)
            ->get(route('v2.rmas.index'))
            ->assertOk()
            ->assertDontSee('Previa V3');
    }

    public function test_flag_ligada_expoe_a_previa_v3_nos_dois_temas(): void
    {
        config(['temas.v3_preview_enabled' => true]);
        $usuario = $this->usuario(TemaPreferido::V1);

        $v1 = $this->actingAs($usuario)->get(route('rmas.entrada'));
        $v1->assertOk();
        $v1->assertSee('Previa V3');
        $v1->assertSee(route('v3.dashboard'), false);

        $v2 = $this->actingAs($usuario)->get(route('v2.rmas.index'));
        $v2->assertOk();
        $v2->assertSee('Previa V3');
        $v2->assertSee(route('v3.dashboard'), false);
    }

    public function test_previa_v3_nao_persiste_tema_e_volta_ao_tema_do_usuario(): void
    {
        config(['temas.v3_preview_enabled' => true]);

        foreach ([TemaPreferido::V1, TemaPreferido::V2] as $tema) {
            $usuario = $this->usuario($tema);

            $this->actingAs($usuario)->get(route('v3.dashboard'))->assertOk();
            $this->assertSame($tema, $usuario->fresh()->tema_preferido);

            // "Voltar ao sistema" (rota canonica) volta a resolver pela preferencia
            // persistida - V1 e V2 tem views proprias para `rma.index`.
            $this->actingAs($usuario)
                ->get(route('rmas.index'))
                ->assertOk()
                ->assertViewIs("temas.{$tema->value}.rma.index");
        }
    }

    public function test_shell_v3_oferece_a_acao_voltar_ao_sistema(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $resposta = $this->actingAs($usuario)->get(route('v3.dashboard'));

        $resposta->assertOk();
        $resposta->assertSee('Voltar ao sistema');
        $resposta->assertSee('href="'.route('rmas.index').'"', false);
    }

    public function test_alternancia_publica_continua_binaria_entre_v1_e_v2(): void
    {
        $usuario = $this->usuario(TemaPreferido::V1);

        $this->actingAs($usuario)->post(route('tema.alternar'));
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);

        $this->actingAs($usuario->fresh())->post(route('tema.alternar'));
        $this->assertSame(TemaPreferido::V1, $usuario->fresh()->tema_preferido);
    }
}
