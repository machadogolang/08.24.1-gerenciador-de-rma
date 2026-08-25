<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\AssistenciaTecnica;
use App\Models\Rma;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * CP19 — `15.8.1/banco.php:803` (`right_portoalegre()`, versão ativa — a query
 * anterior comentada no arquivo fonte não é usada). RMAs em Entrada/Recebido cujo
 * fornecedor, fabricante OU destinatário (quando é assistência técnica) fica em
 * Porto Alegre. O legado casa por NOME de texto solto contra 3 tabelas; o V3 tem FK
 * reais (`fornecedor_id`/`fabricante_id`/`destinatario_type`+`id`), então a mesma
 * regra vira `whereHas`/`whereHasMorph` real em vez de `LEFT JOIN` por nome.
 */
final class TransportePortoAlegre
{
    private const CIDADE = 'PORTO ALEGRE';

    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where(function ($query) {
                $query->whereHas('fornecedor', fn ($q) => $q->where('cidade', self::CIDADE))
                    ->orWhereHas('fabricante', fn ($q) => $q->where('cidade', self::CIDADE))
                    ->orWhereHasMorph('destinatario', [AssistenciaTecnica::class], fn ($q) => $q->where('cidade', self::CIDADE));
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
