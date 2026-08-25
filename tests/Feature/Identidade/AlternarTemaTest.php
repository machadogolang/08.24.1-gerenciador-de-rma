<?php

namespace Tests\Feature\Identidade;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlternarTemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_alterna_e_persiste_o_tema(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $response = $this->actingAs($usuario)->post('/tema/alternar');

        $response->assertRedirect();
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);
    }

    public function test_alternar_duas_vezes_volta_ao_original(): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $this->actingAs($usuario)->post('/tema/alternar');
        $this->actingAs($usuario)->post('/tema/alternar');

        $this->assertSame(TemaPreferido::V1, $usuario->fresh()->tema_preferido);
    }

    public function test_login_subsequente_usa_o_tema_persistido(): void
    {
        $usuario = User::factory()->create([
            'email' => 'tema@rma.local',
            'password' => bcrypt('senha-correta'),
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);

        $this->actingAs($usuario)->post('/tema/alternar');
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'tema@rma.local',
            'password' => 'senha-correta',
        ]);

        $this->assertSame('v2', session('tema_preferido'));
    }
}
