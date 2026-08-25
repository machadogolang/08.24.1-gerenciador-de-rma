<?php

namespace App\Rma\Infraestrutura\Migracao\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Mesma regra de dedup de `EncontrarOuCriarCliente` (Fase 2, trim + case-insensitive) —
 * mas para os importadores, que também precisam ATUALIZAR os dados do parceiro já
 * existente (idempotência real: rodar 2x não duplica, e a 2ª rodada reflete o dado mais
 * recente do legado). `Model::updateOrCreate()` puro não serve aqui porque casa por
 * igualdade EXATA de string — duas grafias do mesmo nome (`'seagate'`/`'Seagate'`)
 * criariam 2 linhas em vez de 1.
 */
trait AtualizaOuCriaPorNomeNormalizado
{
    /**
     * `$createdAt` é aplicado só na criação (valor histórico do legado, nunca
     * `now()`) — `created_at` não está na lista `#[Fillable]` dos models de parceiro
     * (mass assignment o ignoraria em silêncio), então é gravado via propriedade
     * direta depois do `create()`, e nunca tocado num update de linha já existente
     * (idempotência não deve reescrever a data de cadastro original).
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  array<string, mixed>  $atributos
     * @return TModel
     */
    private function atualizarOuCriarPorNome(string $model, string $nomeDigitado, array $atributos, mixed $createdAt = null): Model
    {
        $nomeNormalizado = trim(preg_replace('/\s+/', ' ', $nomeDigitado));

        $existente = $model::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeNormalizado)])
            ->first();

        if ($existente !== null) {
            $existente->update($atributos);

            return $existente;
        }

        $novo = $model::create(['nome' => $nomeNormalizado, ...$atributos]);

        if ($createdAt !== null) {
            $novo->created_at = $createdAt;
            $novo->save();
        }

        return $novo;
    }
}
