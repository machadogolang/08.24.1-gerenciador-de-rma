<?php

namespace App\Rma\Dominio;

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

        return new self(
            id: $this->id,
            descricao: $this->descricao,
            fabricanteId: $this->fabricanteId,
            fornecedorId: $this->fornecedorId,
            modelo: $this->modelo,
            sn: $this->sn,
            os: $this->os,
            origem: $origem,
            empresa: $this->empresa,
            clienteId: $this->clienteId,
            defeito: $this->defeito,
            observacao: $this->observacao,
            status: $this->status,
            recebidoEm: $this->recebidoEm,
            encaminhadoEm: $this->encaminhadoEm,
            concluidoEm: $this->concluidoEm,
            arquivadoEm: $this->arquivadoEm,
            protocolo: $this->protocolo,
            solucao: $this->solucao,
            snretorno: $this->snretorno,
            destinatarioType: $this->destinatarioType,
            destinatarioId: $this->destinatarioId,
        );
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

        return new self(
            id: $this->id,
            descricao: $this->descricao,
            fabricanteId: $this->fabricanteId,
            fornecedorId: $this->fornecedorId,
            modelo: $this->modelo,
            sn: $this->sn,
            os: $this->os,
            origem: $this->origem,
            empresa: $this->empresa,
            clienteId: $this->clienteId,
            defeito: $this->defeito,
            observacao: $this->observacao,
            status: $this->status,
            recebidoEm: $this->recebidoEm,
            encaminhadoEm: $this->encaminhadoEm,
            concluidoEm: $this->concluidoEm,
            arquivadoEm: $this->arquivadoEm,
            protocolo: $this->protocolo,
            solucao: $this->solucao,
            snretorno: $this->sn,
            destinatarioType: $this->destinatarioType,
            destinatarioId: $this->destinatarioId,
        );
    }
}
