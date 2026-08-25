<?php

namespace App\Identidade\Aplicacao;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class ResetarSenhaDeUsuario
{
    public function resetar(User $ator, User $alvo, string $novaSenha): void
    {
        abort_unless($ator->papel->podeGerenciarUsuarios(), 403);
        $alvo->update(['password' => Hash::make($novaSenha)]);
    }
}
