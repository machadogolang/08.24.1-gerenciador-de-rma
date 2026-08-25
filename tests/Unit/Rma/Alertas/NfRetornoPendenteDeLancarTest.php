<?php

namespace Tests\Unit\Rma\Alertas;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\NfRetornoPendenteDeLancar;
use App\Rma\Dominio\Status;
use App\Rma\Dominio\StatusDeLancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NfRetornoPendenteDeLancarTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_quando_concluido_e_lancamento_pendente(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'lancadoretorno' => StatusDeLancamento::Pendente,
        ]);

        $resultado = (new NfRetornoPendenteDeLancar())->listar();

        $this->assertTrue($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_quando_ja_lancado(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'lancadoretorno' => StatusDeLancamento::Sim,
        ]);

        $resultado = (new NfRetornoPendenteDeLancar())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_caso_limite_lancamento_adjacente_nf_devolucao_nao_dispara(): void
    {
        // Prova que só o valor exato `Pendente` dispara, não qualquer valor "não Sim".
        $rma = Rma::factory()->create([
            'status' => Status::Concluido,
            'lancadoretorno' => StatusDeLancamento::NfDevolucao,
        ]);

        $resultado = (new NfRetornoPendenteDeLancar())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }

    public function test_nao_dispara_para_outro_status(): void
    {
        $rma = Rma::factory()->create([
            'status' => Status::Recebido,
            'lancadoretorno' => StatusDeLancamento::Pendente,
        ]);

        $resultado = (new NfRetornoPendenteDeLancar())->listar();

        $this->assertFalse($resultado->contains('id', $rma->id));
    }
}
