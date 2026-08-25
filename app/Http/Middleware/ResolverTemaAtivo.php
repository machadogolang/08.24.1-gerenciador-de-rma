<?php

namespace App\Http\Middleware;

use App\Identidade\Dominio\TemaPreferido;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fase 8 — resolve qual tema visual (`v1`/`v2`) a requisição atual deve renderizar e
 * compartilha `temaAtivo` com todas as views. Não decide Controller/rota — o mesmo
 * Controller (Fases 1-7) responde independente do tema; só a view escolhida por
 * `view_do_tema()` muda (ver `app/Support/view_do_tema.php`).
 *
 * Prioridade de resolução:
 * 1. Tema FORÇADO pela rota (`routes/tema-{v1,v2}.php`, usado para QA/comparação
 *    visual e pelos testes de smoke `RenderizaTemaV{1,2}Test` — permite visitar
 *    `/v1/...`/`/v2/...` independente da preferência salva do usuário).
 * 2. `tema_preferido` do usuário autenticado (decisão de produto 2026-08-25: SEMPRE
 *    respeitada, sem exceção — não existe mais "login próprio de TEMA V1" que ignore
 *    essa preferência).
 * 3. Fallback `TemaPreferido::V2` (equivalente ao login-gateway compartilhado do
 *    legado, que não tem tema próprio antes da autenticação).
 */
final class ResolverTemaAtivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $nomeDaRota = $request->route()?->getName() ?? '';

        $temaForcado = match (true) {
            str_starts_with($nomeDaRota, 'v1.') => TemaPreferido::V1,
            str_starts_with($nomeDaRota, 'v2.') => TemaPreferido::V2,
            default => null,
        };

        $tema = $temaForcado ?? ($request->user()?->tema_preferido ?? TemaPreferido::V2);

        $request->attributes->set('temaAtivo', $tema);
        View::share('temaAtivo', $tema);

        return $next($request);
    }
}
