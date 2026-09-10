<?php

namespace App\Identidade\Aplicacao;

use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Models\TentativaDeAcesso;
use Illuminate\Support\Carbon;

/**
 * PAR15-USR-001 / PAR15-DATA-001 / PAR15-DATA-002 - projeta "QT Login" e
 * "Ultimo login" a partir de `tentativas_de_acesso`, a fonte moderna de verdade
 * para autenticacao.
 *
 * O Legacy `15.8.1/subp/usuarios.php` lia `usuario.quantidade_login` e
 * `usuario.ultimo_login`; essas colunas nao foram migradas (decisao arquitetural
 * registrada). Em vez de duplicar estado, a tela V2 deriva do log:
 *
 * - QT Login = COUNT(tentativas permitidas daquele usuario);
 * - Ultimo login = MAX(created_at) das tentativas permitidas.
 *
 * O dado historico exato anterior a migracao nao e reconstruivel (ver
 * PAR15-DATA-005/006 na matriz forense); isto e projecao do dado atual.
 */
final class ResumoDeAcessoDosUsuarios
{
    /**
     * @return array<int, array{quantidade: int, ultimo: Carbon|null}>
     */
    public function porUsuario(): array
    {
        return TentativaDeAcesso::query()
            ->where('resultado', ResultadoDeAcesso::Permitido->name)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as quantidade, MAX(created_at) as ultimo')
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(fn ($linha) => [
                (int) $linha->user_id => [
                    'quantidade' => (int) $linha->quantidade,
                    'ultimo' => $linha->ultimo !== null ? Carbon::parse($linha->ultimo) : null,
                ],
            ])
            ->all();
    }

    /**
     * @return array{quantidade: int, ultimo: Carbon|null}
     */
    public function deUsuario(int $usuarioId): array
    {
        return $this->porUsuario()[$usuarioId] ?? ['quantidade' => 0, 'ultimo' => null];
    }
}
