<?php

namespace App\Console\Commands;

use App\Rma\Aplicacao\ReservarNumeroDeRma;
use Illuminate\Console\Command;

/**
 * EVO-SAAS-001 (S10.4) - comando auxiliar para teste concorrente de reserva de numero
 * por processo de sistema operacional independente.
 */
final class ReservarNumerosConcorrente extends Command
{
    protected $signature = 'rma:reservar-numeros {company_id : ID da empresa} {quantidade=10 : Quantidade de numeros a reservar}';

    protected $description = 'Reserva uma sequencia de numeros de RMA para uma empresa e imprime como JSON';

    public function handle(ReservarNumeroDeRma $reservar): int
    {
        $companyId = (int) $this->argument('company_id');
        $quantidade = max(1, (int) $this->argument('quantidade'));

        $numeros = [];
        for ($i = 0; $i < $quantidade; $i++) {
            $numeros[] = $reservar->reservar($companyId);
        }

        $this->line(json_encode($numeros, JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
