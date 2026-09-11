<?php

namespace App\Identidade\Aplicacao;

use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;

final class DefinirTemaPreferido
{
    /**
     * T3-17 - Selecao explicita de tema visual.
     * Persistencia de V1 e V2 no banco. V3 mantido em sessao/QA ate T3-GATE.
     */
    public function definir(User $usuario, TemaPreferido $novoTema, bool $persistir = true): TemaPreferido
    {
        if ($persistir && $novoTema !== TemaPreferido::V3) {
            $usuario->update(['tema_preferido' => $novoTema]);
        }

        return $novoTema;
    }
}
