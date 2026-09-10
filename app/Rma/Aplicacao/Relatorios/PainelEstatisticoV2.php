<?php

namespace App\Rma\Aplicacao\Relatorios;

use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma as RmaEloquent;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Status;
use Illuminate\Support\Carbon;

/**
 * PAR15-REL-001..007 - painel estatistico de Relatorios do TEMA V2, equivalente a
 * `15.8.1/page/relatorios.php`.
 *
 * Todas as contagens sao feitas por SQL agregado (nunca carregando RMAs para contar
 * em PHP) e herdam o escopo de tenant do modelo `Rma`.
 *
 * Serie historica: o Legacy fixa 2014..2016 no codigo; aqui a serie e dirigida
 * pelos anos realmente presentes nos dados (mais recentes primeiro), o que preserva
 * o contrato de tela sem inventar anos vazios.
 */
final class PainelEstatisticoV2
{
    private const MAX_ANOS = 6;

    /**
     * @return array<string, mixed>
     */
    public function montar(): array
    {
        return [
            'situacao' => $this->situacao(),
            'resolucao' => $this->resolucao(),
            'origem' => $this->origem(),
            'fornecedores' => $this->fornecedores(),
            'notas' => $this->notas(),
            'sistema' => $this->sistema(),
            'anos' => $this->series(),
        ];
    }

    /**
     * @return array<int, array{rotulo: string, valor: int}>
     */
    private function situacao(): array
    {
        $rotulos = [
            Status::Entrada->name => 'Entrada',
            Status::Recebido->name => 'Recebido',
            Status::Encaminhado->name => 'Encaminhado',
            Status::Concluido->name => 'Concluido',
        ];

        $contagens = RmaEloquent::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $saida = [];
        foreach ($rotulos as $case => $rotulo) {
            $saida[] = ['rotulo' => $rotulo, 'valor' => (int) ($contagens[$case] ?? 0)];
        }

        return $saida;
    }

    /**
     * @return array<int, array{rotulo: string, valor: int}>
     */
    private function resolucao(): array
    {
        $mapa = [
            'REPARO' => 'Reparo',
            'TROCA DO PRODUTO' => 'Troca do produto',
            'TROCA DE PECA INTERNA' => 'Troca de peca',
            'DEVOLUCAO DO PRODUTO' => 'Devolucao',
            'REEMBOLSO DO DINHEIRO' => 'Reembolso',
            'REPARO PELO RMA' => 'Reparo pelo RMA',
            'TESTADO TUDO OK' => 'Testado Tudo OK',
            'ORCAMENTO PAGO' => 'Orcamento pago',
            'DESCRITO NA OBSERVACAO' => 'Descrito na observacao',
            'SEM GARANTIA' => 'Sem garantia',
            'PROCON' => 'Procon',
            'PENDENTE CREDITO' => 'Pendente credito',
            'GERADO CREDITO' => 'Gerado credito',
        ];

        $contagens = RmaEloquent::query()
            ->selectRaw('solucao, COUNT(*) as total')
            ->whereNotNull('solucao')
            ->groupBy('solucao')
            ->pluck('total', 'solucao');

        $saida = [];
        foreach ($mapa as $valor => $rotulo) {
            $saida[] = ['rotulo' => $rotulo, 'valor' => (int) ($contagens[$valor] ?? 0)];
        }

        return $saida;
    }

    /**
     * @return array<int, array{rotulo: string, valor: int}>
     */
    private function origem(): array
    {
        $contagens = RmaEloquent::query()
            ->selectRaw('origem, COUNT(*) as total')
            ->whereNotNull('origem')
            ->groupBy('origem')
            ->pluck('total', 'origem');

        $saida = [];
        foreach ([Origem::Licitacao, Origem::Cliente, Origem::MercadoLivre, Origem::Ac, Origem::Leilao, Origem::Loja, Origem::Casa, Origem::Unknown] as $origem) {
            $saida[] = ['rotulo' => $origem->value, 'valor' => (int) ($contagens[$origem->value] ?? 0)];
        }

        return $saida;
    }

