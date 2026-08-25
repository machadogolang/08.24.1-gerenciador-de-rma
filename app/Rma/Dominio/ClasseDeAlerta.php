<?php

namespace App\Rma\Dominio;

/**
 * RN-11 — classificação visual de inconformidade. Sem backing (sem número mágico); a
 * fidelidade de apresentação (classe CSS/Blade por tema) fica para a Fase 8 — aqui só
 * o enum de domínio existe. Devolvido por `Rma::classeDeAlerta()`.
 */
enum ClasseDeAlerta
{
    case Inconformidade;
    case Urgente;
    case SemGarantia;
    case Neutro; // equivalente a TrZebrada — sem significado de alerta
}
