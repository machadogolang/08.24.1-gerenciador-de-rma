<?php

namespace App\Policies;

use App\Models\User;

/**
 * Leitura é liberada a qualquer usuário autenticado (autenticação já filtra
 * `Papel::Bloqueado`); escrita delega a `Papel::podeGravar()`, já existente da Fase 1.
 */
class FabricantePolicy
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

    public function delete(User $ator): bool
    {
        return $ator->papel->podeGravar();
    }
}
