<?php

namespace App\Rma\Infraestrutura;

use App\Models\Rma as RmaEloquent;
use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;

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
        );
    }
}
