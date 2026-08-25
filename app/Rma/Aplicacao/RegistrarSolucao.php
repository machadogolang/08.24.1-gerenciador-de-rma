<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\Eventos\SolucaoRegistrada;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;

/**
 * LEG-RMA-017. Atualiza `solucao` independente de transição de status (o legado
 * permite editar via `salvar_rma.php` a qualquer momento) e reaplica
 * `comSnretornoAutoPreenchido()` (RN-15). Fase 7: dispara `SolucaoRegistrada` ao
 * final.
 */
final class RegistrarSolucao
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function registrar(User $ator, Rma $rma, Solucao $solucao): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);

        $comSolucao = $rma->comAlteracoes(['solucao' => $solucao]);

        $rmaAtualizado = $this->repositorio->atualizar($comSolucao->comSnretornoAutoPreenchido());

        SolucaoRegistrada::dispatch($ator, $rmaAtualizado);

        return $rmaAtualizado;
    }
}
