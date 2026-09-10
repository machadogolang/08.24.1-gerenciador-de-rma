<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\RelatorioInformacaoAdicional;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Relatorios\RelatorioCreditosDisponiveis;
use App\Rma\Aplicacao\Relatorios\RelatorioFiscalV1;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEmEstoqueParaContagem;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEncaminhados;
use App\Rma\Dominio\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * 3 relatorios fiscais/contabeis (`LEG-RMA-037/038/039`).
 *
 * PAR14-REL-RPEC/RCD/RMPE-001 - o TEMA V1 recebe a folha historica completa do Legacy
 * `14.6.1/page/relatorios.php` (colunas, totalizadores, informacao adicional e nota de
 * impressao) via `RelatorioFiscalV1`; o TEMA V2 mantem as views proprias de
 * compatibilidade (o menu historico do V2 tem um unico item Relatorios, PAR15-REL).
 *
 * A regra de SELECAO vive nos servicos de relatorio; XML/HTML nunca carrega SQL.
 */
class RelatorioController extends Controller
{
    public function creditosDisponiveis(
        RelatorioCreditosDisponiveis $relatorio,
        RelatorioFiscalV1 $projecao,
    ): View {
        Gate::authorize('viewAny', RmaEloquent::class);

        $registros = $relatorio->listar();
        $montado = $projecao->montar($registros, 'RCRD');

        return view_do_tema('rma.relatorios.rcd', [
            'titulo' => 'Relatorio de Creditos Disponiveis (RCD)',
            'registros' => $registros,
            'relatorio' => $this->pacoteV1(
                'RCRD',
                'RCD - RELATORIO DE CREDITOS DISPONIVEIS',
                'Credito disponivel:',
                [
                    ['rotulo' => 'CONCLUIDO', 'largura' => 8, 'chave' => 'data'],
                    ['rotulo' => 'FABRICANTE', 'largura' => 11, 'chave' => 'fabricante'],
                    ['rotulo' => 'DESCRICAO', 'largura' => 13, 'chave' => 'descricao'],
                    ['rotulo' => 'EMPRESA', 'largura' => 10, 'chave' => 'empresa'],
                    ['rotulo' => 'MODELO', 'largura' => 16, 'chave' => 'modelo'],
                    ['rotulo' => 'NF C', 'largura' => 5, 'chave' => 'nfcompra'],
                    ['rotulo' => 'NF R', 'largura' => 5, 'chave' => 'nfremessa'],
                    ['rotulo' => 'VALOR', 'largura' => 5, 'chave' => 'valor'],
                    ['rotulo' => 'PROTOCOLO', 'largura' => 11, 'chave' => 'protocolo'],
                    ['rotulo' => 'DESTINATARIO', 'largura' => 12, 'chave' => 'destinatario'],
                    ['rotulo' => 'OS', 'largura' => 4, 'chave' => 'os'],
                ],
                $montado,
            ),
        ]);
    }

    /**
     * RPEC - status continua filtro configuravel pelo usuario (query string opcional),
     * somado as condicoes confirmadas do Legacy (nfremessa < 1 e marcarestoque = 1).
     */
    public function produtosEmEstoqueParaContagem(
        Request $request,
        RelatorioProdutosEmEstoqueParaContagem $relatorio,
        RelatorioFiscalV1 $projecao,
    ): View {
        Gate::authorize('viewAny', RmaEloquent::class);

        $dados = $request->validate([
            'status' => ['nullable', 'string', 'in:'.implode(',', array_column(Status::cases(), 'name'))],
        ]);

        $status = isset($dados['status'])
            ? collect(Status::cases())->first(fn (Status $caso) => $caso->name === $dados['status'])
            : null;

        $registros = $relatorio->listar($status);
        $montado = $projecao->montar($registros, 'RPEC');

        return view_do_tema('rma.relatorios.rpec', [
            'titulo' => 'Relatorio de Produtos em Estoque para Contagem (RPEC)',
            'registros' => $registros,
            'status' => $status,
            'relatorio' => $this->pacoteV1(
                'RPEC',
                'RPEC - RELACAO DOS PRODUTOS EM ESTOQUE PARA CONTAGEM',
                'Relatorio de relatorio com produtos que estao no setor de RMA e que devem ser contados no estoque:',
                [
                    ['rotulo' => 'ENTRADA', 'largura' => 8, 'chave' => 'data'],
                    ['rotulo' => 'FABRICANTE', 'largura' => 12, 'chave' => 'fabricante'],
                    ['rotulo' => 'DESCRICAO', 'largura' => 13, 'chave' => 'descricao'],
                    ['rotulo' => 'EMPRESA', 'largura' => 10, 'chave' => 'empresa'],
                    ['rotulo' => 'MODELO', 'largura' => 18, 'chave' => 'modelo'],
                    ['rotulo' => 'NF C', 'largura' => 5, 'chave' => 'nfcompra'],
                    ['rotulo' => 'NF V', 'largura' => 5, 'chave' => 'nfvenda'],
                    ['rotulo' => 'ORIGEM', 'largura' => 11, 'chave' => 'origem'],
                    ['rotulo' => 'DESTINATARIO', 'largura' => 14, 'chave' => 'destinatario'],
                    ['rotulo' => 'OS', 'largura' => 4, 'chave' => 'os'],
                ],
                $montado,
            ),
        ]);
    }

