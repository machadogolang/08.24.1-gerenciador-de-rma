<?php

namespace App\Identidade\Dominio;

/**
 * Tema visual ativo. V1/V2 preservam o legado; V3 e a Console Operacional
 * Adaptativa (Tema V3, oculto nesta fase).
 */
enum TemaPreferido: string
{
    case V1 = 'v1';
    case V2 = 'v2';
    case V3 = 'v3';

    /**
     * Alternancia binaria preservada para V1/V2 (T3-17 sera a selecao explicita).
     * V3 nunca e alvo de alternancia publica.
     */
    public function alternar(): self
    {
        return match ($this) {
            self::V1 => self::V2,
            self::V2 => self::V1,
            self::V3 => throw new \LogicException('Tema V3 nao usa alternancia binaria.'),
        };
    }
}
