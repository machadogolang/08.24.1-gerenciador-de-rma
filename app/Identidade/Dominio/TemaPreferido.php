<?php

namespace App\Identidade\Dominio;

enum TemaPreferido: string
{
    case V1 = 'v1';
    case V2 = 'v2';

    public function alternar(): self
    {
        return match ($this) {
            self::V1 => self::V2,
            self::V2 => self::V1,
        };
    }
}
