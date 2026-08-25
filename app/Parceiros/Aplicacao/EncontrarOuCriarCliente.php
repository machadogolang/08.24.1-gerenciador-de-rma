<?php

namespace App\Parceiros\Aplicacao;

use App\Models\Cliente;

/**
 * Único caso de uso real desta fase: corrige o achado do legado (`adicionar_cli()`,
 * `WHERE nome = ?` exato, sem trim/normalização de espaço, sem case-insensitive), que
 * duplicava cliente por variação de digitação. Comportamento percebido pelo usuário não
 * muda — ele digita um nome, o sistema acha ou cria — só para de duplicar.
 */
final class EncontrarOuCriarCliente
{
    public function encontrarOuCriar(string $nomeDigitado): Cliente
    {
        $nomeNormalizado = trim(preg_replace('/\s+/', ' ', $nomeDigitado));

        return Cliente::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeNormalizado)])
            ->first()
            ?? Cliente::create(['nome' => $nomeNormalizado]);
    }
}
