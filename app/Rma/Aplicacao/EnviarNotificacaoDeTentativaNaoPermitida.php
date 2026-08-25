<?php

namespace App\Rma\Aplicacao;

use App\Rma\Dominio\Eventos\TentativaDeGravacaoNaoPermitida;
use Illuminate\Support\Facades\Log;

/**
 * `LEG-RMA-045` (`naopermitido()`). Assina `TentativaDeGravacaoNaoPermitida`,
 * disparado por `RmaPolicy::update()` quando um usuário sem `Papel::podeGravar()`
 * tenta editar/gravar um RMA.
 *
 * **Decisão de implementação (sem Mailable dedicado):** diferente de
 * `EnviarNotificacaoDeConclusao`, o `design.md`/`tasks.md` não listam um Mailable
 * específico para este caso — só o listener. O canal escolhido é o log de aplicação
 * (`Log::warning`, canal padrão), registrando quem tentou e quando; é o equivalente
 * funcional auditável ao e-mail avulso do legado sem introduzir um segundo template de
 * e-mail para um evento de acesso negado (que já fica auditável de outra forma: o
 * próprio `RmaPolicy::update()` devolve 403). Se o produto pedir e-mail de fato aqui no
 * futuro, é trocar `Log::warning` por `Mail::to(...)->send(...)` sem mudar a assinatura
 * do listener nem o disparo do evento.
 */
final class EnviarNotificacaoDeTentativaNaoPermitida
{
    public function handle(TentativaDeGravacaoNaoPermitida $evento): void
    {
        Log::warning('Tentativa de gravação de RMA não permitida.', [
            'user_id' => $evento->ator->id,
            'papel' => $evento->ator->papel->name,
        ]);
    }
}
