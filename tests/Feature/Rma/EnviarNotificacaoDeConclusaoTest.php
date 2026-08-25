<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Mail\RmaConcluidoMailable;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * `LEG-RMA-045` (`ezequiel()`) — destinatário lido de `config('rma.notificacoes.
 * conclusao')`, nunca hardcoded no código.
 */
class EnviarNotificacaoDeConclusaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_email_de_conclusao_para_destinatario_configurado(): void
    {
        config(['rma.notificacoes.conclusao' => 'auditoria@cellsystem.example']);
        Mail::fake();

        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", [
            'solucao' => Solucao::DevolucaoDoProduto->value,
        ]);

        Mail::assertSent(RmaConcluidoMailable::class, function (RmaConcluidoMailable $mail) use ($rma) {
            return $mail->hasTo('auditoria@cellsystem.example')
                && $mail->rma->id === $rma->id;
        });
    }

    public function test_nao_envia_quando_destinatario_nao_configurado(): void
    {
        config(['rma.notificacoes.conclusao' => null]);
        Mail::fake();

        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Encaminhado]);

        $this->actingAs($operador)->post("/rmas/{$rma->id}/concluir", [
            'solucao' => Solucao::DevolucaoDoProduto->value,
        ]);

        Mail::assertNothingSent();
    }
}
