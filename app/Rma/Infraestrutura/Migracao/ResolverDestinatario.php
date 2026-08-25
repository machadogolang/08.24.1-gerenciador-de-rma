<?php

namespace App\Rma\Infraestrutura\Migracao;

use App\Models\AssistenciaTecnica;
use App\Models\Fabricante;
use App\Models\Fornecedor;

/**
 * `INV-RMA-06` §7 — cascata `assistencia_tecnica → fornecedor → fabricante`, mesma
 * ordem do legado, comparação normalizada (trim + case-insensitive, correção sobre o
 * bug de comparação exata do legado). **Sem auto-criação** — diferente de
 * `cliente`/`fabricante`/`fornecedor` (que usam `EncontrarOuCriar*` com criação
 * automática): o legado também nunca auto-cria para `destinatario`. Quando nenhuma das
 * 3 bate, devolve `null` — quem chama preserva o nome bruto em
 * `destinatario_nome_legado` e registra a linha no relatório.
 */
final class ResolverDestinatario
{
    /**
     * @return array{type: class-string, id: int}|null
     */
    public function resolver(?string $nomeDigitado): ?array
    {
        if ($nomeDigitado === null || trim($nomeDigitado) === '') {
            return null;
        }

        $nomeNormalizado = mb_strtolower(trim(preg_replace('/\s+/', ' ', $nomeDigitado)));

        $assistencia = AssistenciaTecnica::query()
            ->whereRaw('LOWER(TRIM(nome)) = ?', [$nomeNormalizado])
            ->first();

        if ($assistencia !== null) {
            return ['type' => AssistenciaTecnica::class, 'id' => $assistencia->id];
        }

        $fornecedor = Fornecedor::query()
            ->whereRaw('LOWER(TRIM(nome)) = ?', [$nomeNormalizado])
            ->first();

        if ($fornecedor !== null) {
            return ['type' => Fornecedor::class, 'id' => $fornecedor->id];
        }

        $fabricante = Fabricante::query()
            ->whereRaw('LOWER(TRIM(nome)) = ?', [$nomeNormalizado])
            ->first();

        if ($fabricante !== null) {
            return ['type' => Fabricante::class, 'id' => $fabricante->id];
        }

        return null;
    }
}
