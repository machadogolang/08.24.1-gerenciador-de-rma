<?php

namespace App\Rma\Aplicacao;

use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Parceiros\Aplicacao\EncontrarOuCriarCliente;
use App\Rma\Dominio\Eventos\RmaCriado;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use Illuminate\Support\Facades\Auth;

/**
 * LEG-RMA-007. Usa `EncontrarOuCriarCliente` (Fase 2, Parceiros) quando o cliente
 * informado é novo, e aplica `Rma::comNormalizacaoDeGravacao()` (RN-13/RN-14) antes de
 * persistir. RN-17 (`marcarestoque`): dívida técnica do legado (calcula um valor por
 * `origem` e descarta o resultado, nunca grava — código morto) — não reproduzida; o
 * valor do formulário é gravado normalmente, sem cálculo adicional.
 *
 * **Fase 7:** dispara `RmaCriado` ao final, lido via `Auth::user()` em vez de um novo
 * parâmetro `User $ator` no método — o controller (`RmaController::store()`) nunca
 * passou o ator autenticado para este caso de uso, e mudar a assinatura quebraria os
 * call sites das Fases 3 sem necessidade. `Auth::user()` é o mesmo usuário que
 * `Gate::authorize('create', ...)` já validou no controller antes de chegar aqui. Sem
 * sessão autenticada (ex.: chamada via `tinker`/console), o evento simplesmente não
 * dispara — não há ator para registrar.
 */
final class CriarRma
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
    public function criar(array $dados): Rma
    {
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
            id: null,
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

        $criado = $this->repositorio->criar($rmaNormalizado);

        if (Auth::user() !== null) {
            RmaCriado::dispatch(Auth::user(), $criado);
        }

        return $criado;
    }
}
