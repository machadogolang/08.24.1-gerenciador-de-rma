<?php

namespace App\Rma\Dominio;

/**
 * Sem backing (mesmo princípio de `Status`) — o nome do case é persistido como string
 * pelo cast Eloquent (`App\Models\ModificacaoDeRma`). Um valor por evento de domínio
 * assinado por `RegistrarModificacaoDeRma` (Fase 7).
 */
enum AcaoDeModificacao
{
    case Criacao;
    case Edicao;
    case Receber;
    case Encaminhar;
    case Concluir;
    case Arquivar;
    case Reverter;
    case RegistrarSolucao;
}
