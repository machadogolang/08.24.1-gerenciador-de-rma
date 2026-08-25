<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\Eventos\RmaRevertido;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Status;

/**
 * LEG-RMA-015. Reversão livre no mesmo dia do encaminhamento para quem `podeGravar()`;
 * fora da janela de "mesmo dia", só `Papel::podeReverterAlemDoMesmoDia()`
 * (equivalente a `permissao==4` do legado, único nível SuperAdministrador). Fase 7:
 * dispara `RmaRevertido` ao final.
 */
final class ReverterRmaParaEntrada
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function reverter(User $ator, Rma $rma): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->status->podeReverterParaEntrada(), 422);

        $mesmoDia = $rma->encaminhadoEm?->isToday() ?? true;
        abort_unless($mesmoDia || $ator->papel->podeReverterAlemDoMesmoDia(), 403);

        $atualizado = $rma->comAlteracoes([
            'status' => Status::Entrada,
            'recebidoEm' => null,
            'encaminhadoEm' => null,
        ]);

        $rmaAtualizado = $this->repositorio->atualizar($atualizado);

        RmaRevertido::dispatch($ator, $rmaAtualizado);

        return $rmaAtualizado;
    }
}
