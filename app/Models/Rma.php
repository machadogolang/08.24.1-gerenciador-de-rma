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
    'numero_legado',
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
    'credito_disponivel',
    // Fase 9 — colunas históricas de preservação (`INV-RMA-06` §1.2, §5, §7, §10),
    // preenchidas só pelo migrador, sem regra de negócio dona.
    'nf_devolucao_de_venda',
    'nf_entrada_cliente_legado',
    'nf_retorno_cliente_legado',
    'nf_remessa',
    'nf_remessa_emissao',
    'nf_remessa_chave',
    'nf_retorno_numero',
    'nf_retorno_emissao',
    'nf_retorno_chave',
    'pn',
    'snid',
    'rastreio_ida',
    'rastreio_retorno',
    'cliente_email_legado',
    'destinatario_email_legado',
    'destinatario_fone_legado',
    'descricao_final_legado',
    'solucao_legado_bruto',
    'destinatario_nome_legado',
    'operador_email_legado',
    'operador_id',
    'created_at',
    'updated_at',
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
            'credito_disponivel' => 'boolean',
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
