<?php

namespace Tests\Unit\Rma;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\UrgenciaPorThreshold;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RN-12 (`LEG-RMA-029`). `valor` exatamente R$75,00 NÃO dispara — comparação é `>`,
 * não `>=` (operador estrito, mesmo princípio das regras de data).
 */
class UrgenciaPorThresholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_valor_acima_de_75_cliente_fora_de_estoque(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'valor' => 75.01,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_valor_exatamente_75_nao_dispara_operador_estrito(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'valor' => 75.00,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_valor_baixo(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'valor' => 10.00,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_marcarestoque_true_mesmo_com_valor_alto(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => true,
            'valor' => 500.00,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_origem_fora_do_par_cliente_licitacao(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Loja->value,
            'marcarestoque' => false,
            'valor' => 500.00,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_dispara_por_prioridade_alta_independente_de_valor(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Loja->value,
            'marcarestoque' => true,
            'valor' => null,
            'prioridade' => Prioridade::Alta,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_prazo_legal_ja_estourou(): void
    {
        // Regra é "ainda dá tempo de agir" — created_at > hoje-30d. Um RMA criado há
        // 31 dias já passou do prazo legal e não entra aqui (ver desvio documentado em
        // `UrgenciaPorThreshold`, coberto por outras regras, ex. `GarantiaFornecedorExpirada`).
        $rma = Rma::factory()->create([
            'status' => Status::Entrada,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'valor' => 500.00,
            'created_at' => now()->subDays(31),
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'valor' => 500.00,
        ]);

        $resultado = (new UrgenciaPorThreshold())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
