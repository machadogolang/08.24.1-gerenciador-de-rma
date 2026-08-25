<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Rma;
use App\Rma\Aplicacao\Alertas\AguardandoCredito;
use App\Rma\Aplicacao\Alertas\UrgenciaPorThreshold;

/**
 * CP19 (paridade visual V2) — composição das 14 seções de
 * `legacy-source/15.8.1/inc/rightmenu.php` num único array, mesmo padrão de
 * `Alertas\ListarGruposDeAlertas` (Fase 8). "URGENTE" e "PENDENTE CREDITO" reutilizam
 * classes de leitura já existentes (`UrgenciaPorThreshold`/`AguardandoCredito`) — as
 * queries de `right_urgente()`/`right_pendentecredito()` do legado já tinham
 * equivalente exato implementado na Fase 5 (RN-11/RN-12), nenhuma regra de negócio
 * nova. Injetado no layout do TEMA V2 via `View::composer`
 * (`AppServiceProvider::boot()`) — a sidebar aparece em toda página, igual ao legado
 * (`inc/rightmenu.php` incluído por `index.php`, não por página específica).
 */
final class ListarPainelLateral
{
    public function __construct(
        private readonly DeuEntradaHoje $deuEntradaHoje,
        private readonly Recebidos $recebidos,
        private readonly Encaminhados $encaminhados,
        private readonly Last10Concluidos $last10Concluidos,
        private readonly Destinatarios $destinatarios,
        private readonly TransportePortoAlegre $transportePortoAlegre,
        private readonly UrgenciaPorThreshold $urgente,
        private readonly AguardandoCredito $pendenteCredito,
        private readonly CreditoDisponivel $creditoDisponivel,
        private readonly Fabricantes $fabricantes,
        private readonly Fornecedores $fornecedores,
        private readonly Clientes $clientes,
        private readonly ProdutosDeCliente $produtosDeCliente,
        private readonly TodosProdutos $todosProdutos,
    ) {}

    /**
     * Cada seção normaliza para `{nome, valor, id?}` — `id` só existe nas seções
     * `lista` (viram link para `rmas.show`); as `contagem` nunca são clicáveis no
     * legado (nenhuma delas tem `<a>` de verdade — a única exceção do PHP fonte é uma
     * condição residual `if ($q==1)` em duas seções, comportamento estranho demais
     * para reproduzir sem necessidade).
     *
     * [BUG-LEGADO] `right_entrada()` formata a hora com `date('H:m', ...)` — `m` é
     * mês, não minuto (`i`); exibiria "14:08" achando que é 14h08 quando na verdade é
     * hora 14 do mês 08. Decisão: corrigir para `H:i` (hora:minuto) — diferente de
     * uma inconsistência cosmética (ex.: encoding), isto exibe uma informação
     * literalmente errada ao usuário, mesmo padrão de correção já usado nos ARQ-*
     * desta base.
     *
     * @return array<string, array{titulo: string, tipo: 'lista'|'contagem', registros: array<int, array{nome: string, valor: string, id?: int}>}>
     */
    public function listar(): array
    {
        return [
            'entrada_r' => $this->secaoLista('DEU ENTRADA HOJE', $this->deuEntradaHoje->listar(), fn (Rma $r) => $r->created_at, 'H:i'),
            'recebido_r' => $this->secaoLista('RECEBIDOS', $this->recebidos->listar(), fn (Rma $r) => $r->recebido_em, 'd/m'),
            'encaminhado_r' => $this->secaoLista('ENCAMINHADOS', $this->encaminhados->listar(), fn (Rma $r) => $r->encaminhado_em, 'd/m'),
            'concluido_r' => $this->secaoLista('LAST 10 CONCLUIDOS', $this->last10Concluidos->listar(), fn (Rma $r) => $r->concluido_em, 'd/m'),
            'destinatarios_r' => $this->secaoContagem('DESTINATARIOS', $this->destinatarios->listar()),
            'portoalegre_r' => $this->secaoLista('TRANSPORTE P/ PORTO A', $this->transportePortoAlegre->listar(), fn (Rma $r) => $r->created_at, 'd/m'),
            'urgente_r' => $this->secaoLista('URGENTE', $this->urgente->listar(), fn (Rma $r) => $r->created_at, 'd/m'),
            'pendentecredito_r' => $this->secaoLista('PENDENTE CREDITO', $this->pendenteCredito->listar(), fn (Rma $r) => $r->created_at, 'd/m'),
            'creditodisponivel_r' => $this->secaoContagem('CREDITO DISPONIVEL', $this->creditoDisponivel->listar()),
            'fabricantes_r' => $this->secaoContagem('FABRICANTES', $this->fabricantes->listar()),
            'fornecedores_r' => $this->secaoContagem('FORNECEDORES', $this->fornecedores->listar()),
            'clientes_r' => $this->secaoContagem('CLIENTES', $this->clientes->listar()),
            'produtosdecliente_r' => $this->secaoContagem('PRODUTOS DE CLIENTE', $this->produtosDeCliente->listar()),
            'todosprodutos_r' => $this->secaoContagem('TODOS PRODUTOS', $this->todosProdutos->listar()),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Rma>  $registros
     * @param  \Closure(Rma): ?\DateTimeInterface  $data
     */
    private function secaoLista(string $titulo, $registros, \Closure $data, string $formato): array
    {
        return [
            'titulo' => $titulo,
            'tipo' => 'lista',
            'registros' => $registros->map(fn (Rma $r) => [
                'id' => $r->id,
                'nome' => mb_substr($r->descricao, 0, 16),
                'valor' => $data($r)?->format($formato) ?? '',
            ])->all(),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{nome: string, contagem: int}>  $registros
     */
    private function secaoContagem(string $titulo, $registros): array
    {
        return [
            'titulo' => $titulo,
            'tipo' => 'contagem',
            'registros' => $registros->map(fn (array $r) => ['nome' => $r['nome'], 'valor' => (string) $r['contagem']])->all(),
        ];
    }
}
