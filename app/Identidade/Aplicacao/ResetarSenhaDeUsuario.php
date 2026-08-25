<?php

namespace App\Identidade\Aplicacao;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class ResetarSenhaDeUsuario
{
    public function resetar(User $ator, User $alvo, string $novaSenha): void
    {
        // ARQ-003 (`INV-RMA-10`): Supervisor não pode resetar senha de um
        // SuperAdministrador — `podeOperarSobrePapel` já inclui `podeGerenciarUsuarios()`.
        abort_unless($ator->papel->podeOperarSobrePapel($alvo->papel), 403);
        $alvo->update(['password' => Hash::make($novaSenha)]);
    }
}
