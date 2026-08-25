<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;

/**
 * LEG-RMA-017. Atualiza `solucao` independente de transição de status (o legado
 * permite editar via `salvar_rma.php` a qualquer momento) e reaplica
 * `comSnretornoAutoPreenchido()` (RN-15).
 */
final class RegistrarSolucao
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function registrar(User $ator, Rma $rma, Solucao $solucao): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);

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
            status: $rma->status,
            recebidoEm: $rma->recebidoEm,
            encaminhadoEm: $rma->encaminhadoEm,
            concluidoEm: $rma->concluidoEm,
            arquivadoEm: $rma->arquivadoEm,
            protocolo: $rma->protocolo,
            solucao: $solucao,
            snretorno: $rma->snretorno,
            destinatarioType: $rma->destinatarioType,
            destinatarioId: $rma->destinatarioId,
        );

        return $this->repositorio->atualizar($comSolucao->comSnretornoAutoPreenchido());
    }
}
