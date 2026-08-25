<?php

namespace App\Rma\Dominio;

/**
 * RN-13/RN-14 — domínio fechado de origem do RMA, confirmado nos dois temas do legado.
 * Backed string (compatível com o valor já persistido em `rmas.origem` desde a Fase 3).
 * Ver `App\Rma\Dominio\Rma::comNormalizacaoDeGravacao()` (Fase 3) para os valores que a
 * normalização realmente produz — nem todos os 10 cases aqui nascem dessa normalização
 * (alguns só existem como valor de entrada válido, ex.: `Casa`, `MercadoLivre`).
 */
enum Origem: string
{
    case Unknown = 'Unknown';
    case Loja = 'Loja';
    case Casa = 'Casa';
    case Cliente = 'Cliente';
    case Licitacao = 'Licitação';
    case Leilao = 'Leilão';
    case MercadoLivre = 'Mercado Livre';
    case Credito = 'Credito';
    case Ac = 'AC';
    case Rolo = 'Rolo';
}
