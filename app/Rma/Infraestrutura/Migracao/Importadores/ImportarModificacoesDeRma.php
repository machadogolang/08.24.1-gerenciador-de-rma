<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Models\ModificacaoDeRma;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\AcaoDeModificacao;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `modificacao` → `modificacoes_de_rma` (`INV-RMA-06` §13, Fase 7). **Só roda se a Fase
 * 7 já estiver implementada** — checagem defensiva de verdade (`Schema::hasTable()`),
 * não assumida implicitamente só porque a Fase 7 está commitada no repositório: se por
 * qualquer motivo a tabela não existir no schema corrente, este importador não falha,
 * só não processa nada (`disponivel()` deixa o comando avisar em vez de abortar).
 *
 * `rma_id` resolvido via `numero_legado` (mesma chave de `ImportarRmas`) — se o RMA de
 * origem não foi migrado (órfão), a linha de modificação é descartada e reportada.
 * Todas as linhas migradas recebem `AcaoDeModificacao::Edicao` (o legado nunca
 * discriminava o tipo de ação em `modificacao`, limitação conhecida e aceita só para o
 * histórico migrado — registros novos da V3 já gravam a ação granular real).
 */
final class ImportarModificacoesDeRma
{
    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
    ) {}

    public function disponivel(): bool
    {
        return Schema::hasTable('modificacoes_de_rma');
    }

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false): void
    {
        if (! $this->disponivel()) {
            return;
        }

        $origem = $this->conexao->modificacao();
        $total = 0;
        $processados = 0;

        DB::transaction(function () use ($origem, $relatorio, $dryRun, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;

                if ($dryRun) {
                    continue;
                }

                $rma = $linha->numero !== null
                    ? RmaEloquent::query()->where('numero_legado', $linha->numero)->first()
                    : null;

                if ($rma === null) {
                    $relatorio->registrarAnomalia('modificacao', $linha->id, "numero={$linha->numero} não corresponde a nenhum RMA migrado — modificação órfã, descartada");

                    continue;
                }

                $userId = null;

                if ($linha->email !== null && trim($linha->email) !== '') {
                    $user = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($linha->email))])->first();
                    $userId = $user?->id;
                }

                if ($userId === null) {
                    $relatorio->registrarAnomalia('modificacao', $linha->id, "email='{$linha->email}' não bate com nenhum usuário migrado — modificação órfã (user_id obrigatório), descartada");

                    continue;
                }

                $jaMigrado = ModificacaoDeRma::query()
                    ->where('rma_id', $rma->id)
                    ->where('user_id', $userId)
                    ->where('created_at', $linha->dta)
                    ->exists();

                if ($jaMigrado) {
                    continue;
                }

                $modificacao = new ModificacaoDeRma;
                $modificacao->forceFill([
                    'rma_id' => $rma->id,
                    'user_id' => $userId,
                    'acao' => AcaoDeModificacao::Edicao,
                    'ip' => $linha->ip,
                    'user_agent' => $linha->navegador,
                    'estado_apos' => [
                        'descricao' => $linha->descricao,
                        'app' => $linha->app,
                        'so' => $linha->so,
                        'fabricante' => $linha->fabricante,
                        'modelo' => $linha->modelo,
                        'sn' => $linha->sn,
                    ],
                    'created_at' => $linha->dta,
                    'updated_at' => $linha->dta,
                ]);
                $modificacao->save();

                $processados++;
            }
        });

        $relatorio->contarOrigem('modificacao', $total);
        $relatorio->contarDestino('modificacoes_de_rma', $processados);
    }
}
