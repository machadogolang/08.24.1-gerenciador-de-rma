<?php

namespace App\Rma\Dominio;

use Carbon\CarbonImmutable;

final class Rma
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $descricao,
        public readonly ?int $fabricanteId,
        public readonly ?int $fornecedorId,
        public readonly ?string $modelo,
        public readonly ?string $sn,
        public readonly ?string $os,
        public readonly ?string $origem,
        public readonly ?string $empresa,
        public readonly ?int $clienteId,
        public readonly string $defeito,
        public readonly ?string $observacao,
        public readonly Status $status = Status::Entrada,
        public readonly ?\DateTimeInterface $recebidoEm = null,
        public readonly ?\DateTimeInterface $encaminhadoEm = null,
        public readonly ?\DateTimeInterface $concluidoEm = null,
        public readonly ?\DateTimeInterface $arquivadoEm = null,
        public readonly ?string $protocolo = null,
        public readonly ?Solucao $solucao = null,
        public readonly ?string $snretorno = null,
        /**
         * Relação polimórfica (Eloquent: `AssistenciaTecnica`/`Fornecedor`/
         * `Fabricante`) representada aqui como par tipo/id, não como objeto Eloquent —
         * o domínio permanece puro (mesmo padrão de `fabricanteId`/`fornecedorId`:
         * ids resolvidos para exibição fora deste objeto).
         */
        public readonly ?string $destinatarioType = null,
        public readonly ?int $destinatarioId = null,
        public readonly ?Prioridade $prioridade = null,
        public readonly bool $marcarestoque = true,
        public readonly ?string $nfcompra = null,
        public readonly ?\DateTimeInterface $nfcompraEmissao = null,
        public readonly ?string $nfcompraChave = null,
        public readonly ?string $nfvenda = null,
        public readonly ?\DateTimeInterface $nfvendaEmissao = null,
        public readonly ?string $nfvendaChave = null,
        /**
         * VIS-V1-003 — promovidos de "coluna histórica de preservação" (Fase 9) para
         * campo de primeira classe do agregado: confirmado em runtime que `P/N` e
         * `SNID` são inputs reais do formulário "Novo" do TEMA V1 legado
         * (`menujs-top/novo.php`), gravados por `banco.oo.php::novo()` na criação e
         * incluídos na assinatura de `banco.oo.php::salvar()` (edição — código morto,
         * nunca chamado por nenhuma página, então não editáveis depois na prática).
         * Não aparecem em `detalhes.php` nem em nenhuma busca/regra de negócio do
         * legado — write-once na criação. Decisão: campo de primeira classe (a coluna
         * já existe, `App\Models\Rma::$fillable`), exposto no formulário de criação e
         * no detalhe do TEMA V1; não exposto no formulário de edição (mesmo
         * comportamento do legado).
         */
        public readonly ?string $pn = null,
        public readonly ?string $snid = null,
        public readonly ?StatusDeLancamento $lancadoretorno = null,
        public readonly ?float $valor = null,
        public readonly ?\DateTimeInterface $createdAt = null,
        /**
         * `LEG-RMA-036` — gravado só por `MarcarCreditoDisponivel`, nunca em cascata
         * automática a partir de `solucao`. Ver Fase 6 (`rma-creditos-e-relatorios`).
         */
        public readonly bool $creditoDisponivel = false,
    ) {}

    /**
     * RN-13 (HGST→Hitachi) + RN-14 (cascata de origem) — normalização confirmada
     * idêntica nos dois temas do legado, aplicada na criação e na edição. Método puro
     * (sem side effect), chamado por CriarRma/EditarRma antes de persistir.
     */
    public function comNormalizacaoDeGravacao(
        ?string $nomeFabricante,
        ?string $nomeFornecedor,
        ?string $nomeCliente,
        ?string $nomeEmpresa,
    ): self {
        $fabricante = $nomeFabricante === 'HGST' ? 'Hitachi' : $nomeFabricante;

        $origem = match (true) {
            $this->origem === $fabricante => 'Unknown',
            $this->origem === $nomeFornecedor => 'Unknown',
            $this->origem === $nomeCliente => 'Cliente',
            $this->origem === $nomeEmpresa => 'Cliente',
            in_array($this->origem, ['CELLSYSTEM', 'Cellsystem'], true) => 'Loja',
            in_array($this->origem, ['Leilao', 'Receita Federal', 'Receita'], true) => 'Leilão',
            default => $this->origem,
        };

        return $this->comAlteracoes(['origem' => $origem]);
    }

    /**
     * RN-15 — só copia `sn` → `snretorno` se estiver vazio E a solução implicar mesmo
     * aparelho de retorno; caso contrário fica em branco para digitação manual. Ausente
     * em TEMA V1 (regra nova nesta fase, sem regressão a corrigir). Método puro.
     */
    public function comSnretornoAutoPreenchido(): self
    {
        if ($this->snretorno !== null && $this->snretorno !== '') {
            return $this;
        }

        if ($this->solucao?->implicaMesmoAparelhoDeRetorno() !== true) {
            return $this;
        }

        return $this->comAlteracoes(['snretorno' => $this->sn]);
    }

    /**
     * ARQ-001 (`INV-RMA-10`) — cópia segura centralizada: preserva todo o estado atual
     * do agregado e sobrescreve só os campos informados. Único ponto de reconstrução do
     * objeto; os casos de uso de edição/transição de ciclo de vida devem usar este
     * método em vez de `new Rma(...)` campo a campo, que apagava silenciosamente todo
     * campo omitido (prioridade, notas fiscais, valor, crédito etc.) sempre que um
     * campo novo era introduzido no domínio sem que cada chamador fosse revisado.
     *
     * @param array<string, mixed> $alteracoes chaves = nomes dos parâmetros do construtor
     */
    public function comAlteracoes(array $alteracoes): self
    {
        return new self(...[...$this->paraConstrucao(), ...$alteracoes]);
    }

    /**
     * @return array<string, mixed>
     */
    private function paraConstrucao(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'fabricanteId' => $this->fabricanteId,
            'fornecedorId' => $this->fornecedorId,
            'modelo' => $this->modelo,
            'sn' => $this->sn,
            'os' => $this->os,
            'origem' => $this->origem,
            'empresa' => $this->empresa,
            'clienteId' => $this->clienteId,
            'defeito' => $this->defeito,
            'observacao' => $this->observacao,
            'status' => $this->status,
            'recebidoEm' => $this->recebidoEm,
            'encaminhadoEm' => $this->encaminhadoEm,
            'concluidoEm' => $this->concluidoEm,
            'arquivadoEm' => $this->arquivadoEm,
            'protocolo' => $this->protocolo,
            'solucao' => $this->solucao,
            'snretorno' => $this->snretorno,
            'destinatarioType' => $this->destinatarioType,
            'destinatarioId' => $this->destinatarioId,
            'prioridade' => $this->prioridade,
            'marcarestoque' => $this->marcarestoque,
            'nfcompra' => $this->nfcompra,
            'nfcompraEmissao' => $this->nfcompraEmissao,
            'nfcompraChave' => $this->nfcompraChave,
            'nfvenda' => $this->nfvenda,
            'nfvendaEmissao' => $this->nfvendaEmissao,
            'nfvendaChave' => $this->nfvendaChave,
            'pn' => $this->pn,
            'snid' => $this->snid,
            'lancadoretorno' => $this->lancadoretorno,
            'valor' => $this->valor,
            'createdAt' => $this->createdAt,
            'creditoDisponivel' => $this->creditoDisponivel,
        ];
    }

    /**
     * RN-11 (`LEG-RMA-028`) — a ordem de avaliação do `match(true)` preserva a
     * precedência confirmada no legado (primeiro critério que bate vence). **Sem** o
     * critério morto `prioridade=='urgente'` (não existe mais, ver `Prioridade`).
     *
     * Desvio do `design.md`: a comparação usa `Origem::Cliente->value` (string) em vez
     * do enum diretamente — `$this->origem` permanece `?string` neste objeto (não
     * `?Origem`), porque `comNormalizacaoDeGravacao()` recebe/produz valores de origem
     * ainda não normalizados (ex.: nomes de fabricante/cliente arbitrários) que não
     * pertencem ao domínio fechado do enum; tipar a propriedade como `Origem` quebraria
     * `RmaTest` (Fase 3). Ver decisão completa em `log-implementacao-v3.md`, Fase 5.
     */
    public function classeDeAlerta(): ClasseDeAlerta
    {
        return match (true) {
            $this->solucao === Solucao::SemGarantia => ClasseDeAlerta::Inconformidade,
            $this->prioridade === Prioridade::Alta => ClasseDeAlerta::Inconformidade,
            $this->origemEhTerceiroForaDoPrazo() => ClasseDeAlerta::Inconformidade,
            $this->marcarestoque === false
                && in_array($this->origem, [Origem::Cliente->value, Origem::Licitacao->value], true)
                => ClasseDeAlerta::Inconformidade,
            default => ClasseDeAlerta::Neutro,
        };
    }

    /**
     * RN-12 — prazo legal de 30 dias contado da criação do RMA, não persistido
     * (calculado). Usado por `origemEhTerceiroForaDoPrazo()` e disponível para exibição.
     */
    public function prazoLegal(): CarbonImmutable
    {
        return CarbonImmutable::make($this->createdAt)->addDays(30);
    }

    private function origemEhTerceiroForaDoPrazo(): bool
    {
        return $this->origem === Origem::Cliente->value
            && $this->marcarestoque === false
            && $this->createdAt !== null
            && $this->prazoLegal()->isPast();
    }

    /**
     * Fase 7 (`RegistrarModificacaoDeRma`) — snapshot desnormalizado equivalente ao
     * gravado pelo `modificacao` do legado (`estado_apos`): campos-chave suficientes
     * para reconstruir "o que era o RMA no momento da ação" sem diff campo-a-campo
     * (`EVO-AUD-001`, backlog evolutivo, pendência registrada em `proposal.md`).
     *
     * @return array<string, mixed>
     */
    public function paraSnapshot(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'fabricante_id' => $this->fabricanteId,
            'fornecedor_id' => $this->fornecedorId,
            'modelo' => $this->modelo,
            'sn' => $this->sn,
            'os' => $this->os,
            'origem' => $this->origem,
            'empresa' => $this->empresa,
            'cliente_id' => $this->clienteId,
            'defeito' => $this->defeito,
            'observacao' => $this->observacao,
            'status' => $this->status->name,
            'protocolo' => $this->protocolo,
            'solucao' => $this->solucao?->value,
            'snretorno' => $this->snretorno,
            'destinatario_type' => $this->destinatarioType,
            'destinatario_id' => $this->destinatarioId,
            'pn' => $this->pn,
            'snid' => $this->snid,
        ];
    }
}
