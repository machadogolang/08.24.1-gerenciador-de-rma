<?php

namespace App\Http\Middleware;

use App\Identidade\Dominio\TemaPreferido;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fase 8 - resolve qual tema visual renderizar e compartilha `temaAtivo` com as
 * views. V1/V2 seguem a preferencia salva; V3 so e alcancado por rota prefixada
 * `/v3/...` (QA oculto, nunca pelo seletor publico).
 */
final class ResolverTemaAtivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $nomeDaRota = $request->route()?->getName() ?? '';

        $temaForcado = match (true) {
            str_starts_with($nomeDaRota, 'v1.') => TemaPreferido::V1,
            str_starts_with($nomeDaRota, 'v2.') => TemaPreferido::V2,
            str_starts_with($nomeDaRota, 'v3.') => TemaPreferido::V3,
            default => null,
        };

        $tema = $temaForcado ?? ($request->user()?->tema_preferido ?? TemaPreferido::V2);

        $request->attributes->set('temaAtivo', $tema);
        View::share('temaAtivo', $tema);

        return $next($request);
    }
}
