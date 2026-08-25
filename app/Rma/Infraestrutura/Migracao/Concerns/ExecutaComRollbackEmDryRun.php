<?php

namespace App\Rma\Infraestrutura\Migracao\Concerns;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * ARQ-002 (`INV-RMA-10`) — antes, cada importador pulava a tradução inteira em
 * `--dry-run` (`if ($dryRun) { continue; }` antes de traduzir a linha), então dry-run
 * nunca detectava anomalia nenhuma e sempre reportava zero linhas processadas,
 * escondendo justamente os problemas que o dry-run deveria revelar.
 *
 * Este trait roda o callback (tradução + gravação) sempre da mesma forma, dry-run ou
 * não — mesma cobertura de anomalias e mesma contagem real do que seria gravado — mas
 * embrulha tudo numa transação que só é confirmada quando `$dryRun` é falso. Em
 * dry-run, uma exceção marcadora força o rollback ao final, desfazendo inclusive
 * efeitos colaterais indiretos (ex.: `EncontrarOuCriarFabricante` criando um fabricante
 * novo durante a tradução de um RMA).
 */
trait ExecutaComRollbackEmDryRun
{
    private function executarComRollbackSeDryRun(bool $dryRun, Closure $callback): void
    {
        try {
            DB::transaction(function () use ($callback, $dryRun) {
                $callback();

                if ($dryRun) {
                    throw new DryRunConcluido('dry-run: desfazendo transação de propósito, sem efeito externo.');
                }
            });
        } catch (DryRunConcluido) {
            // Esperado — ver docblock da trait.
        }
    }
}
