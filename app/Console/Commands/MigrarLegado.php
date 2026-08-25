<?php

namespace App\Console\Commands;

use App\Rma\Infraestrutura\Migracao\Importadores\ImportarAssistenciasTecnicas;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarClientes;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarFabricantes;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarFornecedores;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarLogsDeAcesso;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarModificacoesDeRma;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarRmas;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarUsuarios;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use Illuminate\Console\Command;

/**
 * `php artisan rma:migrar-legado` — orquestra os 8 importadores na ordem de dependência
 * de FK (`INV-RMA-06`/`INV-RMA-05` §14): Usuarios → Clientes → Fabricantes →
 * Fornecedores → AssistenciasTecnicas → Rmas → LogsDeAcesso → ModificacoesDeRma.
 * Transação por importador (não uma transação gigante) — uma tabela com erro não trava
 * as outras.
 */
final class MigrarLegado extends Command
{
    protected $signature = 'rma:migrar-legado
        {--somente= : Roda só um importador (usuarios|clientes|fabricantes|fornecedores|assistencias|rmas|logs|modificacoes)}
        {--dry-run : Roda toda a tradução/reconciliação sem gravar nada}
        {--forcar : Permite reprocessar RMAs já marcados como migrados (numero_legado já existente)}';

    protected $description = 'Migra o dado real do CellSystem RMA V2 (rma_legacy) para o schema V3, com relatório de reconciliação';

    public function handle(): int
    {
        $somente = $this->option('somente');
        $dryRun = (bool) $this->option('dry-run');
        $forcar = (bool) $this->option('forcar');

        if ($dryRun) {
            $this->warn('--dry-run: nenhuma escrita será feita, só tradução + contagem + anomalias.');
        }

        $relatorio = new RelatorioDeReconciliacao;

        if ($dryRun) {
            $relatorio->marcarComoDryRun();
        }

        $passos = [
            'usuarios' => fn () => (new ImportarUsuarios)->executar($relatorio, $dryRun),
            'clientes' => fn () => (new ImportarClientes)->executar($relatorio, $dryRun),
            'fabricantes' => fn () => (new ImportarFabricantes)->executar($relatorio, $dryRun),
            'fornecedores' => fn () => (new ImportarFornecedores)->executar($relatorio, $dryRun),
            'assistencias' => fn () => (new ImportarAssistenciasTecnicas)->executar($relatorio, $dryRun),
            'rmas' => fn () => (new ImportarRmas)->executar($relatorio, $dryRun, $forcar),
            'logs' => fn () => (new ImportarLogsDeAcesso)->executar($relatorio, $dryRun),
            'modificacoes' => function () use ($relatorio, $dryRun) {
                $importador = new ImportarModificacoesDeRma;

                if (! $importador->disponivel()) {
                    $this->warn('Fase 7 (modificacoes_de_rma) não está disponível neste schema — passo pulado, não é falha.');

                    return;
                }

                $importador->executar($relatorio, $dryRun);
            },
        ];

        foreach ($passos as $nome => $executar) {
            if ($somente !== null && $somente !== $nome) {
                continue;
            }

            $this->info("Importando: {$nome}...");
            $executar();
        }

        $resumo = $relatorio->resumo();
        $this->line('');
        $this->line($resumo);

        if (! $dryRun) {
            $caminho = $relatorio->salvar();
            $this->info("Relatório salvo em storage/app/{$caminho}");
        }

        return self::SUCCESS;
    }
}
