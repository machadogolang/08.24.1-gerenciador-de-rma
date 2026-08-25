<?php

namespace App\Policies;

use App\Models\User;

/**
 * Mesmo padrão de `ClientePolicy` (Fase 2): leitura liberada a qualquer autenticado,
 * escrita delega a `Papel::podeGravar()`. `App\Models\Rma` só é usado aqui como âncora
 * de autorização — nunca sai do controller como objeto exposto (a leitura/escrita real
 * passa pelos casos de uso de `App\Rma\Aplicacao`, que devolvem `Dominio\Rma`).
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
        return $ator->papel->podeGravar();
    }
}
