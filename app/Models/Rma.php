<?php

namespace App\Models;

use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Database\Factories\RmaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        ];
    }

    public function destinatario(): MorphTo
    {
        return $this->morphTo();
    }
}
