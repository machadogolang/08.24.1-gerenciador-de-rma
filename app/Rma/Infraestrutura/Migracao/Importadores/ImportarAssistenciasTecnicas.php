<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Models\AssistenciaTecnica;
use App\Rma\Infraestrutura\Migracao\Concerns\AtualizaOuCriaPorNomeNormalizado;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use Illuminate\Support\Facades\DB;

/**
 * `assistencia_tecnica` → `assistencias_tecnicas` (`INV-RMA-06` §16). Dedup por nome
 * normalizado. **Não confundir com `assistencias`** (tabela órfã diferente, §15 — nunca
 * migrada, `LEG-RMA-035`).
 */
final class ImportarAssistenciasTecnicas
{
    use AtualizaOuCriaPorNomeNormalizado;

    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
    ) {}

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false): void
    {
        $origem = $this->conexao->assistenciaTecnica();
        $total = 0;
        $processados = 0;

        DB::transaction(function () use ($origem, $dryRun, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;

                if ($dryRun) {
                    continue;
                }

                $this->atualizarOuCriarPorNome(
                    AssistenciaTecnica::class,
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
                        'observacao' => $linha->observacao,
                        'politica_de_garantia' => $linha->politicadegarantia,
                    ],
                    $linha->data_de_cadastro
                );

                $processados++;
            }
        });

        $relatorio->contarOrigem('assistencia_tecnica', $total);
        $relatorio->contarDestino('assistencias_tecnicas', $processados);
    }
}
