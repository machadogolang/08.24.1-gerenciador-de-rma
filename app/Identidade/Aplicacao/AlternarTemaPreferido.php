<?php

namespace App\Identidade\Aplicacao;

use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;

final class AlternarTemaPreferido
{
    public function alternar(User $usuario): TemaPreferido
    {
        $novo = $usuario->tema_preferido->alternar();
        $usuario->update(['tema_preferido' => $novo]);

        return $novo;
    }
}
