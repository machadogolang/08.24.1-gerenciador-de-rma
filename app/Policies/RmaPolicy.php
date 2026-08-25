<?php

namespace App\Policies;

use App\Models\User;
use App\Rma\Dominio\Eventos\TentativaDeGravacaoNaoPermitida;

/**
 * Mesmo padrão de `ClientePolicy` (Fase 2): leitura liberada a qualquer autenticado,
 * escrita delega a `Papel::podeGravar()`. `App\Models\Rma` só é usado aqui como âncora
 * de autorização — nunca sai do controller como objeto exposto (a leitura/escrita real
 * passa pelos casos de uso de `App\Rma\Aplicacao`, que devolvem `Dominio\Rma`).
 *
 * **Fase 7 (`LEG-RMA-045`, `naopermitido()`):** `update()` dispara
 * `TentativaDeGravacaoNaoPermitida` explicitamente antes de devolver `false`. A
 * autorização continua decidindo só `true`/`false`; o evento é responsabilidade
 * explícita e testável de notificar a tentativa negada, não um side-effect escondido
 * — decisão registrada em `design.md` (rejeita ouvir
 * `Illuminate\Auth\Access\AuthorizationException` globalmente, que exigiria acoplar a
 * um middleware).
 */
class RmaPolicy
{
    public function viewAny(User $ator): bool
    {
        return true;
    }

    public function view(User $ator): bool
    {
        return true;
    }

    public function create(User $ator): bool
    {
        return $ator->papel->podeGravar();
    }

    public function update(User $ator): bool
    {
        if ($ator->papel->podeGravar()) {
            return true;
        }

        TentativaDeGravacaoNaoPermitida::dispatch($ator);

        return false;
    }
}
