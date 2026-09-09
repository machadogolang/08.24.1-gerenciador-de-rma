<?php

namespace App\Http\Middleware;

use App\Compartilhado\Tenant\ContextoDeTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EVO-SAAS-001 (S4) — resolve a empresa ativa do usuário autenticado e define o
 * `ContextoDeTenant`:
 *
 * 1. sem usuário autenticado (login/guest) → contexto vazio e segue;
 * 2. exatamente um vínculo ativo → usa esse vínculo (sem seletor);
 * 3. vários vínculos ativos → usa a empresa escolhida na sessão se ela pertencer ao
 *    usuário, senão a primeira vínculo ativo (backend multi-vínculo; UI de seletor é
 *    `[DECISAO-PENDENTE]`);
 * 4. nenhum vínculo ativo → falha explícita com 403 (S4.6).
 */
final class ResolverTenantAtivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();
        if ($usuario === null) {
            return $next($request);
        }

        $contexto = app(ContextoDeTenant::class);

        $vinculos = $usuario->empresas()
            ->wherePivot('ativo', true)
            ->get();

        if ($vinculos->isEmpty()) {
            abort(403, 'Conta sem empresa ativa.');
        }

        $empresaEscolhida = $vinculos->first();
        $empresaDaSessao = (int) $request->session()->get('empresa_ativa_id', 0);

        if ($empresaDaSessao > 0) {
            $correspondente = $vinculos->firstWhere('id', $empresaDaSessao);
            if ($correspondente !== null) {
                $empresaEscolhida = $correspondente;
            }
        }

        $contexto->definir($empresaEscolhida);

        return $next($request);
    }
}
