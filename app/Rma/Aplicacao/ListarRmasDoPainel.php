<?php

namespace App\Rma\Aplicacao;

use App\Rma\Dominio\PainelDeStatus;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;

/**
 * VIS-V1-001 — os 4 atalhos de navegação superior do TEMA V1 (Entrada/Encaminhado/
 * Aguardando crédito/Concluído), cada um abrindo uma listagem filtrada própria.
 */
final class ListarRmasDoPainel
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
    ) {}

    /** @return Rma[] */
    public function listar(PainelDeStatus $painel): array
    {
        return $this->repositorio->listarPorPainel($painel);
    }
}
