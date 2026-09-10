<?php

namespace App\Rma\Aplicacao\Relatorios;

use Illuminate\Database\Eloquent\Collection;

/**
 * PAR14-REL-RPEC/RCD/RMPE-001 - monta as linhas e os totais dos relatorios fiscais do
 * TEMA V1 exatamente como o Legacy `14.6.1/page/relatorios.php` os apresenta:
 * colunas na ordem historica, rotulos de origem abreviados e os totalizadores
 * (Valor Total, DATA DO RELATORIO, Quantidade Total, Quantidade sem valor).
 *
 * A regra de SELECAO continua nos servicos de relatorio; aqui so a projecao de tela.
 */
final class RelatorioFiscalV1
{
    public const CODIGOS = ['RPEC', 'RCRD', 'RMPE'];

    /**
     * @return array{linhas: array<int, array<string, string>>, totais: array<string, mixed>}
     */
    public function montar(Collection $registros, string $codigo): array
    {
        $registros->loadMissing(['fabricante', 'destinatario']);

        $linhas = [];
        $soma = 0.0;
        $quantidade = 0;
        $semValor = 0;

        foreach ($registros as $rma) {
            $valor = (float) $rma->valor;
            $soma += $valor;
            $quantidade++;

            if ($valor == 0.0) {
                $semValor++;
            }

            $fabricante = $rma->fabricante?->nome ?? '';
            $destinatario = $rma->destinatario?->nome ?? '';
            $nfCompra = (float) $rma->nfcompra > 0 ? (string) $rma->nfcompra : '';
            $nfVenda = (float) $rma->nfvenda > 0 ? (string) $rma->nfvenda : '';
            $valorFormatado = $valor > 0 ? number_format($valor, 2, '.', '') : '';

            $linhas[] = match ($codigo) {
                'RCRD' => [
                    'data' => $rma->concluido_em?->format('d/m/Y') ?? '',
                    'fabricante' => $fabricante,
                    'descricao' => (string) $rma->descricao,
                    'empresa' => (string) $rma->empresa,
                    'modelo' => (string) $rma->modelo,
                    'nfcompra' => $nfCompra,
                    'nfremessa' => (string) $rma->nf_remessa,
                    'valor' => $valorFormatado,
                    'protocolo' => (string) $rma->protocolo,
                    'destinatario' => $destinatario,
                    'os' => (string) $rma->os,
                ],
                'RPEC' => [
                    'data' => $rma->created_at?->format('d/m/Y') ?? '',
                    'fabricante' => $fabricante,
                    'descricao' => (string) $rma->descricao,
                    'empresa' => (string) $rma->empresa,
                    'modelo' => (string) $rma->modelo,
                    'nfcompra' => $nfCompra,
                    'nfvenda' => $nfVenda,
                    'origem' => origem_abreviada_v1((string) $rma->origem),
                    'destinatario' => $destinatario,
                    'os' => (string) $rma->os,
                ],
                default => [
                    'data' => $rma->encaminhado_em?->format('d/m/Y') ?? '',
                    'fabricante' => $fabricante,
                    'descricao' => (string) $rma->descricao,
                    'empresa' => (string) $rma->empresa,
                    'modelo' => (string) $rma->modelo,
                    'nfcompra' => $nfCompra,
                    'nfvenda' => $nfVenda,
                    'nfremessa' => (string) $rma->nf_remessa,
                    'valor' => $valorFormatado,
                    'destinatario' => $destinatario,
                    'os' => (string) $rma->os,
                ],
            };
        }

        return [
            'linhas' => $linhas,
            'totais' => [
                'valor' => number_format($soma, 2, '.', ''),
                'quantidade' => $quantidade,
                'sem_valor' => $semValor,
                'data' => now()->format('d/m/Y'),
            ],
        ];
    }
}
