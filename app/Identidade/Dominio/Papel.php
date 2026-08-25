<?php

namespace App\Identidade\Dominio;

enum Papel
{
    case Bloqueado;
    case Leitura;
    case Operador;
    case Supervisor;
    case SuperAdministrador;

    public function podeAutenticar(): bool
    {
        return $this !== self::Bloqueado;
    }

    public function podeGravar(): bool
    {
        return match ($this) {
            self::Bloqueado, self::Leitura => false,
            default => true,
        };
    }

    public function podeGerenciarUsuarios(): bool
    {
        return match ($this) {
            self::Supervisor, self::SuperAdministrador => true,
            default => false,
        };
    }

    public function ocultoDaListagemDeUsuarios(): bool
    {
        return $this === self::SuperAdministrador;
    }
}
