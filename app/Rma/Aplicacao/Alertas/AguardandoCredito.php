<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Solucao;
use Illuminate\Database\Eloquent\Collection;

/**
 * RMAs com `solucao=PendenteCredito` aguardando a segunda camada de controle
 * (`solucao=GeradoCredito` + `MarcarCreditoDisponivel`) — mesma família de consulta de
 * leitura da Fase 5 (`app/Rma/Aplicacao/Alertas/`), reforça que crédito não é módulo
 * próprio (`INV-RMA-05` §3). Filtro inteiro no SQL, mesma disciplina das 10 regras da
 * Fase 5.
 */
final class AguardandoCredito
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('solucao', Solucao::PendenteCredito)
            ->get();
    }
}
