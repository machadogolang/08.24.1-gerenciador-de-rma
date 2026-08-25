<?php

use App\Http\Middleware\ResolverTemaAtivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Fase 8 — rotas prefixadas por tema (`/v1/...`, `/v2/...`), usadas por QA
            // visual/comparação com o LEGACY-RUNTIME e pelos testes de smoke
            // `RenderizaTemaV{1,2}Test`. Reaproveitam os MESMOS Controllers das rotas
            // sem prefixo em `routes/web.php` (nenhuma lógica duplicada) — só forçam o
            // tema resolvido por `ResolverTemaAtivo`, independente de `tema_preferido`.
            Route::middleware('web')->group(base_path('routes/tema-v1.php'));
            Route::middleware('web')->group(base_path('routes/tema-v2.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Fase 8 — resolve o tema ativo (v1/v2) em toda requisição web e compartilha
        // `temaAtivo` com as views; `view_do_tema()` (app/Support/view_do_tema.php)
        // usa esse valor para resolver `temas.{tema}.<view>`.
        $middleware->appendToGroup('web', ResolverTemaAtivo::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
