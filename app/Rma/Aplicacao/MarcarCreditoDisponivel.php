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

        $comCreditoDisponivel = new Rma(
            id: $rma->id,
            descricao: $rma->descricao,
            fabricanteId: $rma->fabricanteId,
            fornecedorId: $rma->fornecedorId,
            modelo: $rma->modelo,
            sn: $rma->sn,
            os: $rma->os,
            origem: $rma->origem,
            empresa: $rma->empresa,
            clienteId: $rma->clienteId,
            defeito: $rma->defeito,
            observacao: $rma->observacao,
            status: $rma->status,
            recebidoEm: $rma->recebidoEm,
            encaminhadoEm: $rma->encaminhadoEm,
            concluidoEm: $rma->concluidoEm,
            arquivadoEm: $rma->arquivadoEm,
            protocolo: $rma->protocolo,
            solucao: $rma->solucao,
            snretorno: $rma->snretorno,
            destinatarioType: $rma->destinatarioType,
            destinatarioId: $rma->destinatarioId,
            prioridade: $rma->prioridade,
            marcarestoque: $rma->marcarestoque,
            nfcompra: $rma->nfcompra,
            nfcompraEmissao: $rma->nfcompraEmissao,
            nfcompraChave: $rma->nfcompraChave,
            nfvenda: $rma->nfvenda,
            nfvendaEmissao: $rma->nfvendaEmissao,
            nfvendaChave: $rma->nfvendaChave,
            lancadoretorno: $rma->lancadoretorno,
            valor: $rma->valor,
            createdAt: $rma->createdAt,
            creditoDisponivel: true,
        );

        return $this->repositorio->atualizar($comCreditoDisponivel);
    }
}
