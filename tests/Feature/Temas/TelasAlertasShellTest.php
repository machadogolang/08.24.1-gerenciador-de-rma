<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FRONT-003/UI-04 — Painel de Alertas no shell V1/V2 com links de RMA no contrato.
 */
class TelasAlertasShellTest extends TestCase
{
    use RefreshDatabase;

    public static function temasProvider(): array
    {
        return [
            'v1' => [TemaPreferido::V1],
            'v2' => [TemaPreferido::V2],
        ];
    }

    #[DataProvider('temasProvider')]
    public function test_painel_de_alertas_renderiza_no_shell_do_tema(TemaPreferido $tema): void
    {
        $usuario = User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'prioridade' => Prioridade::Alta,
            'descricao' => 'Alerta prioridade alta shell',
        ]);

        $response = $this->actingAs($usuario)->get(route('rmas.alertas'));

        $response->assertOk();
        $response->assertViewIs("temas.{$tema->value}.rma.alertas.index");
        $response->assertSee('class="painel-alertas"', false);
        $response->assertSee('class="acao acao--secundaria acao--compacta"', false);
        $response->assertSeeText('Alerta prioridade alta shell');
        $response->assertSee(route('rmas.show', $rma->id), false);
    }
}
