<?php

namespace App\Models;

use Database\Factories\RmaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
])]
class Rma extends Model
{
    /** @use HasFactory<RmaFactory> */
    use HasFactory;
}
