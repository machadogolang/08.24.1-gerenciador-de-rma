<?php

namespace App\Rma\Aplicacao\Destinatarios;

use App\Models\AssistenciaTecnica;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * UX-003/P7 - selecao validada do destinatario de encaminhamento.
 *
 * O Legacy (`14.6.1/page/detalhes.php:186`, `15.8.1/page/rma.php:489`) escolhia o
 * destinatario por TEXTO livre + `<datalist>` de nomes. O V3 tolerava "tipo + id cru"
 * e confiava no navegador. Aqui a superficie passa a ser UMA selecao com
 * `value="tipo:id"` e o servidor revalida tudo:
 *
 * - tipo permitido (whitelist, nunca FQCN vindo do HTML);
 * - existencia da entidade;
 * - vinculo com o tenant ativo (o Global Scope de `PertenceATenant` filtra pela
 *   empresa do `ContextoDeTenant`, entao id de outra empresa nao e encontrado).
 *
 * Nenhuma regra de negocio nova: o caso de uso `EncaminharRma` continua recebendo
 * `class-string` + id resolvidos no servidor.
 */
final class OpcoesDeDestinatario
{
    /** @var array<string, class-string<Model>> */
    public const TIPOS = [
        'assistencia_tecnica' => AssistenciaTecnica::class,
        'fornecedor' => Fornecedor::class,
        'fabricante' => Fabricante::class,
    ];

    /**
     * Opcoes por tipo, prontas para o `<select>` (somente o tenant ativo).
     *
     * @return array<string, list<array{valor: string, nome: string}>>
     */
    public function agrupadas(): array
    {
        $opcoes = [];

        foreach (self::TIPOS as $slug => $classe) {
            $opcoes[$slug] = $classe::query()
                ->orderBy('nome')
                ->get()
                ->map(fn ($registro) => [
                    'valor' => $slug.':'.$registro->id,
                    'nome' => (string) $registro->nome,
                ])
                ->all();
        }

        return $opcoes;
    }

    /**
     * Resolve `tipo:id` para `[class-string, id]`, revalidando tipo, existencia e
     * tenant. Entrada invalida/estrangeira gera erro de validacao (422), nunca escrita.
     *
     * @return array{0: class-string<Model>, 1: int}
     */
    public function resolver(?string $valor): array
    {
        $partes = explode(':', (string) $valor, 2);
        $classe = self::TIPOS[$partes[0] ?? ''] ?? null;

        if ($classe === null || count($partes) !== 2 || ! ctype_digit($partes[1]) || (int) $partes[1] < 1) {
            throw ValidationException::withMessages([
                'destinatario' => 'Selecione um destinatario valido.',
            ]);
        }

        $id = (int) $partes[1];

        if ($classe::query()->whereKey($id)->doesntExist()) {
            throw ValidationException::withMessages([
                'destinatario' => 'Destinatario inexistente para a empresa ativa.',
            ]);
        }

        return [$classe, $id];
    }
}
