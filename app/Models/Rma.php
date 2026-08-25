<?php

namespace App\Models;

use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use App\Rma\Dominio\StatusDeLancamento;
use Database\Factories\RmaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Uso interno de `App\Rma\Infraestrutura\RmasEmBanco` — não expor este model fora da
 * classe de infra (o objeto de domínio puro é `App\Rma\Dominio\Rma`).
 */
#[Fillable([
    'descricao',
    'fabricante_id',
    'fornecedor_id',
    'modelo',
    'sn',
    'os',
    'origem',
    'empresa',
    'cliente_id',
    'defeito',
    'observacao',
    'status',
    'recebido_em',
    'encaminhado_em',
    'concluido_em',
    'arquivado_em',
    'protocolo',
    'solucao',
    'snretorno',
    'destinatario_type',
    'destinatario_id',
    'prioridade',
    'marcarestoque',
    'nfcompra',
    'nfcompra_emissao',
    'nfcompra_chave',
    'nfvenda',
    'nfvenda_emissao',
    'nfvenda_chave',
    'lancadoretorno',
    'valor',
])]
class Rma extends Model
{
    /** @use HasFactory<RmaFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'solucao' => Solucao::class,
            'recebido_em' => 'datetime',
            'encaminhado_em' => 'datetime',
            'concluido_em' => 'datetime',
            'arquivado_em' => 'datetime',
            // `origem` deliberadamente SEM cast para `Origem::class` — ver decisão
            // registrada em `docs/produto/log-implementacao-v3.md` (Fase 5):
            // `comNormalizacaoDeGravacao()` (Fase 3) tem um ramo `default` que devolve
            // o valor original sem alterar, podendo persistir texto livre fora do
            // domínio fechado do enum; um cast Eloquent quebraria a hidratação
            // (`ValueError`) para esses registros. As 10 regras de alerta continuam
            // usando `Origem::Cliente` etc. literalmente nas queries — o query
            // builder do Laravel converte `BackedEnum` para `->value` na construção do
            // SQL independente de cast no model (`Illuminate\Support\enum_value()`).
            'prioridade' => Prioridade::class,
            'marcarestoque' => 'boolean',
            'nfcompra_emissao' => 'date',
            'nfvenda_emissao' => 'date',
            'lancadoretorno' => StatusDeLancamento::class,
            'valor' => 'decimal:2',
        ];
    }

    public function destinatario(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usada por `NaoVaiDarGarantia` (RN-02) — join real via FK, não comparação de
     * string (`fabricante_id` existe desde a Fase 2/3).
     */
    public function fabricante(): BelongsTo
    {
        return $this->belongsTo(Fabricante::class);
    }

    /**
     * Usada por `NaoVaiDarGarantia` (RN-02) — join real via FK.
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
