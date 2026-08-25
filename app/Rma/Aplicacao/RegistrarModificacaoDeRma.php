<?php

namespace App\Rma\Aplicacao;

use App\Models\ModificacaoDeRma;
use App\Rma\Dominio\AcaoDeModificacao;
use App\Rma\Dominio\Eventos\RmaArquivado;
use App\Rma\Dominio\Eventos\RmaConcluido;
use App\Rma\Dominio\Eventos\RmaCriado;
use App\Rma\Dominio\Eventos\RmaEditado;
use App\Rma\Dominio\Eventos\RmaEncaminhado;
use App\Rma\Dominio\Eventos\RmaRecebido;
use App\Rma\Dominio\Eventos\RmaRevertido;
use App\Rma\Dominio\Eventos\SolucaoRegistrada;
use RuntimeException;

/**
 * `LEG-RMA-044` — listener que assina os 8 eventos de domínio disparados pelos casos
 * de uso de `App\Rma\Aplicacao` (Fases 3/4 + `ConcluirRma`), um único ponto de verdade
 * que substitui o `registra_modificacao()` chamado manualmente em cada arquivo do
 * legado. Nunca chamado diretamente por Controllers.
 */
final class RegistrarModificacaoDeRma
{
    /**
     * @var array<class-string, AcaoDeModificacao>
     */
    private const ACAO_POR_EVENTO = [
        RmaCriado::class => AcaoDeModificacao::Criacao,
        RmaEditado::class => AcaoDeModificacao::Edicao,
        RmaRecebido::class => AcaoDeModificacao::Receber,
        RmaEncaminhado::class => AcaoDeModificacao::Encaminhar,
        RmaConcluido::class => AcaoDeModificacao::Concluir,
        RmaArquivado::class => AcaoDeModificacao::Arquivar,
        RmaRevertido::class => AcaoDeModificacao::Reverter,
        SolucaoRegistrada::class => AcaoDeModificacao::RegistrarSolucao,
    ];

    public function handle(object $evento): void
    {
        ModificacaoDeRma::create([
            'rma_id' => $evento->rma->id,
            'user_id' => $evento->ator->id,
            'acao' => $this->acaoParaEvento($evento),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'estado_apos' => $evento->rma->paraSnapshot(),
        ]);
    }

    private function acaoParaEvento(object $evento): AcaoDeModificacao
    {
        return self::ACAO_POR_EVENTO[$evento::class]
            ?? throw new RuntimeException('Evento sem AcaoDeModificacao mapeada: ' . $evento::class);
    }
}
