<?php

namespace App\Rma\Dominio;

/**
 * Estado de lançamento contábil da NF de retorno (`rmas.lancadoretorno`) — usado por
 * `NfRetornoPendenteDeLancar` (RN-03). Backed string.
 */
enum StatusDeLancamento: string
{
    case Pendente = 'pendente';
    case NfDevolucao = 'nf_devolucao';
    case SemMovimentacao = 'sem_movimentacao';
    case Nao = 'nao';
    case Sim = 'sim';
}
