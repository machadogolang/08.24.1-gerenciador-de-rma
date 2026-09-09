<?php

namespace App\Rma\Aplicacao;

use App\Models\ContadorDeRma;
use Illuminate\Support\Facades\DB;

/**
 * EVO-SAAS-001 (S10) - reserva o próximo número operacional de RMA da empresa dentro
 * de transação com `lockForUpdate`; nunca usa `MAX(numero)+1`. Cada chamada devolve um
 * número distinto mesmo sob concorrência (a linha do contador fica travada até o
 * commit da transação).
 */
final class ReservarNumeroDeRma
{
    public function reservar(int $companyId): int
    {
        return DB::transaction(function () use ($companyId): int {
            $contador = ContadorDeRma::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    ['company_id' => $companyId],
                    ['proximo_numero' => 1],
                );

            $numero = (int) $contador->proximo_numero;
            $contador->increment('proximo_numero');

            return $numero;
        });
    }
}