    /**
     * @return array<int, array{rotulo: string, valor: int}>
     */
    private function fornecedores(): array
    {
        return [[
            'rotulo' => 'Receita Federal (Leilao)',
            'valor' => RmaEloquent::query()
                ->whereHas('fornecedor', fn ($query) => $query->where('nome', 'Leilão'))
                ->count(),
        ]];
    }

    /**
     * @return array<int, array{rotulo: string, valor: int}>
     */
    private function notas(): array
    {
        return [
            ['rotulo' => 'Sem NF de Compra', 'valor' => RmaEloquent::query()->where('nfcompra', '<=', 0)->count()],
            ['rotulo' => 'Sem NF de Venda', 'valor' => RmaEloquent::query()->where('nfvenda', '<=', 0)->count()],
            ['rotulo' => 'Sem nenhuma nota', 'valor' => RmaEloquent::query()->where('nfcompra', '<=', 0)->where('nfvenda', '<=', 0)->count()],
        ];
    }

    /**
     * @return array<int, array{rotulo: string, valor: int}>
     */
    private function sistema(): array
    {
        return [
            ['rotulo' => 'Quantidade de RMA', 'valor' => RmaEloquent::query()->count()],
            ['rotulo' => 'Quantidade de Clientes', 'valor' => Cliente::query()->count()],
            ['rotulo' => 'Quantidade de Fornecedores', 'valor' => Fornecedor::query()->count()],
            ['rotulo' => 'Quantidade de Fabricantes', 'valor' => Fabricante::query()->count()],
            ['rotulo' => 'Quantidade de Assistencias tecnicas', 'valor' => AssistenciaTecnica::query()->count()],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function series(): array
    {
        $anos = RmaEloquent::query()
            ->selectRaw('YEAR(created_at) as ano')
            ->whereNotNull('created_at')
            ->groupBy('ano')
            ->orderByDesc('ano')
            ->limit(self::MAX_ANOS)
            ->pluck('ano')
            ->map(fn ($ano) => (int) $ano)
            ->all();

        $saida = [];
        foreach ($anos as $ano) {
            $saida[] = [
                'ano' => $ano,
                'entrada' => $this->porMes('created_at', $ano),
                'encaminhado' => $this->porMes('encaminhado_em', $ano),
                'concluido' => $this->porMes('concluido_em', $ano),
                'totais' => $this->totaisDoAno($ano),
            ];
        }

        return $saida;
    }

    /**
     * @return array<int, int>
     */
    private function porMes(string $coluna, int $ano): array
    {
        $contagens = RmaEloquent::query()
            ->selectRaw("MONTH({$coluna}) as mes, COUNT(*) as total")
            ->whereYear($coluna, $ano)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $meses = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $meses[$mes] = (int) ($contagens[$mes] ?? 0);
        }

        return $meses;
    }

    /**
     * @return array<string, int>
     */
    private function totaisDoAno(int $ano): array
    {
        $inicio = Carbon::create($ano, 1, 1)->startOfYear();
        $fim = (clone $inicio)->endOfYear();

        return [
            'entrada' => RmaEloquent::query()->whereBetween('created_at', [$inicio, $fim])->count(),
            'recebido' => RmaEloquent::query()->whereBetween('recebido_em', [$inicio, $fim])->count(),
            'encaminhado' => RmaEloquent::query()->whereBetween('encaminhado_em', [$inicio, $fim])->count(),
            'concluido' => RmaEloquent::query()->whereBetween('concluido_em', [$inicio, $fim])->count(),
            'com_nf_compra' => RmaEloquent::query()->whereBetween('created_at', [$inicio, $fim])->where('nfcompra', '>', 0)->count(),
            'garantia_ok' => RmaEloquent::query()
                ->whereBetween('concluido_em', [$inicio, $fim])
                ->where(fn ($query) => $query->whereNull('solucao')->orWhere('solucao', '!=', 'SEM GARANTIA'))
                ->count(),
            'sem_garantia' => RmaEloquent::query()->whereBetween('concluido_em', [$inicio, $fim])->where('solucao', 'SEM GARANTIA')->count(),
        ];
    }
}
