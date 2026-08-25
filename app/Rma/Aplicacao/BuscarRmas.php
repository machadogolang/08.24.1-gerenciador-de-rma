<?php

namespace App\Rma\Aplicacao;

use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;

/**
 * LEG-RMA-008 — unifica `pesquisar_{rma,nf,sn,descricao}.php` (4 arquivos idênticos no
 * legado) numa única busca parametrizada por `CriterioDeBusca`.
 */
final class BuscarRmas
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    /** @return Rma[] */
    public function buscar(CriterioDeBusca $criterio): array
    {
        return $this->repositorio->buscar($criterio);
    }
}
