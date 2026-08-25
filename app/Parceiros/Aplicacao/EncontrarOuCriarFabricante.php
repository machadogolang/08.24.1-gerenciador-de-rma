<?php

namespace App\Parceiros\Aplicacao;

use App\Models\Fabricante;

/**
 * Generalização de `EncontrarOuCriarCliente` (Fase 2) — usada SÓ pelo migrador
 * (`INV-RMA-06` §17). O runtime de criação de RMA (Fase 3, `CriarRma`/`EditarRma`)
 * continua exigindo fabricante de uma lista já cadastrada; esta classe existe porque o
 * legado nunca teve FK e `bd.fabricante` histórico pode conter nomes que não batem
 * exatamente com nenhuma linha de `fabricantes` — sem essa resolução o RMA inteiro seria
 * perdido por causa de um nome não cadastrado formalmente.
 */
final class EncontrarOuCriarFabricante
{
    public function encontrarOuCriar(string $nomeDigitado): Fabricante
    {
        $nomeNormalizado = trim(preg_replace('/\s+/', ' ', $nomeDigitado));

        return Fabricante::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeNormalizado)])
            ->first()
            ?? Fabricante::create(['nome' => $nomeNormalizado]);
    }
}
