<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Parceiros\Aplicacao\EncontrarOuCriarCliente;
use App\Parceiros\Aplicacao\EncontrarOuCriarFabricante;
use App\Parceiros\Aplicacao\EncontrarOuCriarFornecedor;
use App\Rma\Dominio\Prioridade;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\ParserDeDataLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use App\Rma\Infraestrutura\Migracao\ResolverDestinatario;
use App\Rma\Infraestrutura\Migracao\TabelaDeTraducao;
use Illuminate\Support\Facades\DB;

/**
 * `bd` → `rmas` (`INV-RMA-06` §1) — entidade central, campo a campo. Idempotência via
 * `numero_legado`: se `Rma::where('numero_legado', $numero)->exists()`, a linha é pulada
 * (a menos que `$forcar=true`, reprocessamento explícito pós-correção de bug).
 *
 * Aplica as 3 pendências reais resolvidas de `INV-RMA-06`:
 * 1. Formato de data ambíguo — `ParserDeDataLegado` (3 tentativas), nunca lança exceção,
 *    data não-parseável vira `NULL` + anomalia com o valor bruto original.
 * 2. `status='retornou'`/`retornou IS NOT NULL` — registrado como anomalia se ocorrer em
 *    dado real, sem inventar case novo no enum `Status`.
 * 3. `relatorio.informacaoadicional` — decisão B (descartar) aplicada por omissão: esta
 *    classe nunca lê a tabela `relatorio` (nem existe `ConexaoLegado::relatorio()`).
 */
