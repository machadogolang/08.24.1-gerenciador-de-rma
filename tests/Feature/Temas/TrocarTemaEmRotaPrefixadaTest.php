<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAR-V2-THEME-01 - troca V1 <-> V2 funcionando tambem partindo de rotas QA
 * prefixadas (`/v1/...` e `/v2/...`), onde `ResolverTemaAtivo` forca o tema pelo
 * prefixo e `back()` anularia a troca visualmente.
 */
class TrocarTemaEmRotaPrefixadaTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioComTema(TemaPreferido $tema): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => $tema,
        ]);
    }

    #[Test]
    #[DataProvider('paresDeRotaProvider')]
    public function troca_partindo_de_rota_prefixada_redireciona_para_a_contraparte(
        TemaPreferido $temaInicial,
        TemaPreferido $temaEsperado,
        string $rotaInicial,
        string $rotaEsperada,
    ): void {
        $usuario = $this->usuarioComTema($temaInicial);
        $rma = Rma::factory()->create();

        $resposta = $this->actingAs($usuario)
            ->post('/tema/alternar', [], ['Referer' => route($rotaInicial, ['rma' => $rma->id])]);

        $resposta->assertRedirect(route($rotaEsperada, ['rma' => $rma->id]));
        $this->assertSame($temaEsperado, $usuario->fresh()->tema_preferido);
    }

    public static function paresDeRotaProvider(): array
    {
        return [
            'v2 detalhe -> v1 detalhe' => [
                TemaPreferido::V2,
                TemaPreferido::V1,
                'v2.rmas.show',
                'v1.rmas.show',
            ],
            'v1 detalhe -> v2 detalhe' => [
                TemaPreferido::V1,
                TemaPreferido::V2,
                'v1.rmas.show',
                'v2.rmas.show',
            ],
        ];
    }

    #[Test]
    public function rota_canonica_mantem_back_e_a_preferencia_passa_a_mandar_na_view(): void
    {
        $usuario = $this->usuarioComTema(TemaPreferido::V1);
        $rma = Rma::factory()->create();
        $urlCanonica = route('rmas.show', ['rma' => $rma->id]);

        $resposta = $this->actingAs($usuario)
            ->post('/tema/alternar', [], ['Referer' => $urlCanonica]);

        $resposta->assertRedirect($urlCanonica);
        $this->assertSame(TemaPreferido::V2, $usuario->fresh()->tema_preferido);

        $this->actingAs($usuario->fresh())
            ->get($urlCanonica)
            ->assertOk()
            ->assertViewIs('temas.v2.rma.show');
    }

    #[Test]
    public function troca_partindo_de_rota_prefixada_persiste_apos_relogin(): void
    {
        $usuario = User::factory()->create([
            'email' => 'troca-prefixo@rma.local',
            'password' => bcrypt('senha-troca'),
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V2,
        ]);
        $rma = Rma::factory()->create();

        $this->actingAs($usuario)
            ->post('/tema/alternar', [], ['Referer' => route('v2.rmas.show', ['rma' => $rma->id])])
            ->assertRedirect(route('v1.rmas.show', ['rma' => $rma->id]));

        $this->assertSame(TemaPreferido::V1, $usuario->fresh()->tema_preferido);

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'troca-prefixo@rma.local',
            'password' => 'senha-troca',
        ]);

        $this->assertSame('v1', session('tema_preferido'));
        $this->actingAs($usuario->fresh())
            ->get(route('rmas.show', ['rma' => $rma->id]))
            ->assertOk()
            ->assertViewIs('temas.v1.rma.show');
    }
}
