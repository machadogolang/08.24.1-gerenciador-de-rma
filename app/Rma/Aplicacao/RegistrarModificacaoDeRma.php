<?php

namespace App\Rma\Aplicacao;

use App\Models\ModificacaoDeRma;
use App\Models\Rma as RmaEloquent;
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
 * `LEG-RMA-044` - listener que assina os 8 eventos de domínio disparados pelos casos
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
        // EVO-SAAS-001 (S7/S8) - a modificacao herda o tenant do RMA pai, mesmo quando
        // o evento roda sem ContextoDeTenant (testes, jobs futuros). Nenhuma linha de
        // auditoria pode nascer com tenant nulo se o RMA ja tem tenant.
        $rma = RmaEloquent::query()
            ->withoutGlobalScopes()
            ->find($evento->rma->id);

        $modificacao = new ModificacaoDeRma([
            'rma_id' => $evento->rma->id,
            'user_id' => $evento->ator->id,
            'acao' => $this->acaoParaEvento($evento),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'estado_apos' => $evento->rma->paraSnapshot(),
        ]);

        if ($rma !== null) {
            $modificacao->tenant_id = $rma->tenant_id;
        }

        $modificacao->save();
    }

    private function acaoParaEvento(object $evento): AcaoDeModificacao
    {
        return self::ACAO_POR_EVENTO[$evento::class]
            ?? throw new RuntimeException('Evento sem AcaoDeModificacao mapeada: ' . $evento::class);
    }
}
