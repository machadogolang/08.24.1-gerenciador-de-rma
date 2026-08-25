<?php

namespace App\Rma\Infraestrutura\Migracao;

use Illuminate\Support\Facades\Storage;

/**
 * Objeto acumulador — contagem origem×destino por tabela, anomalias (valor fora do
 * domínio, data não-parseável, destinatário não resolvido, etc.) e conversões
 * assistidas (ex.: `prioridade='urgente'` → `Alta`, se ocorrer em dado real). Cada
 * importador recebe a mesma instância e acumula nela — `resumo()` monta o relatório
 * final ao fim do comando, impresso no console e salvo em `storage/app/migracao/`.
 */
final class RelatorioDeReconciliacao
{
    /** @var array<string, int> */
    private array $origem = [];

    /** @var array<string, int> */
    private array $destino = [];

    /** @var array<int, array{tabela: string, chave: mixed, motivo: string}> */
    private array $anomalias = [];

    /** @var array<int, array{tabela: string, chave: mixed, detalhe: string}> */
    private array $conversoesAssistidas = [];

    private bool $dryRun = false;

    /**
     * ARQ-002 (`INV-RMA-10`): marca o relatório como resultado de `--dry-run` — as
     * contagens abaixo passam a refletir o que SERIA gravado (tradução completa rodou,
     * mas cada importador desfez sua transação), nunca escrita real. `resumo()` rotula
     * a coluna de destino de acordo, para não ser confundida com uma migração real.
     */
    public function marcarComoDryRun(): void
    {
        $this->dryRun = true;
    }

    public function contarOrigem(string $tabela, int $n): void
    {
        $this->origem[$tabela] = ($this->origem[$tabela] ?? 0) + $n;
    }

    public function contarDestino(string $tabela, int $n): void
    {
        $this->destino[$tabela] = ($this->destino[$tabela] ?? 0) + $n;
    }

    public function registrarAnomalia(string $tabela, mixed $chaveOrigem, string $motivo): void
    {
        $this->anomalias[] = ['tabela' => $tabela, 'chave' => $chaveOrigem, 'motivo' => $motivo];
    }

    public function registrarConversaoAssistida(string $tabela, mixed $chaveOrigem, string $detalhe): void
    {
        $this->conversoesAssistidas[] = ['tabela' => $tabela, 'chave' => $chaveOrigem, 'detalhe' => $detalhe];
    }

    /** @return array<int, array{tabela: string, chave: mixed, motivo: string}> */
    public function anomalias(): array
    {
        return $this->anomalias;
    }

    /** @return array<int, array{tabela: string, chave: mixed, detalhe: string}> */
    public function conversoesAssistidas(): array
    {
        return $this->conversoesAssistidas;
    }

    /** @return array<string, int> */
    public function contagemOrigem(): array
    {
        return $this->origem;
    }

    /** @return array<string, int> */
    public function contagemDestino(): array
    {
        return $this->destino;
    }

    public function resumo(): string
    {
        $linhas = [];
        $linhas[] = '=== Relatório de reconciliação — migração V2→V3 ===';

        if ($this->dryRun) {
            $linhas[] = '';
            $linhas[] = '*** MODO --dry-run: NENHUMA ESCRITA FOI PERSISTIDA. ***';
            $linhas[] = "*** \"planejado\" abaixo é o que SERIA gravado — tradução completa rodou, cada ***";
            $linhas[] = '*** importador desfez sua própria transação ao final.                        ***';
        }

        $linhas[] = '';
        $linhas[] = $this->dryRun ? '-- Contagem origem × planejado --' : '-- Contagem origem × destino --';

        $tabelas = array_unique([...array_keys($this->origem), ...array_keys($this->destino)]);
        sort($tabelas);

        foreach ($tabelas as $tabela) {
            $origem = $this->origem[$tabela] ?? 0;
            $destino = $this->destino[$tabela] ?? 0;
            $diferenca = $origem - $destino;
            $linhas[] = sprintf(
                $this->dryRun ? '%-24s origem=%-6d planejado=%-6d diferença=%d' : '%-24s origem=%-6d destino=%-6d diferença=%d',
                $tabela,
                $origem,
                $destino,
                $diferenca
            );
        }

        $linhas[] = '';
        $linhas[] = sprintf('-- Anomalias (%d) --', count($this->anomalias));
        foreach ($this->anomalias as $anomalia) {
            $linhas[] = sprintf(
                '[%s] chave=%s — %s',
                $anomalia['tabela'],
                is_scalar($anomalia['chave']) ? (string) $anomalia['chave'] : json_encode($anomalia['chave']),
                $anomalia['motivo']
            );
        }

        $linhas[] = '';
        $linhas[] = sprintf('-- Conversões assistidas (%d) --', count($this->conversoesAssistidas));
        foreach ($this->conversoesAssistidas as $conversao) {
            $linhas[] = sprintf(
                '[%s] chave=%s — %s',
                $conversao['tabela'],
                is_scalar($conversao['chave']) ? (string) $conversao['chave'] : json_encode($conversao['chave']),
                $conversao['detalhe']
            );
        }

        return implode("\n", $linhas)."\n";
    }

    public function salvar(): string
    {
        $caminho = 'migracao/relatorio-'.now()->format('Y-m-d_His').'.txt';
        Storage::put($caminho, $this->resumo());

        return $caminho;
    }
}
