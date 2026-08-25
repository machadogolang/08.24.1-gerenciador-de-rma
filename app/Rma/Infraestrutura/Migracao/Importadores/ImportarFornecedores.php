<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Models\Fornecedor;
use App\Rma\Infraestrutura\Migracao\Concerns\AtualizaOuCriaPorNomeNormalizado;
use App\Rma\Infraestrutura\Migracao\Concerns\ConcatenaObservacaoSgvFr;
use App\Rma\Infraestrutura\Migracao\Concerns\ExecutaComRollbackEmDryRun;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;

/**
 * `fornecedor` → `fornecedores` (`INV-RMA-06` §16). Dedup por nome normalizado.
 * `fornecedor` usa `observacaoSGV`/`observacaoFR` (mesmo padrão de `cliente`, diferente
 * de `fabricante`/`assistencia_tecnica`).
 */
final class ImportarFornecedores
{
    use AtualizaOuCriaPorNomeNormalizado;
    use ConcatenaObservacaoSgvFr;
    use ExecutaComRollbackEmDryRun;

    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
    ) {}

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false): void
    {
        $origem = $this->conexao->fornecedor();
        $total = 0;
        $processados = 0;

        $this->executarComRollbackSeDryRun($dryRun, function () use ($origem, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;

                $observacao = $this->concatenarObservacaoSgvFr($linha->observacaoSGV ?? null, $linha->observacaoFR ?? null);

                $this->atualizarOuCriarPorNome(
                    Fornecedor::class,
                    (string) $linha->nome,
                    [
                        'representante' => $linha->representante,
                        'cpf_cnpj' => $linha->cpfcnpj,
                        'email' => $linha->email,
                        'email_secundario' => $linha->email2,
                        'telefone' => $linha->fone,
                        'telefone2' => $linha->fone2,
                        'cep' => $linha->cep,
                        'logradouro' => $linha->logradouro,
                        'numero' => $linha->numero,
                        'complemento' => $linha->complemento,
                        'bairro' => $linha->bairro,
                        'cidade' => $linha->cidade,
                        'uf' => $linha->uf,
                        'www' => $linha->www,
                        'frete' => $linha->frete,
                        'cfop' => $linha->cfop,
                        'observacao' => $observacao,
                        'politica_de_garantia' => $linha->politicadegarantia,
                    ],
                    $linha->data_de_cadastro
                );

                $processados++;
            }
        });

        $relatorio->contarOrigem('fornecedor', $total);
        $relatorio->contarDestino('fornecedores', $processados);
    }
}
