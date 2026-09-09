<?php

namespace Tests\Feature;

use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaizRedirecionamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_na_raiz_vai_para_o_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_autenticado_na_raiz_vai_para_o_dashboard(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);

        $this->actingAs($usuario)->get('/')->assertRedirect(route('dashboard'));
    }
}
