<?php

namespace Tests\Feature\Rma;

use App\Identidade\Dominio\Papel;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Eventos\TentativaDeGravacaoNaoPermitida;
use App\Rma\Dominio\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * `LEG-RMA-045` (`naopermitido()`). `RmaPolicy::update()` dispara
 * `TentativaDeGravacaoNaoPermitida` antes de devolver `false` — o listener
 * `EnviarNotificacaoDeTentativaNaoPermitida` assina o evento e loga a tentativa.
 */
class EnviarNotificacaoDeTentativaNaoPermitidaTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_evento_quando_usuario_sem_podegravar_tenta_editar(): void
    {
        Event::fake([TentativaDeGravacaoNaoPermitida::class]);

        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $response = $this->actingAs($leitura)->put("/rmas/{$rma->id}", [
            'descricao' => 'Não deveria salvar',
            'defeito' => 'X',
        ]);

        $response->assertForbidden();
        Event::assertDispatched(TentativaDeGravacaoNaoPermitida::class, function (TentativaDeGravacaoNaoPermitida $evento) use ($leitura) {
            return $evento->ator->id === $leitura->id;
        });
    }

    public function test_nao_dispara_evento_quando_usuario_pode_gravar(): void
    {
        Event::fake([TentativaDeGravacaoNaoPermitida::class]);

        $operador = User::factory()->create(['papel' => Papel::Operador]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $this->actingAs($operador)->put("/rmas/{$rma->id}", [
            'descricao' => 'Ok',
            'defeito' => 'X',
        ]);

        Event::assertNotDispatched(TentativaDeGravacaoNaoPermitida::class);
    }

    public function test_listener_registra_log_de_tentativa_negada(): void
    {
        Log::spy();

        $leitura = User::factory()->create(['papel' => Papel::Leitura]);
        $rma = RmaEloquent::factory()->create(['status' => Status::Entrada]);

        $this->actingAs($leitura)->put("/rmas/{$rma->id}", [
            'descricao' => 'Não deveria salvar',
            'defeito' => 'X',
        ]);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $mensagem, array $contexto) use ($leitura) {
                return $contexto['user_id'] === $leitura->id;
            });
    }
}
