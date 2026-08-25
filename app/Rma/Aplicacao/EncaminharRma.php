<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\Eventos\RmaEncaminhado;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\Date;

/**
 * LEG-RMA-012. Exige `destinatario` preenchido antes de aceitar — no legado essa
 * validação existe só em JS; aqui vira regra de domínio real (decisão registrada no
 * `proposal.md`). Fase 7: dispara `RmaEncaminhado` ao final.
 */
final class EncaminharRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function encaminhar(User $ator, Rma $rma, string $destinatarioType, int $destinatarioId): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->status->podeEncaminhar(), 422);
        abort_unless($destinatarioType !== '' && $destinatarioId > 0, 422);

        $atualizado = $rma->comAlteracoes([
            'status' => Status::Encaminhado,
            'encaminhadoEm' => Date::now(),
            'destinatarioType' => $destinatarioType,
            'destinatarioId' => $destinatarioId,
        ]);

        $rmaAtualizado = $this->repositorio->atualizar($atualizado);

        RmaEncaminhado::dispatch($ator, $rmaAtualizado);

        return $rmaAtualizado;
    }
}
