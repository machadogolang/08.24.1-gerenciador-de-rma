<?php

namespace App\Rma\Infraestrutura\Migracao;

use Carbon\Carbon;

/**
 * Resultado de `ParserDeDataLegado::parse()` — `ok=false` nunca é uma exceção, é um
 * valor normal que o importador consulta antes de decidir gravar `NULL` + anomalia.
 */
final class ResultadoDeParseDeData
{
    public function __construct(
        public readonly ?Carbon $data,
        public readonly bool $ok,
        public readonly ?string $bruto,
    ) {}
}
