<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TelasSecundariasShellTest extends TestCase
{
    use RefreshDatabase;

    public static function creditoShellProvider(): array
    {
        return [
            // PAR15-CREDIT-001/003 - V2 reproduz a tabela do 15.8.1/page/credito.php;
            // PAR14-CREDIT-001 - V1 mantem o painel de relatorios do 14.6.1.
            'v1' => [TemaPreferido::V1, 'formButtonMENU', 'RELATORIO DE CREDITOS DISPONIVEIS'],
            'v2' => [TemaPreferido::V2, 'header-v2', 'PROTOCOLO'],
        ];
    }

    #[DataProvider('creditoShellProvider')]
    public function test_credito_renderiza_dentro_do_shell_do_tema(TemaPreferido $tema, string $marcadorShell, string $conteudo): void
    {
        $usuario = User::factory()->create(['tema_preferido' => $tema]);

        $this->actingAs($usuario)
            ->get('/rmas-credito')
            ->assertOk()
            ->assertSee($marcadorShell, false)
            ->assertSee($conteudo, false);
    }
}
