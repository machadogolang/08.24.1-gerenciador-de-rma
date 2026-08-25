<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscarRmasTest extends TestCase
{
    use RefreshDatabase;

    public function test_busca_por_texto(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'HD com clique estranho']);
        Rma::factory()->create(['descricao' => 'Fonte queimada']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=texto&valor=clique');

        $response->assertOk();
        $response->assertSee('HD com clique estranho');
        $response->assertDontSee('Fonte queimada');
    }

    public function test_busca_por_serial(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'RMA com serial', 'sn' => 'ABC123']);
        Rma::factory()->create(['descricao' => 'RMA com outro serial', 'sn' => 'XYZ999']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=serial&valor=ABC123');

        $response->assertOk();
        $response->assertSee('RMA com serial');
        $response->assertDontSee('RMA com outro serial');
    }

    public function test_busca_vazia_nao_lista_nada(): void
    {
        $usuario = User::factory()->create(['papel' => Papel::Leitura]);
        Rma::factory()->create(['descricao' => 'Não deveria aparecer']);

        $response = $this->actingAs($usuario)->get('/rmas?tipo=texto&valor=');

        $response->assertOk();
        $response->assertDontSee('Não deveria aparecer');
    }
}
