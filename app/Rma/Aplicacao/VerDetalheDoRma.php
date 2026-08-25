<?php

namespace App\Rma\Aplicacao;

use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;

/**
 * LEG-RMA-009.
 */
final class VerDetalheDoRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    public function porId(int $id): ?Rma
    {
        return $this->repositorio->buscarPorId($id);
    }
}
