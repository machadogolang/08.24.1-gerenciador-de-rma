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

    /**
     * ARQ-003 (`INV-RMA-10`) — checagem por alvo: Supervisor não pode operar sobre um
     * SuperAdministrador (nem trocar seu papel, nem resetar sua senha), mesmo por URL
     * direta. Sempre via método nomeado do enum, nunca por ordinal/inteiro.
     */
    public function gerenciarUsuario(User $ator, User $alvo): bool
    {
        return $ator->papel->podeOperarSobrePapel($alvo->papel);
    }
}
