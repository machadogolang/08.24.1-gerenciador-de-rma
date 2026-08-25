<?php

namespace App\Rma\Aplicacao;

use App\Models\Rma as RmaEloquent;
use App\Rma\Dominio\Rma;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * `LEG-RMA-041` — RMAs relacionados ao mesmo destinatário/fabricante/fornecedor de um
 * RMA de referência, excluindo ele mesmo. Paginado (o legado não tem `LIMIT`, achado de
 * risco de performance já registrado) — resultado percebido pelo usuário é o mesmo
 * conjunto de dados, só a forma de consumir muda.
 *
 * **Desvio do `design.md`:** o pseudocódigo original usa `orWhere('destinatario_id',
 * $rma->destinatarioId)` incondicionalmente — como o Query Builder do Laravel traduz
 * `where('coluna', null)` para `coluna IS NULL`, um RMA de referência sem
 * destinatário/fabricante/fornecedor casaria com **todo** outro RMA igualmente sem
 * esses campos (falso positivo confirmado em teste manual via `tinker` durante esta
 * fase). Cada condição só entra na query quando o campo correspondente do RMA de
 * referência não é nulo — mesmo conjunto de dados pretendido ("RMAs que compartilham
 * alguma referência real"), sem o efeito colateral do `IS NULL` genérico.
 */
final class BoletinsRelacionados
{
    public function listar(Rma $rma, int $porPagina = 20): LengthAwarePaginator
    {
        return RmaEloquent::query()
            ->where('id', '!=', $rma->id)
            ->where(function ($query) use ($rma) {
                $temCondicao = false;

                if ($rma->destinatarioId !== null) {
                    $query->where('destinatario_id', $rma->destinatarioId);
                    $temCondicao = true;
                }

                if ($rma->fabricanteId !== null) {
                    $temCondicao
                        ? $query->orWhere('fabricante_id', $rma->fabricanteId)
                        : $query->where('fabricante_id', $rma->fabricanteId);
                    $temCondicao = true;
                }

                if ($rma->fornecedorId !== null) {
                    $temCondicao
                        ? $query->orWhere('fornecedor_id', $rma->fornecedorId)
                        : $query->where('fornecedor_id', $rma->fornecedorId);
                    $temCondicao = true;
                }

                if (! $temCondicao) {
                    // RMA de referência sem nenhum dos 3 campos: nada é "relacionado".
                    $query->whereRaw('1 = 0');
                }
            })
            ->paginate($porPagina);
    }
}
