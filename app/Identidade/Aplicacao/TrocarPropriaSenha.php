<?php

namespace App\Identidade\Aplicacao;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class TrocarPropriaSenha
{
    public function trocar(User $usuario, string $senhaAtual, string $novaSenha): void
    {
        if (! Hash::check($senhaAtual, $usuario->password)) {
            throw new SenhaAtualIncorretaException;
        }
        $usuario->update(['password' => Hash::make($novaSenha)]);
    }
}