    /**
     * RMPE - intervalo de datas OPCIONAL (filtro moderno) sobre a regra confirmada.
     * O Legacy nao filtrava por periodo (a query ignora data); por isso, sem
     * `data_inicio`/`data_fim` a selecao roda sem filtro de periodo, mantendo a
     * resposta 200 e uma URL de QA deterministica (`/v1/relatorios/rmpe`).
     */
    public function produtosEncaminhados(
        Request $request,
        RelatorioProdutosEncaminhados $relatorio,
        RelatorioFiscalV1 $projecao,
    ): View {
        Gate::authorize('viewAny', RmaEloquent::class);

        $dados = $request->validate([
            'data_inicio' => ['nullable', 'date', 'required_with:data_fim'],
            'data_fim' => ['nullable', 'date', 'required_with:data_inicio', 'after_or_equal:data_inicio'],
        ]);

        $registros = $relatorio->listar(
            isset($dados['data_inicio']) ? new \DateTimeImmutable($dados['data_inicio']) : null,
            isset($dados['data_fim']) ? new \DateTimeImmutable($dados['data_fim'].' 23:59:59') : null,
        );
        $montado = $projecao->montar($registros, 'RMPE');

        return view_do_tema('rma.relatorios.rmpe', [
            'titulo' => 'Relatorio de Produtos Encaminhados (RMPE)',
            'registros' => $registros,
            'dataInicio' => $dados['data_inicio'] ?? '',
            'dataFim' => $dados['data_fim'] ?? '',
            'relatorio' => $this->pacoteV1(
                'RMPE',
                'RMPE - RELACAO DOS PRODUTOS ENCAMINHADOS PELO RMA',
                'Relatorio dos produtos encaminhados para garantia que nao constam no estoque no momento:',
                [
                    ['rotulo' => 'ENCAMINHADO', 'largura' => 10, 'chave' => 'data'],
                    ['rotulo' => 'FABRICANTE', 'largura' => 10, 'chave' => 'fabricante'],
                    ['rotulo' => 'DESCRICAO', 'largura' => 13, 'chave' => 'descricao'],
                    ['rotulo' => 'EMPRESA', 'largura' => 10, 'chave' => 'empresa'],
                    ['rotulo' => 'MODELO', 'largura' => 18, 'chave' => 'modelo'],
                    ['rotulo' => 'NF C', 'largura' => 5, 'chave' => 'nfcompra'],
                    ['rotulo' => 'NF V', 'largura' => 5, 'chave' => 'nfvenda'],
                    ['rotulo' => 'NF R', 'largura' => 5, 'chave' => 'nfremessa'],
                    ['rotulo' => 'VALOR', 'largura' => 6, 'chave' => 'valor'],
                    ['rotulo' => 'DESTINATARIO', 'largura' => 14, 'chave' => 'destinatario'],
                    ['rotulo' => 'OS', 'largura' => 4, 'chave' => 'os'],
                ],
                $montado,
            ),
        ]);
    }

    /**
     * PAR14-REL-RPEC-004/RCD-003/RMPE-002 - persiste a informacao adicional do
     * relatorio (substitui `relatorio.informacaoadicional` do Legacy).
     */
    public function salvarInformacaoAdicional(Request $request, string $codigo): RedirectResponse
    {
        Gate::authorize('viewAny', RmaEloquent::class);
        abort_unless(in_array($codigo, RelatorioFiscalV1::CODIGOS, true), 404);

        $dados = $request->validate([
            'informacao_adicional' => ['nullable', 'string', 'max:5000'],
        ]);

        $registro = RelatorioInformacaoAdicional::query()->firstOrNew(['codigo' => $codigo]);
        $registro->informacao_adicional = $dados['informacao_adicional'] ?? '';
        $registro->save();

        return back()->with('status', 'Informacao adicional salva.');
    }

    /**
     * @param  array<int, array{rotulo: string, largura: int, chave: string}>  $colunas
     * @param  array{linhas: array<int, array<string, string>>, totais: array<string, mixed>}  $montado
     * @return array<string, mixed>
     */
    private function pacoteV1(string $codigo, string $titulo, string $subtitulo, array $colunas, array $montado): array
    {
        return [
            'codigo' => $codigo,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'colunas' => $colunas,
            'linhas' => $montado['linhas'],
            'totais' => $montado['totais'],
            'informacao_adicional' => RelatorioInformacaoAdicional::query()
                ->where('codigo', $codigo)
                ->value('informacao_adicional') ?? 'n/a',
        ];
    }
}
