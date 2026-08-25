<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Models\Cliente;
use App\Rma\Infraestrutura\Migracao\Concerns\AtualizaOuCriaPorNomeNormalizado;
use App\Rma\Infraestrutura\Migracao\Concerns\ConcatenaObservacaoSgvFr;
use App\Rma\Infraestrutura\Migracao\Concerns\ExecutaComRollbackEmDryRun;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;

/**
 * `cliente` → `clientes` (`INV-RMA-06` §16). Dedup por nome normalizado (mesma regra de
 * `EncontrarOuCriarCliente`, Fase 2) — sem coluna `id_legado`, o nome já é a chave de
 * negócio usada pelo próprio runtime da V3.
 */
final class ImportarClientes
{
    use AtualizaOuCriaPorNomeNormalizado;
    use ConcatenaObservacaoSgvFr;
    use ExecutaComRollbackEmDryRun;

    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
    ) {}

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false): void
    {
        $origem = $this->conexao->cliente();
        $total = 0;
        $processados = 0;

        $this->executarComRollbackSeDryRun($dryRun, function () use ($origem, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;

                $observacao = $this->concatenarObservacaoSgvFr($linha->observacaoSGV ?? null, $linha->observacaoFR ?? null);

                $this->atualizarOuCriarPorNome(
                    Cliente::class,
                    (string) $linha->nome,
                    [
                        'representante' => $linha->representante,
                        'cpf_cnpj' => $linha->cpfcnpj,
                        'email' => $linha->email,
                        'telefone' => $linha->fone,
                        'telefone2' => $linha->fone2,
                        'cep' => $linha->cep,
                        'logradouro' => $linha->logradouro,
                        'numero' => $linha->numero,
                        'complemento' => $linha->complemento,
                        'bairro' => $linha->bairro,
                        'cidade' => $linha->cidade,
                        'uf' => $linha->uf,
                        'observacao' => $observacao,
                    ],
                    $linha->data_de_cadastro
                );

                $processados++;
            }
        });

        $relatorio->contarOrigem('cliente', $total);
        $relatorio->contarDestino('clientes', $processados);
    }
}
