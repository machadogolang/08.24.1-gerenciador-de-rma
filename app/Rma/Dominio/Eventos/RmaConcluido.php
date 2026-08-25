<?php

namespace App\Rma\Dominio\Eventos;

use App\Models\User;
use App\Rma\Dominio\Rma;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado por `ConcluirRma`. Fase 7: `RegistrarModificacaoDeRma` (histórico) e
 * `EnviarNotificacaoDeConclusao` (e-mail) assinam este evento.
 *
 * **Ajuste de revisão (Fase 7):** ganhou a propriedade `ator` (não existia na Fase 4)
 * — necessária para `RegistrarModificacaoDeRma` gravar `user_id`, mesmo padrão dos
 * outros 7 eventos criados nesta fase. `ConcluirRma::concluir()` já recebe `User $ator`
 * como parâmetro, então o call site só passou a propagar um valor que já tinha em mãos
 * — não é uma mudança de comportamento observável, e `ConcluirRmaTest` (Fase 4) só
 * verifica `Event::assertDispatched(RmaConcluido::class)`, sem inspecionar argumentos.
 */
final class RmaConcluido
{
    use Dispatchable;

    public function __construct(
        public readonly User $ator,
        public readonly Rma $rma,
    ) {}
}
