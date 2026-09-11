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
        // Garante que a linha do contador exista deterministicamente antes do lock,
        // evitando gap-locks de insercao concorrente simultanea em tabela vazia.
        ContadorDeRma::query()->insertOrIgnore([
            'company_id' => $companyId,
            'proximo_numero' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::transaction(function () use ($companyId): int {
            /** @var ContadorDeRma $contador */
            $contador = ContadorDeRma::query()
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->sole();

            $numero = (int) $contador->proximo_numero;
            $contador->increment('proximo_numero');

            return $numero;
        }, 5);
    }
}
