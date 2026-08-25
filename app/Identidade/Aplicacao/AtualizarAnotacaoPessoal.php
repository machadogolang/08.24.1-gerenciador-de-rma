<?php

namespace App\Identidade\Aplicacao;

use App\Models\User;

final class AtualizarAnotacaoPessoal
{
    public function atualizar(User $usuario, ?string $anotacao): void
    {
        $usuario->update(['anotacao' => $anotacao]);
    }
}
