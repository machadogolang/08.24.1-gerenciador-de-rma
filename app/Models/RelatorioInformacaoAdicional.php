<?php

namespace App\Models;

use App\Compartilhado\Tenant\PertenceATenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * PAR14-REL-* - informacao adicional por relatorio (RPEC/RCRD/RMPE), tenant-aware.
 * Substitui `relatorio.informacaoadicional` do Legacy sem acoplar a informacao ao RMA.
 */
#[Fillable(['codigo', 'informacao_adicional'])]
class RelatorioInformacaoAdicional extends Model
{
    use PertenceATenant;

    protected $table = 'relatorio_informacoes_adicionais';
}
