<?php

namespace App\Rma\Dominio;

/**
 * Sem backing (princípio do projeto: sem número mágico) e sem case `Urgente` — RN-08:
 * valor usado em ~14 arquivos de destaque visual do legado, mas inexistente no
 * `<select>` real (resíduo de um domínio anterior de 4 níveis). Reproduzir um case
 * morto que nenhum formulário grava violaria "sem string mágica sem significado" tanto
 * quanto reproduzir um bug.
 */
enum Prioridade
{
    case Baixa;
    case Media;
    case Alta;

    public function alta(): bool
    {
        return $this === self::Alta;
    }
}
