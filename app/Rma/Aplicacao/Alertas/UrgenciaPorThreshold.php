<?php

namespace App\Rma\Aplicacao\Alertas;

use App\Models\Rma;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Status;
use Illuminate\Database\Eloquent\Collection;

/**
 * RN-12 (`LEG-RMA-029`) — threshold econômico de urgência (R$75). Origem confirmada
 * em `15.8.1/banco.php:777` (`right_urgente()`), fora de `metodo.php` mas tratada como
 * camada compartilhada por decisão registrada no `proposal.md`. Comparação de valor
 * estrita `>` — R$75,00 exato NÃO dispara.
 *
 * Desvio do `design.md`: o snippet literal usa
 * `$q3->whereColumn('created_at', '<', now())` — sintaxe inválida (`whereColumn()`
 * compara duas colunas entre si, não aceita um valor `Carbon` como segundo argumento;
 * erro de documentação análogo à correção já feita na coluna `valor`). Além disso, o
 * sentido "`<` now()" tornaria a condição um no-op quase sempre verdadeiro (frágil sob
 * teste: `created_at` truncado ao segundo pode empatar com `now()` em execuções
 * rápidas, produzindo falso negativo por corrida). A leitura que faz sentido de
 * negócio e bate com "prazo = created_at->addDays(30)" (a mesma citada na prosa do
 * `design.md`) é: o alerta é "ainda dá tempo de agir, mas o valor alto exige
 * prioridade" — ou seja, o prazo legal de 30 dias **ainda não estourou**. Implementado
 * como `where('created_at', '>', now()->subDays(30))`, equivalente a
 * `!Rma::prazoLegal()->isPast()`. Também resolve o cenário de verificação manual do
 * `tasks.md` (RMA recém-criado deve aparecer em `UrgenciaPorThreshold::listar()`).
 */
final class UrgenciaPorThreshold
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido, Status::Encaminhado])
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereIn('origem', [Origem::Cliente, Origem::Licitacao])
                        ->where('marcarestoque', false)
                        ->where('valor', '>', 75.00)
                        ->where('created_at', '>', now()->subDays(30));
                })->orWhere('prioridade', Prioridade::Alta);
            })
            ->get();
    }
}
