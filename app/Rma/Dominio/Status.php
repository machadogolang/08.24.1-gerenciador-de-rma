<?php

namespace App\Rma\Dominio;

/**
 * Sem backing numérico (princípio do projeto: sem número mágico) e sem case
 * `Retornou` — `LEG-RMA-016`, código morto confirmado nos dois temas do legado (rota
 * existe no `.htaccess`, nenhuma transição jamais grava esse valor), `NÃO RECONSTRUIR`.
 */
enum Status
{
    case Entrada;
    case Recebido;
    case Encaminhado;
    case Concluido;
    case Arquivado;

    public function podeReceber(): bool
    {
        return $this === self::Entrada;
    }

    public function podeEncaminhar(): bool
    {
        return $this === self::Recebido;
    }

    public function podeConcluir(): bool
    {
        return $this === self::Encaminhado;
    }

    /**
     * [INFERIDO] — o legado não documenta explicitamente a restrição de status de
     * origem para arquivar; assume-se Entrada/Recebido/Encaminhado (não Concluido, não
     * já Arquivado), coerente com "pausa reabrível" (ver proposal.md).
     */
    public function podeArquivar(): bool
    {
        return match ($this) {
            self::Entrada, self::Recebido, self::Encaminhado => true,
            default => false,
        };
    }

    public function podeReverterParaEntrada(): bool
    {
        return match ($this) {
            self::Recebido, self::Encaminhado => true,
            default => false,
        };
    }
}
