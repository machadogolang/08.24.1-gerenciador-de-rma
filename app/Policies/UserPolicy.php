<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determina se o ator pode gerenciar usuários (listar, trocar papel, resetar
     * senha de outro usuário). Nunca compara papel por ordinal/inteiro — sempre via
     * método nomeado do enum.
     */
    public function gerenciar(User $ator): bool
    {
        return $ator->papel->podeGerenciarUsuarios();
    }
}
