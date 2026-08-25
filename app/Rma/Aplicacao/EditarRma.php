<?php

namespace App\Rma\Aplicacao;

use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Parceiros\Aplicacao\EncontrarOuCriarCliente;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use RuntimeException;

/**
 * LEG-RMA-010 — ajuste da revisão (não tinha fase dona no plano original). Mesmas
 * normalizações RN-13/RN-14 da criação, reaplicadas a cada edição.
 */
final class EditarRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
        private readonly EncontrarOuCriarCliente $encontrarOuCriarCliente,
    ) {}

    /**
     * @param array{
     *     descricao: string,
     *     fabricante_id: ?int,
     *     fornecedor_id: ?int,
     *     modelo: ?string,
     *     sn: ?string,
     *     os: ?string,
     *     origem: ?string,
     *     empresa: ?string,
     *     cliente_nome: ?string,
     *     defeito: string,
     *     observacao: ?string,
     * } $dados
     */
    public function editar(int $id, array $dados): Rma
    {
        if ($this->repositorio->buscarPorId($id) === null) {
            throw new RuntimeException("Rma {$id} não encontrado.");
        }

        $cliente = filled($dados['cliente_nome'] ?? null)
            ? $this->encontrarOuCriarCliente->encontrarOuCriar($dados['cliente_nome'])
            : null;

        $fabricante = ($dados['fabricante_id'] ?? null)
            ? Fabricante::query()->find($dados['fabricante_id'])
            : null;

        $fornecedor = ($dados['fornecedor_id'] ?? null)
            ? Fornecedor::query()->find($dados['fornecedor_id'])
            : null;

        $rma = new Rma(
            id: $id,
            descricao: $dados['descricao'],
            fabricanteId: $fabricante?->id,
            fornecedorId: $fornecedor?->id,
            modelo: $dados['modelo'] ?? null,
            sn: $dados['sn'] ?? null,
            os: $dados['os'] ?? null,
            origem: $dados['origem'] ?? null,
            empresa: $dados['empresa'] ?? null,
            clienteId: $cliente?->id,
            defeito: $dados['defeito'],
            observacao: $dados['observacao'] ?? null,
        );

        $rmaNormalizado = $rma->comNormalizacaoDeGravacao(
            $fabricante?->nome,
            $fornecedor?->nome,
            $cliente?->nome,
            $rma->empresa,
        );

        return $this->repositorio->atualizar($rmaNormalizado);
    }
}
