<?php

namespace App\Rma\Dominio;

interface RepositorioDeRmas
{
    public function criar(Rma $rma): Rma;         // devolve com id preenchido

    /**
     * Não está no snippet literal do design.md (que antecede o ajuste da revisão que
     * trouxe `EditarRma`/`LEG-RMA-010` para esta fase) — acrescentado para que
     * `EditarRma` não precise furar a fronteira e tocar o Eloquent model diretamente.
     */
    public function atualizar(Rma $rma): Rma;

    public function buscarPorId(int $id): ?Rma;

    /** @return Rma[] */
    public function buscar(CriterioDeBusca $criterio): array;

    /** @return Rma[] */
    public function listarPorPainel(PainelDeStatus $painel): array;
}
