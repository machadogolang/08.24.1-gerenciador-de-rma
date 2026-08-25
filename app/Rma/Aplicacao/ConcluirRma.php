<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\Eventos\RmaConcluido;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\Date;

/**
 * LEG-RMA-013. Exige `solucao` preenchida, aplica `comSnretornoAutoPreenchido()`
 * (RN-15) e dispara `RmaConcluido` (sem listener nesta fase — Fase 7 assina).
 */
final class ConcluirRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function concluir(User $ator, Rma $rma, Solucao $solucao): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->status->podeConcluir(), 422);

        $comSolucao = new Rma(
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
            status: Status::Concluido,
            recebidoEm: $rma->recebidoEm,
            encaminhadoEm: $rma->encaminhadoEm,
            concluidoEm: Date::now(),
            arquivadoEm: $rma->arquivadoEm,
            protocolo: $rma->protocolo,
            solucao: $solucao,
            snretorno: $rma->snretorno,
            destinatarioType: $rma->destinatarioType,
            destinatarioId: $rma->destinatarioId,
        );

        $atualizado = $this->repositorio->atualizar($comSolucao->comSnretornoAutoPreenchido());

        RmaConcluido::dispatch($atualizado);

        return $atualizado;
    }
}
