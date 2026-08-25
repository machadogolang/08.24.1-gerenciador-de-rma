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

        $comSolucao = $rma->comAlteracoes([
            'status' => Status::Concluido,
            'concluidoEm' => Date::now(),
            'solucao' => $solucao,
        ]);

        $atualizado = $this->repositorio->atualizar($comSolucao->comSnretornoAutoPreenchido());

        RmaConcluido::dispatch($ator, $atualizado);

        return $atualizado;
    }
}
