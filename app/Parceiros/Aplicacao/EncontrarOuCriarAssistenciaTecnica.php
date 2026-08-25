<?php

namespace App\Parceiros\Aplicacao;

use App\Models\AssistenciaTecnica;

/**
 * Generalização de `EncontrarOuCriarCliente` (Fase 2) — usada SÓ pelo migrador
 * (`INV-RMA-06` §17). Ver `EncontrarOuCriarFabricante` para a justificativa completa.
 */
final class EncontrarOuCriarAssistenciaTecnica
{
    public function encontrarOuCriar(string $nomeDigitado): AssistenciaTecnica
    {
        $nomeNormalizado = trim(preg_replace('/\s+/', ' ', $nomeDigitado));

        return AssistenciaTecnica::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeNormalizado)])
            ->first()
            ?? AssistenciaTecnica::create(['nome' => $nomeNormalizado]);
    }
}
