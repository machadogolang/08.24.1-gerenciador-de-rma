<?php

namespace App\Rma\Dominio;

/**
 * 16 valores confirmados por leitura direta do `<select name="solucao">` real de
 * `15.8.1/page/rma.php:578-595` (arquivo ISO-8859-1, decodificado para conferência).
 * Não inventar um 17º valor — ver nota de rastreabilidade no `design.md`.
 */
enum Solucao: string
{
    case Reparo = 'REPARO';
    case TrocaDoProduto = 'TROCA DO PRODUTO';
    case TrocaDePecaInterna = 'TROCA DE PECA INTERNA';
    case PendenteCredito = 'PENDENTE CREDITO';
    case GeradoCredito = 'GERADO CREDITO';
    case DevolucaoDoProduto = 'DEVOLUCAO DO PRODUTO';
    case ReembolsoDoDinheiro = 'REEMBOLSO DO DINHEIRO';
    case OrcamentoPago = 'ORCAMENTO PAGO';
    case OrcamentoPendente = 'ORCAMENTO PENDENTE';
    case OrcamentoNegado = 'ORCAMENTO NEGADO';
    case ReparoPeloRma = 'REPARO PELO RMA';
    case CasoSolucionado = 'CASO SOLUCIONADO';
    case TestadoTudoOk = 'TESTADO TUDO OK';
    case Procon = 'PROCON';
    case DescritoNaObservacao = 'DESCRITO NA OBSERVACAO';
    case SemGarantia = 'SEM GARANTIA';

    /**
     * RN-15 (`LEG-RMA-047`) — ausente em TEMA V1, funcionalidade nova nesta fase.
     */
    public function implicaMesmoAparelhoDeRetorno(): bool
    {
        return match ($this) {
            self::TrocaDePecaInterna, self::Reparo, self::OrcamentoPago,
            self::OrcamentoNegado, self::ReparoPeloRma, self::TestadoTudoOk => true,
            default => false,
        };
    }
}