final class ImportarRmas
{
    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
        private readonly EncontrarOuCriarFabricante $fabricantes = new EncontrarOuCriarFabricante,
        private readonly EncontrarOuCriarFornecedor $fornecedores = new EncontrarOuCriarFornecedor,
        private readonly EncontrarOuCriarCliente $clientes = new EncontrarOuCriarCliente,
        private readonly ResolverDestinatario $destinatarios = new ResolverDestinatario,
    ) {}

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false, bool $forcar = false): void
    {
        $origem = $this->conexao->bd();
        $total = 0;
        $processados = 0;

        DB::transaction(function () use ($origem, $relatorio, $dryRun, $forcar, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;
                $numero = (int) $linha->numero;

                $jaMigrado = RmaEloquent::query()->where('numero_legado', $numero)->exists();

                if ($jaMigrado && ! $forcar) {
                    continue;
                }

                if ($dryRun) {
                    continue;
                }

                $dados = $this->traduzirLinha($linha, $numero, $relatorio);

                RmaEloquent::query()->updateOrCreate(['numero_legado' => $numero], $dados);

                $processados++;
            }
        });

        $relatorio->contarOrigem('bd', $total);
        $relatorio->contarDestino('rmas', $processados);
    }

    /**
     * @return array<string, mixed>
     */
    private function traduzirLinha(object $linha, int $numero, RelatorioDeReconciliacao $relatorio): array
    {
        // --- status (PENDÊNCIA-2) ---
        $statusBruto = $linha->status;
        $status = TabelaDeTraducao::status($statusBruto);

        if ($statusBruto === 'retornou') {
            $relatorio->registrarAnomalia(
                'bd',
                $numero,
                "status='retornou' encontrado em dado real (PENDÊNCIA-2 de INV-RMA-06, LEG-RMA-016) — RMA importado sem status resolvido, sem inventar case novo no enum Status preventivamente"
            );
        } elseif ($status === null && $statusBruto !== null && $statusBruto !== '') {
            $relatorio->registrarAnomalia('bd', $numero, "status='{$statusBruto}' fora do domínio confirmado (entrada/recebido/encaminhado/concluido/arquivado)");
        }

        // --- retornou (PENDÊNCIA-2, cross-check adicional) ---
        if (! empty($linha->retornou)) {
            $relatorio->registrarAnomalia(
                'bd',
                $numero,
                "retornou='{$linha->retornou}' preenchido em dado real (PENDÊNCIA-2 de INV-RMA-06) — campo não migrado (LEG-RMA-016, Status sem case Retornou)"
            );
        }

        // --- dtains × entrada (cross-check §1.3, não migrado como coluna) ---
        if (! empty($linha->dtains) && ! empty($linha->entrada) && (string) $linha->dtains !== (string) $linha->entrada) {
            $relatorio->registrarAnomalia('bd', $numero, "dtains ({$linha->dtains}) diverge de entrada ({$linha->entrada}) — só verificação cruzada, nenhum dos dois é sobrescrito");
        }

        // --- origem ---
        $origemBruta = $linha->origem;
        $origem = TabelaDeTraducao::origem($origemBruta);

        if ($origem === null && $origemBruta !== null && $origemBruta !== '') {
            $relatorio->registrarAnomalia('bd', $numero, "origem='{$origemBruta}' fora do domínio confirmado");
        }

        // --- prioridade (com conversão assistida de 'urgente') ---
        $prioridadeBruta = $linha->prioridade;
        $prioridade = TabelaDeTraducao::prioridade($prioridadeBruta);

        if ($prioridade === null && TabelaDeTraducao::prioridadeEhResiduoUrgente($prioridadeBruta)) {
            $prioridade = Prioridade::Alta;
            $relatorio->registrarConversaoAssistida('bd', $numero, "prioridade='urgente' (resíduo RN-08, INV-RMA-06 §4) convertida para Prioridade::Alta");
        } elseif ($prioridade === null && $prioridadeBruta !== null && $prioridadeBruta !== '') {
            $relatorio->registrarAnomalia('bd', $numero, "prioridade='{$prioridadeBruta}' fora do domínio confirmado");
        }

        // --- solucao ---
        $solucaoBruta = $linha->solucao;
        $solucao = TabelaDeTraducao::solucao($solucaoBruta);
        $solucaoLegadoBruto = null;

        if ($solucao === null && $solucaoBruta !== null && $solucaoBruta !== '') {
            $solucaoLegadoBruto = $solucaoBruta;
            $relatorio->registrarAnomalia('bd', $numero, "solucao='{$solucaoBruta}' não bate em nenhum dos 16 valores fechados — preservado em solucao_legado_bruto");
        }

        // --- lancadoretorno ---
        $lancadoRetornoBruto = $linha->lancadoretorno;
        $lancadoRetorno = TabelaDeTraducao::statusDeLancamento($lancadoRetornoBruto);

        if ($lancadoRetorno === null && $lancadoRetornoBruto !== null && $lancadoRetornoBruto !== '') {
            $relatorio->registrarAnomalia('bd', $numero, "lancadoretorno='{$lancadoRetornoBruto}' fora do domínio confirmado");
        }

        // --- datas (PENDÊNCIA-1) ---
        $recebidoEm = $this->parsearData('recebido', $linha->recebido ?? null, $numero, $relatorio);
        $encaminhadoEm = $this->parsearData('encaminhado', $linha->encaminhado ?? null, $numero, $relatorio);
        $concluidoEm = $this->parsearData('concluido', $linha->concluido ?? null, $numero, $relatorio);
        $arquivadoEm = $this->parsearData('arquivado', $linha->arquivado ?? null, $numero, $relatorio);
        $nfcompraEmissao = $this->parsearData('nfcompra_emissao', $linha->nfcompra_emissao ?? null, $numero, $relatorio);
        $nfvendaEmissao = $this->parsearData('nfvenda_emissao', $linha->nfvenda_emissao ?? null, $numero, $relatorio);
        $nfRemessaEmissao = $this->parsearData('nfremessa_emissao', $linha->nfremessa_emissao ?? null, $numero, $relatorio);
        $nfRetornoEmissao = $this->parsearData('nfretorno_emissao', $linha->nfretorno_emissao ?? null, $numero, $relatorio);

        // --- fabricante (RN-13, HGST→Hitachi, defensivo) ---
        $fabricanteId = null;
        $nomeFabricante = $linha->fabricante ?? null;

        if ($nomeFabricante !== null && trim($nomeFabricante) !== '') {
            $nomeFabricanteNormalizado = trim($nomeFabricante) === 'HGST' ? 'Hitachi' : $nomeFabricante;
            $fabricanteId = $this->fabricantes->encontrarOuCriar($nomeFabricanteNormalizado)->id;
        }

        // --- fornecedor ---
        $fornecedorId = null;
        $nomeFornecedor = $linha->fornecedor ?? null;

        if ($nomeFornecedor !== null && trim($nomeFornecedor) !== '') {
            $fornecedorId = $this->fornecedores->encontrarOuCriar($nomeFornecedor)->id;
        }

        // --- cliente ---
        $clienteId = null;
        $nomeCliente = $linha->cliente ?? null;

        if ($nomeCliente !== null && trim($nomeCliente) !== '') {
            $clienteId = $this->clientes->encontrarOuCriar($nomeCliente)->id;
        }

        // --- destinatario (cascata, sem auto-criação) ---
        $nomeDestinatario = $linha->destinatario ?? null;
        $destino = $this->destinatarios->resolver($nomeDestinatario);
        $destinatarioNomeLegado = null;

        if ($destino === null && $nomeDestinatario !== null && trim($nomeDestinatario) !== '') {
            $destinatarioNomeLegado = $nomeDestinatario;
            $relatorio->registrarAnomalia('bd', $numero, "destinatario='{$nomeDestinatario}' não resolvido em assistencia_tecnica/fornecedor/fabricante — preservado em destinatario_nome_legado");
        }

        // --- operador (soft match por e-mail) ---
        $operadorId = null;
        $operadorEmailLegado = $linha->usuario ?? null;

        if ($operadorEmailLegado !== null && trim($operadorEmailLegado) !== '') {
            $operador = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($operadorEmailLegado))])->first();
            $operadorId = $operador?->id;
        }

        // --- empresa ---
        $empresa = TabelaDeTraducao::empresa($linha->empresa ?? null);

        return [
            'descricao' => $linha->descricao,
            'fabricante_id' => $fabricanteId,
            'fornecedor_id' => $fornecedorId,
            'modelo' => $linha->modelo,
            'sn' => $linha->sn,
            'os' => $linha->os,
            'status' => $status,
            'origem' => $origem?->value,
            'empresa' => $empresa,
            'cliente_id' => $clienteId,
            'defeito' => $linha->defeito,
            'observacao' => $linha->observacao,
            'recebido_em' => $recebidoEm,
            'encaminhado_em' => $encaminhadoEm,
            'concluido_em' => $concluidoEm,
            'arquivado_em' => $arquivadoEm,
            'protocolo' => $linha->protocolo,
            'solucao' => $solucao,
            'solucao_legado_bruto' => $solucaoLegadoBruto,
            'snretorno' => $linha->snretorno,
            'destinatario_type' => $destino['type'] ?? null,
            'destinatario_id' => $destino['id'] ?? null,
            'destinatario_nome_legado' => $destinatarioNomeLegado,
            'prioridade' => $prioridade,
            'marcarestoque' => $linha->marcarestoque === null ? true : (bool) $linha->marcarestoque,
            'nfcompra' => $linha->nfcompra,
            'nfcompra_emissao' => $nfcompraEmissao,
            'nfcompra_chave' => $linha->nfcompra_chave,
            'nfvenda' => $linha->nfvenda,
            'nfvenda_emissao' => $nfvendaEmissao,
            'nfvenda_chave' => $linha->nfvenda_chave,
            'lancadoretorno' => $lancadoRetorno,
            'valor' => $linha->valor,
            'credito_disponivel' => (bool) ($linha->creditodisponivel ?? false),
            'created_at' => $linha->entrada,
            'updated_at' => $linha->dtaalt,
            // §1.2 — preservação sem regra de negócio dona
            'nf_devolucao_de_venda' => $linha->nfdevolucaodevenda ?? null,
            'nf_entrada_cliente_legado' => $linha->nfentrada_cli ?? null,
            'nf_retorno_cliente_legado' => $linha->nfretorno_cli ?? null,
            'nf_remessa' => $linha->nfremessa ?? null,
            'nf_remessa_emissao' => $nfRemessaEmissao,
            'nf_remessa_chave' => $linha->nfremessa_chave ?? null,
            'nf_retorno_numero' => $linha->nfretorno ?? null,
            'nf_retorno_emissao' => $nfRetornoEmissao,
            'nf_retorno_chave' => $linha->nfretorno_chave ?? null,
            'pn' => $linha->pn ?? null,
            'snid' => $linha->snid ?? null,
            'rastreio_ida' => $linha->rastreio_ida ?? null,
            'rastreio_retorno' => $linha->rastreio_retorno ?? null,
            'cliente_email_legado' => $linha->cliente_email ?? null,
            'destinatario_email_legado' => $linha->destinatario_email ?? null,
            'destinatario_fone_legado' => $linha->destinatario_fone ?? null,
            'descricao_final_legado' => $linha->descricao_final ?? null,
            'operador_email_legado' => $operadorEmailLegado,
            'operador_id' => $operadorId,
        ];
    }

    private function parsearData(string $campo, ?string $bruto, int $numero, RelatorioDeReconciliacao $relatorio): ?string
    {
        $resultado = ParserDeDataLegado::parse($bruto);

        if (! $resultado->ok) {
            $relatorio->registrarAnomalia('bd', $numero, "{$campo}='{$resultado->bruto}' não é parseável em d/m/Y nem Y-m-d — gravado NULL");

            return null;
        }

        return $resultado->data?->toDateTimeString();
    }
}
