<?php

namespace Tests\Feature\Temas;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceV3Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create([
            'papel' => Papel::Operador,
            'tema_preferido' => TemaPreferido::V1,
        ]);
    }

    public function test_dashboard_v3_executa_em_numero_baixo_e_constante_de_queries(): void
    {
        $usuario = $this->usuario();

        Rma::factory()->count(15)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($usuario)->get('/v3');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk();
        // Garante que o dashboard agrupa contagens e nao faz queries por item
        $this->assertLessThanOrEqual(15, count($queries), 'Dashboard V3 gerou excesso de consultas SQL.');
    }

    public function test_listagem_rmas_v3_previne_n_mais_um_com_eager_loading(): void
    {
        $usuario = $this->usuario();

        $cliente = Cliente::factory()->create();
        $fabricante = Fabricante::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        Rma::factory()->count(20)->create([
            'cliente_id' => $cliente->id,
            'fabricante_id' => $fabricante->id,
            'fornecedor_id' => $fornecedor->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($usuario)->get('/v3/rmas');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk();
        // Com with(['fabricante:id,nome', 'fornecedor:id,nome', 'cliente:id,nome']),
        // nao pode haver uma query por RMA retornado.
        $this->assertLessThanOrEqual(10, count($queries), 'Listagem de RMAs V3 gerou excesso de consultas SQL (possivel N+1).');
    }

    public function test_bundle_v3_esta_compilado_e_mantem_tamanho_reduzido(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath, 'O build de producao do Vite nao foi encontrado em public/build.');

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);

        $this->assertArrayHasKey('resources/js/temas/v3.js', $manifest);
        $v3JsEntry = $manifest['resources/js/temas/v3.js'];

        $jsFile = public_path('build/' . $v3JsEntry['file']);
        $this->assertFileExists($jsFile);
        $this->assertLessThan(50 * 1024, filesize($jsFile), 'Bundle JS do Tema V3 excedeu 50KB.');

        if (! empty($v3JsEntry['css'])) {
            foreach ($v3JsEntry['css'] as $cssRel) {
                $cssFile = public_path('build/' . $cssRel);
                $this->assertFileExists($cssFile);
                $this->assertLessThan(50 * 1024, filesize($cssFile), 'Folha CSS do Tema V3 excedeu 50KB.');
            }
        }
    }
}
