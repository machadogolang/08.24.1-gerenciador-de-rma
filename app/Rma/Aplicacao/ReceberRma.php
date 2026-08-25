<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\Eventos\RmaRecebido;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\Date;

/**
 * LEG-RMA-011. Fase 7: dispara `RmaRecebido` ao final.
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

        $atualizado = $rma->comAlteracoes([
            'status' => Status::Recebido,
            'recebidoEm' => Date::now(),
        ]);

        $rmaAtualizado = $this->repositorio->atualizar($atualizado);

        RmaRecebido::dispatch($ator, $rmaAtualizado);

        return $rmaAtualizado;
    }
}
