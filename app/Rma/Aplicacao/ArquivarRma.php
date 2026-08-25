<?php

namespace App\Rma\Aplicacao;

use App\Models\User;
use App\Rma\Dominio\Eventos\RmaArquivado;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\Date;

/**
 * LEG-RMA-014 — reproduz `15.8.1/banco.php::arquivar()` (TEMA V2, funcional). TEMA V1
 * (`14.6.1/post/arquivar.php`) tem `Fatal Error` incondicional (`new controle()`,
 * classe inexistente) — confirmado por leitura de código-fonte, não reproduzido (ver
 * `proposal.md`). Exige `Papel::podeGerenciarUsuarios()` — [INFERIDO]. Fase 7: dispara
 * `RmaArquivado` ao final.
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

        $atualizado = $rma->comAlteracoes([
            'status' => Status::Arquivado,
            'arquivadoEm' => Date::now(),
        ]);

        $rmaAtualizado = $this->repositorio->atualizar($atualizado);

        RmaArquivado::dispatch($ator, $rmaAtualizado);

        return $rmaAtualizado;
    }
}
