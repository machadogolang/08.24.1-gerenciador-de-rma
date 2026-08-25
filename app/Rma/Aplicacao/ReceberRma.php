<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\Date;

/**
 * LEG-RMA-011.
 */
final class ReceberRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function receber(User $ator, Rma $rma): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->status->podeReceber(), 422);

        $atualizado = new Rma(
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
            status: Status::Recebido,
            recebidoEm: Date::now(),
            encaminhadoEm: $rma->encaminhadoEm,
            concluidoEm: $rma->concluidoEm,
            arquivadoEm: $rma->arquivadoEm,
            protocolo: $rma->protocolo,
            solucao: $rma->solucao,
            snretorno: $rma->snretorno,
            destinatarioType: $rma->destinatarioType,
            destinatarioId: $rma->destinatarioId,
        );

        return $this->repositorio->atualizar($atualizado);
    }
}
