<?php

namespace App\Models;

use App\Rma\Dominio\AcaoDeModificacao;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `LEG-RMA-044` — equivalente ao `modificacao` do legado, com FK real para
 * `rmas`/`users` (o legado grava `numero`/`email` sem constraint) e ação nomeada
 * (`AcaoDeModificacao`) em vez de só um retrato do estado final. Só é criado por
 * `App\Rma\Aplicacao\RegistrarModificacaoDeRma` (listener) — nunca diretamente por
 * controllers.
 */
#[Fillable(['rma_id', 'user_id', 'acao', 'ip', 'user_agent', 'estado_apos'])]
class ModificacaoDeRma extends Model
{
    protected $table = 'modificacoes_de_rma';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'acao' => AcaoDeModificacao::class,
            'estado_apos' => 'array',
        ];
    }

    public function rma(): BelongsTo
    {
        return $this->belongsTo(Rma::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
