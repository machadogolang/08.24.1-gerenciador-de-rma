<?php

namespace App\Parceiros\Aplicacao;

use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * P5 — RMAs associados a um parceiro para a tela de detalhe. A associação considera
 * as FKs reais do schema V3 (`cliente_id`, `fabricante_id`, `fornecedor_id`) e o
 * destinatário polimórfico (`destinatario_type`/`destinatario_id`) quando o parceiro
 * participa do encaminhamento. A tela nunca monta query: tudo passa por este caso de
 * uso, com o tenant já escopado pelo model `Rma`.
 */
final class ListarRmasDeParceiro
{
    public function listar(Model $parceiro): Collection
    {
        $id = (int) $parceiro->getKey();

        return Rma::query()
            ->where(function ($consulta) use ($parceiro, $id): void {
                match (true) {
                    $parceiro instanceof Cliente => $this->comoCliente($consulta, $id),
                    $parceiro instanceof Fabricante => $this->comoFabricante($consulta, $id),
                    $parceiro instanceof Fornecedor => $this->comoFornecedor($consulta, $id),
                    $parceiro instanceof AssistenciaTecnica => $this->comoDestinatario(
                        $consulta,
                        AssistenciaTecnica::class,
                        $id,
                    ),
                    default => null,
                };
            })
            ->latest('created_at')
            ->limit(50)
            ->get();
    }

    private function comoCliente($consulta, int $id): void
    {
        $consulta->where('cliente_id', $id);
    }

    private function comoFabricante($consulta, int $id): void
    {
        $consulta
            ->where('fabricante_id', $id)
            ->orWhere(fn ($destinatario) => $this->comoDestinatario($destinatario, Fabricante::class, $id));
    }

    private function comoFornecedor($consulta, int $id): void
    {
        $consulta
            ->where('fornecedor_id', $id)
            ->orWhere(fn ($destinatario) => $this->comoDestinatario($destinatario, Fornecedor::class, $id));
    }

    private function comoDestinatario($consulta, string $tipo, int $id): void
    {
        $consulta
            ->where('destinatario_type', $tipo)
            ->where('destinatario_id', $id);
    }
}
