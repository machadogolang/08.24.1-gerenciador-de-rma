<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use Illuminate\Support\Facades\DB;

/**
 * `log` → `tentativas_de_acesso` (`INV-RMA-06` §12, Fase 1 já implementada). `nome`/
 * `sistema_operacional`/`app` não são migrados (decisão registrada, não pendência — o
 * schema atual já basta para `LEG-RMA-043`, nenhuma tela/regra consulta esses 2 campos).
 * `retorno` bate 1:1 com `ResultadoDeAcesso`; qualquer valor fora do domínio vira
 * anomalia e a linha é gravada sem `resultado` resolvido — o log em si nunca é perdido
 * (idempotência aqui é "não reprocessar", não dedup por chave natural: `log` não tem
 * equivalente a `numero_legado`, então cada execução migra tudo que ainda não tem
 * `user_id`+`email_informado`+`created_at` idênticos já gravados).
 */
final class ImportarLogsDeAcesso
{
    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
    ) {}

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false): void
    {
        $origem = $this->conexao->log();
        $total = 0;
        $processados = 0;

        DB::transaction(function () use ($origem, $relatorio, $dryRun, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;

                if ($dryRun) {
                    continue;
                }

                $resultado = match ($linha->retorno) {
                    'permitido' => ResultadoDeAcesso::Permitido,
                    'negado' => ResultadoDeAcesso::Negado,
                    'bloqueado' => ResultadoDeAcesso::Bloqueado,
                    default => null,
                };

                if ($resultado === null) {
                    $relatorio->registrarAnomalia('log', $linha->id_log, "retorno='{$linha->retorno}' fora do domínio confirmado (permitido/negado/bloqueado)");

                    continue;
                }

                $jaMigrado = TentativaDeAcesso::query()
                    ->where('email_informado', $linha->email)
                    ->where('ip', $linha->ip)
                    ->where('created_at', $linha->data)
                    ->exists();

                if ($jaMigrado) {
                    continue;
                }

                $userId = null;

                if ($linha->email !== null && trim($linha->email) !== '') {
                    $user = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($linha->email))])->first();
                    $userId = $user?->id;
                }

                $tentativa = new TentativaDeAcesso;
                $tentativa->forceFill([
                    'user_id' => $userId,
                    'email_informado' => $linha->email,
                    'ip' => $linha->ip,
                    'user_agent' => $linha->navegador,
                    'resultado' => $resultado,
                    'created_at' => $linha->data,
                    'updated_at' => $linha->data,
                ]);
                $tentativa->save();

                $processados++;
            }
        });

        $relatorio->contarOrigem('log', $total);
        $relatorio->contarDestino('tentativas_de_acesso', $processados);
    }
}
