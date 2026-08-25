<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\Date;

/**
 * LEG-RMA-014 — reproduz `15.8.1/banco.php::arquivar()` (TEMA V2, funcional). TEMA V1
 * (`14.6.1/post/arquivar.php`) tem `Fatal Error` incondicional (`new controle()`,
 * classe inexistente) — confirmado por leitura de código-fonte, não reproduzido (ver
 * `proposal.md`). Exige `Papel::podeGerenciarUsuarios()` — [INFERIDO].
 */
final class ArquivarRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function arquivar(User $ator, Rma $rma): Rma
    {
        abort_unless($ator->papel->podeGerenciarUsuarios(), 403);
        abort_unless($rma->status->podeArquivar(), 422);

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
            status: Status::Arquivado,
            recebidoEm: $rma->recebidoEm,
            encaminhadoEm: $rma->encaminhadoEm,
            concluidoEm: $rma->concluidoEm,
            arquivadoEm: Date::now(),
            protocolo: $rma->protocolo,
            solucao: $rma->solucao,
            snretorno: $rma->snretorno,
            destinatarioType: $rma->destinatarioType,
            destinatarioId: $rma->destinatarioId,
        );

        return $this->repositorio->atualizar($atualizado);
    }
}
