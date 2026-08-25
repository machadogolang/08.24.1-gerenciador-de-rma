<?php

namespace App\Rma\Aplicacao;

use App\Mail\RmaConcluidoMailable;
use App\Rma\Dominio\Eventos\RmaConcluido;
use Illuminate\Support\Facades\Mail;

/**
 * `LEG-RMA-045` (`ezequiel()`) — destinatário via `config('rma.notificacoes.conclusao')`
 * (`.env`), nunca hardcoded como no legado.
 */
final class EnviarNotificacaoDeConclusao
{
    public function handle(RmaConcluido $evento): void
    {
        $destinatario = config('rma.notificacoes.conclusao');

        if (blank($destinatario)) {
            return;
        }

        Mail::to($destinatario)->send(new RmaConcluidoMailable($evento->rma));
    }
}
