<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;

/**
 * `LEG-RMA-036` — fluxo único de crédito (reconstrói só a intenção do módulo de
 * créditos quebrado em TEMA V2, `LEG-RMA-048`, ver `proposal.md`). Sem transição
 * automática `PendenteCredito`→`GeradoCredito`→`credito_disponivel=true` — o legado
 * também não automatiza (controle manual em duas camadas independentes, confirmado em
 * `modelo-dominio-rma-legado.md`); `EVO-AUT-002` registra a automação como melhoria
 * futura, não implementada agora.
 */
final class MarcarCreditoDisponivel
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function marcar(User $ator, Rma $rma): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->solucao === Solucao::GeradoCredito, 422);

        // ARQ-001 — reconstrução manual campo a campo trocada por `comAlteracoes()`:
        // a versão anterior desta classe apagava silenciosamente qualquer campo do
        // agregado que não estivesse na lista explícita (achado ao adicionar `pn`/
        // `snid`, VIS-V1-003 — marcar crédito disponível zerava os dois).
        $comCreditoDisponivel = $rma->comAlteracoes(['creditoDisponivel' => true]);

        return $this->repositorio->atualizar($comCreditoDisponivel);
    }
}
