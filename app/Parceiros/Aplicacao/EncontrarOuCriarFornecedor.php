<?php

namespace App\Parceiros\Aplicacao;

use App\Models\Fornecedor;

/**
 * Generalização de `EncontrarOuCriarCliente` (Fase 2) — usada SÓ pelo migrador
 * (`INV-RMA-06` §17). Ver `EncontrarOuCriarFabricante` para a justificativa completa.
 */
final class EncontrarOuCriarFornecedor
{
    public function encontrarOuCriar(string $nomeDigitado): Fornecedor
    {
        $nomeNormalizado = trim(preg_replace('/\s+/', ' ', $nomeDigitado));

        return Fornecedor::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeNormalizado)])
            ->first()
            ?? Fornecedor::create(['nome' => $nomeNormalizado]);
    }
}
