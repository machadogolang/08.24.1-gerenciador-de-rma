<?php

namespace App\Rma\Dominio\Eventos;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * `LEG-RMA-045` (`naopermitido()`). Disparado por `RmaPolicy::update()` (Fase 3)
 * explicitamente antes de devolver `false`, quando um usuário sem
 * `Papel::podeGravar()` tenta editar/gravar um RMA. Autorização continua decidindo só
 * `true`/`false`; este evento é a responsabilidade explícita e testável de notificar a
 * tentativa negada — não um side-effect escondido dentro da Policy (decisão registrada
 * em `design.md`). Sem `rma` no payload: a Policy decide por classe (`RmaEloquent::class`),
 * não por instância — mesmo padrão de `RmaPolicy::update(User $ator): bool`.
 */
final class TentativaDeGravacaoNaoPermitida
{
    use Dispatchable;

    public function __construct(
        public readonly User $ator,
    ) {}
}
