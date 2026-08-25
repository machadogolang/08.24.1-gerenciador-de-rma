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
            $this->id, $this->descricao, $this->fabricanteId, $this->fornecedorId,
            $this->modelo, $this->sn, $this->os, $origem, $this->empresa,
            $this->clienteId, $this->defeito, $this->observacao,
        );
    }
}
