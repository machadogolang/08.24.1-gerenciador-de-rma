<?php

namespace App\Rma\Infraestrutura;

use App\Models\Rma as RmaEloquent;
use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\PainelDeStatus;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;

/**
 * Implementação Eloquent de `RepositorioDeRmas`. `App\Models\Rma` é uso interno desta
 * classe — nunca é devolvido nem recebido por fora daqui, o restante da aplicação só
 * conhece `App\Rma\Dominio\Rma`.
 *
 * `buscar()`: os 4 arquivos `pesquisar_{rma,nf,sn,descricao}.php` do legado eram
 * byte-idênticos (mesma função `pesquisar()`, LIKE genérico em 23 colunas) — a
 * distinção de "tipo" era só rótulo de UI. Nesta fase o schema ainda não tem os campos
 * de nota fiscal (`nfcompra`/`nfremessa`/`nfvenda` só entram na Fase 6, crédito/NF); até
 * lá, `CriterioDeBusca::porNotaFiscal()` busca em `os` (ordem de serviço), o campo mais
 * próximo de um identificador de documento já existente neste núcleo — decisão
 * registrada, revisitar quando os campos reais de NF forem introduzidos.
 */
final class RmasEmBanco implements RepositorioDeRmas
{
    public function criar(Rma $rma): Rma
    {
        $model = RmaEloquent::create($this->paraArray($rma));

        return $this->paraDominio($model);
    }

    public function atualizar(Rma $rma): Rma
    {
        $model = RmaEloquent::query()->findOrFail($rma->id);
        $model->update($this->paraArray($rma));

        return $this->paraDominio($model->fresh());
    }

    public function buscarPorId(int $id): ?Rma
    {
        $model = RmaEloquent::query()->find($id);

        return $model ? $this->paraDominio($model) : null;
    }

    /** @return Rma[] */
    public function buscar(CriterioDeBusca $criterio): array
    {
        $consulta = RmaEloquent::query();

        match ($criterio->tipo()) {
            'texto' => $consulta->where(function ($query) use ($criterio) {
                $valor = '%' . $criterio->valor() . '%';
                $query->where('descricao', 'like', $valor)
                    ->orWhere('defeito', 'like', $valor)
                    ->orWhere('observacao', 'like', $valor)
                    ->orWhere('modelo', 'like', $valor)
                    ->orWhere('origem', 'like', $valor)
                    ->orWhere('empresa', 'like', $valor);
            }),
            'serial' => $consulta->where('sn', 'like', '%' . $criterio->valor() . '%'),
            'nota_fiscal' => $consulta->where('os', 'like', '%' . $criterio->valor() . '%'),
        };

        return $consulta->orderByDesc('id')->get()
            ->map(fn (RmaEloquent $model) => $this->paraDominio($model))
            ->all();
    }

