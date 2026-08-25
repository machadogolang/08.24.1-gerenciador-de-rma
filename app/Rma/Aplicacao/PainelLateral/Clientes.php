<?php

namespace App\Rma\Aplicacao\PainelLateral;

use App\Models\Cliente;
use App\Models\Rma;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Status;
use Illuminate\Support\Collection;

/**
 * CP19 — `15.8.1/banco.php:926` (`right_clientes()`): `GROUP BY cliente WHERE
 * (status='recebido' OR 'encaminhado') AND marcarestoque=0 AND (origem='Cliente' OR
 * 'Licitação')`. `Rma` (Eloquent) não tem relação `cliente()` própria — resolvido por
 * mapa de nomes, mesmo padrão de `ListagensPorStatusController::mapaDeFabricantes()`.
 *
 * @return Collection<int, array{nome: string, contagem: int}>
 */
final class Clientes
{
    public function listar(): Collection
    {
        $registros = Rma::query()
            ->whereIn('status', [Status::Recebido, Status::Encaminhado])
            ->where('marcarestoque', false)
            ->whereIn('origem', [Origem::Cliente->value, Origem::Licitacao->value])
            ->whereNotNull('cliente_id')
            ->get();

        $nomes = Cliente::query()
            ->whereIn('id', $registros->pluck('cliente_id')->unique())
            ->pluck('nome', 'id');

        return $registros
            ->groupBy('cliente_id')
            ->map(fn ($grupo, $clienteId) => [
                'nome' => isset($nomes[$clienteId]) ? mb_substr($nomes[$clienteId], 0, 16) : '',
                'contagem' => $grupo->count(),
            ])
            ->filter(fn ($grupo) => $grupo['nome'] !== '')
            ->values();
    }
}
