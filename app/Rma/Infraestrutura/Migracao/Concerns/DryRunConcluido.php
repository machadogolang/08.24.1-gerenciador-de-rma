<?php

namespace App\Rma\Infraestrutura\Migracao\Concerns;

use RuntimeException;

/**
 * ARQ-002 (`INV-RMA-10`) — exceção marcadora usada só para forçar o rollback da
 * transação de um importador em `--dry-run`; nunca deve escapar de
 * `ExecutaComRollbackEmDryRun::executarComRollbackSeDryRun()`.
 */
final class DryRunConcluido extends RuntimeException {}
