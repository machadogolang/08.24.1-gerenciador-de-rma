<?php

namespace App\Rma\Infraestrutura;

use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\ReservarNumeroDeRma;
use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\PainelDeStatus;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Support\Facades\DB;

/**
 * Implementação Eloquent de `RepositorioDeRmas`. `App\Models\Rma` é uso interno desta
 * classe - nunca é devolvido nem recebido por fora daqui, o restante da aplicação só
 * conhece `App\Rma\Dominio\Rma`.
 *
 * `buscar()`: os 4 arquivos `pesquisar_{rma,nf,sn,descricao}.php` do 15.8.1 eram
 * byte-idênticos (mesma função `pesquisar()`, LIKE genérico) - a distinção de "tipo"
 * era só rótulo de UI; no 14.6.1, porém, o campo `NF` do painel Localizar filtrava
 * explicitamente `nfcompra`/`nfvenda`/`nfremessa` (`page/localizar.php:9`), e `os`
 * filtrava só a coluna `os`. ARQ-004 (fechado em 2026-09-09): `nota_fiscal` busca os
 * campos fiscais reais (primeira classe + históricos preservados pelo migrador), e
 * `os` virou critério próprio - nada mais busca `os` no lugar de NF.
 */
final class RmasEmBanco implements RepositorioDeRmas
{
    public function criar(Rma $rma): Rma
    {
        $model = DB::transaction(function () use ($rma): RmaEloquent {
            $dados = $this->paraArray($rma);

            // EVO-SAAS-001 (S10) - número operacional por empresa reservado em
            // transação (nunca MAX+1). O observer preenche tenant_id do contexto.
            $contexto = app(ContextoDeTenant::class);
            if ($contexto->temEmpresa()) {
                $dados['numero_da_empresa'] = app(ReservarNumeroDeRma::class)
                    ->reservar($contexto->empresaId());
            }

            return RmaEloquent::create($dados);
        });

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
                    ->orWhere('empresa', 'like', $valor)
                    // PAR-RMA-003 (parcial, 2026-09-09): campos diretos que o legado
                    // já pesquisava no modo TUDO (14.6.1 `page/localizar.php:15`).
                    // Nomes via relacionamento (fabricante/cliente/destinatario) ficam
                    // para a tarefa integral - `[GAP]` mantido no checklist.
                    ->orWhere('sn', 'like', $valor)
                    ->orWhere('pn', 'like', $valor)
                    ->orWhere('snid', 'like', $valor)
                    ->orWhere('os', 'like', $valor)
                    ->orWhere('protocolo', 'like', $valor)
                    ->orWhere('rastreio_ida', 'like', $valor)
                    ->orWhere('rastreio_retorno', 'like', $valor)
                    ->orWhere('nfcompra', 'like', $valor)
                    ->orWhere('nfvenda', 'like', $valor)
                    ->orWhere('nf_remessa', 'like', $valor)
                    ->orWhere('nf_retorno_numero', 'like', $valor)
                    ->orWhere('numero_legado', 'like', $valor)
                    ->orWhere('destinatario_nome_legado', 'like', $valor)
                    ->orWhere('cliente_email_legado', 'like', $valor)
                    // PAR-RMA-003 (integral, P6): contrapartes reais por FK/morph.
                    ->orWhereHas('fabricante', fn ($fabricante) => $fabricante->where('nome', 'like', $valor))
                    ->orWhereHas('fornecedor', fn ($fornecedor) => $fornecedor->where('nome', 'like', $valor))
                    ->orWhereHas('cliente', fn ($cliente) => $cliente->where('nome', 'like', $valor))
                    ->orWhereHasMorph(
                        'destinatario',
                        [\App\Models\Fabricante::class, \App\Models\Fornecedor::class, \App\Models\AssistenciaTecnica::class],
                        fn ($destinatario) => $destinatario->where('nome', 'like', $valor),
                    );
            }),
            'numero' => $consulta->where('numero_legado', 'like', '%' . $criterio->valor() . '%'),
            'serial' => $consulta->where('sn', 'like', '%' . $criterio->valor() . '%'),
            'nota_fiscal' => $consulta->where(function ($query) use ($criterio) {
                $valor = '%' . $criterio->valor() . '%';
                $query->where('nfcompra', 'like', $valor)
                    ->orWhere('nfvenda', 'like', $valor)
                    ->orWhere('nf_remessa', 'like', $valor)
                    ->orWhere('nf_retorno_numero', 'like', $valor)
                    ->orWhere('nf_devolucao_de_venda', 'like', $valor)
                    ->orWhere('nf_entrada_cliente_legado', 'like', $valor)
                    ->orWhere('nf_retorno_cliente_legado', 'like', $valor);
            }),
            'os' => $consulta->where('os', 'like', '%' . $criterio->valor() . '%'),
        };

        // CP7 (fase 2 V1) - filtro aditivo independente do texto (`solucao` do
        // painel Localizar do legado, ver `CriterioDeBusca::solucao()`).
        if ($criterio->solucao() !== null) {
            $consulta->where('solucao', $criterio->solucao());
        }

        return $consulta->orderByDesc('id')->get()
            ->map(fn (RmaEloquent $model) => $this->paraDominio($model))
            ->all();
    }

    /**
     * VIS-V1-001 - os 4 atalhos de navegação superior do TEMA V1 legado, cada um com
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