    /**
     * VIS-V1-001 — os 4 atalhos de navegação superior do TEMA V1 legado, cada um com
     * seu próprio filtro (`page/{entrada,encaminhados,aguardandocredito,concluidos}.php`).
     * "Entrada" reúne `status='entrada' OR status='recebido'` (mesmo critério do
     * legado); "Aguardando credito" filtra por `solucao`, não por `status`.
     *
     * @return Rma[]
     */
    public function listarPorPainel(PainelDeStatus $painel): array
    {
        $consulta = match ($painel) {
            PainelDeStatus::Entrada => RmaEloquent::query()
                ->whereIn('status', [Status::Entrada, Status::Recebido])
                ->orderByDesc('created_at'),
            PainelDeStatus::Encaminhados => RmaEloquent::query()
                ->where('status', Status::Encaminhado)
                ->orderByDesc('encaminhado_em'),
            PainelDeStatus::AguardandoCredito => RmaEloquent::query()
                ->where('solucao', Solucao::PendenteCredito)
                ->orderByDesc('created_at'),
            PainelDeStatus::Concluidos => RmaEloquent::query()
                ->where('status', Status::Concluido)
                ->orderByDesc('concluido_em'),
            PainelDeStatus::EntradaSomente => RmaEloquent::query()
                ->where('status', Status::Entrada)
                ->orderByDesc('created_at'),
            PainelDeStatus::RecebidoSomente => RmaEloquent::query()
                ->where('status', Status::Recebido)
                ->orderByDesc('recebido_em'),
        };

        return $consulta->get()
            ->map(fn (RmaEloquent $model) => $this->paraDominio($model))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function paraArray(Rma $rma): array
    {
        return [
            'descricao' => $rma->descricao,
            'fabricante_id' => $rma->fabricanteId,
            'fornecedor_id' => $rma->fornecedorId,
            'modelo' => $rma->modelo,
            'sn' => $rma->sn,
            'os' => $rma->os,
            'origem' => $rma->origem,
            'empresa' => $rma->empresa,
            'cliente_id' => $rma->clienteId,
            'defeito' => $rma->defeito,
            'observacao' => $rma->observacao,
            'status' => $rma->status,
            'recebido_em' => $rma->recebidoEm,
            'encaminhado_em' => $rma->encaminhadoEm,
            'concluido_em' => $rma->concluidoEm,
            'arquivado_em' => $rma->arquivadoEm,
            'protocolo' => $rma->protocolo,
            'solucao' => $rma->solucao,
            'snretorno' => $rma->snretorno,
            'destinatario_type' => $rma->destinatarioType,
            'destinatario_id' => $rma->destinatarioId,
            'prioridade' => $rma->prioridade,
            'marcarestoque' => $rma->marcarestoque,
            'nfcompra' => $rma->nfcompra,
            'nfcompra_emissao' => $rma->nfcompraEmissao,
            'nfcompra_chave' => $rma->nfcompraChave,
            'nfvenda' => $rma->nfvenda,
            'nfvenda_emissao' => $rma->nfvendaEmissao,
            'nfvenda_chave' => $rma->nfvendaChave,
            'pn' => $rma->pn,
            'snid' => $rma->snid,
            'lancadoretorno' => $rma->lancadoretorno,
            'valor' => $rma->valor,
            'credito_disponivel' => $rma->creditoDisponivel,
        ];
    }

    private function paraDominio(RmaEloquent $model): Rma
    {
        return new Rma(
            id: $model->id,
            descricao: $model->descricao,
            fabricanteId: $model->fabricante_id,
            fornecedorId: $model->fornecedor_id,
            modelo: $model->modelo,
            sn: $model->sn,
            os: $model->os,
            origem: $model->origem,
            empresa: $model->empresa,
            clienteId: $model->cliente_id,
            defeito: $model->defeito,
            observacao: $model->observacao,
            status: $model->status,
            recebidoEm: $model->recebido_em,
            encaminhadoEm: $model->encaminhado_em,
            concluidoEm: $model->concluido_em,
            arquivadoEm: $model->arquivado_em,
            protocolo: $model->protocolo,
            solucao: $model->solucao,
            snretorno: $model->snretorno,
            destinatarioType: $model->destinatario_type,
            destinatarioId: $model->destinatario_id,
            prioridade: $model->prioridade,
            marcarestoque: $model->marcarestoque,
            nfcompra: $model->nfcompra,
            nfcompraEmissao: $model->nfcompra_emissao,
            nfcompraChave: $model->nfcompra_chave,
            nfvenda: $model->nfvenda,
            nfvendaEmissao: $model->nfvenda_emissao,
            nfvendaChave: $model->nfvenda_chave,
            pn: $model->pn,
            snid: $model->snid,
            lancadoretorno: $model->lancadoretorno,
            valor: $model->valor !== null ? (float) $model->valor : null,
            createdAt: $model->created_at,
            creditoDisponivel: $model->credito_disponivel,
        );
    }
}
