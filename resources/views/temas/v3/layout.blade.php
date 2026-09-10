@php
    $rotaAtual = request()->route()?->getName() ?? '';
    $itensNavegacao = [
        ['rotulo' => 'Dashboard', 'rota' => 'v3.dashboard', 'ativo' => str_starts_with($rotaAtual, 'v3.dashboard')],
        ['rotulo' => 'RMAs', 'rota' => 'v3.rmas.index', 'ativo' => str_starts_with($rotaAtual, 'v3.rmas')],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'CellSystem RMA' }}</title>
    @vite(['resources/js/temas/v3.js'])
</head>
<body>
    <div class="app-shell">
        <header class="app-shell__topbar">
            <button type="button"
                class="app-shell__menu-button"
                data-v3-drawer-abrir
                aria-controls="v3-drawer"
                aria-expanded="false">
                Menu
            </button>
            <a class="app-shell__brand" href="{{ route('v3.dashboard') }}">CellSystem RMA</a>
            <span>{{ auth()->user()?->name }}</span>
            <form class="app-shell__logout" method="POST" action="{{ route('logout') }}">
                
                <button type="submit">Sair</button>
            </form>
        </header>

        <div class="app-shell__body">
            <aside class="app-shell__rail" aria-label="Navegacao principal" data-v3-rail-conteudo>
                <nav aria-label="Dominios">
                    @foreach ($itensNavegacao as $item)
                        <a class="app-shell__nav-link"
                            href="{{ route($item['rota']) }}"
                            @if ($item['ativo']) aria-current="page" @endif>
                            {{ $item['rotulo'] }}
                        </a>
                    @endforeach
                    {{-- AD-22 - sai do prefixo /v3 e volta para a rota canonica,
                    que resolve de novo o tema persistido (V1 ou V2). --}}
                    <a class="app-shell__nav-link app-shell__voltar-sistema" href="{{ route('rmas.index') }}">Voltar ao sistema</a>
                </nav>
                <button type="button" class="app-shell__rail-toggle" data-v3-rail>Recolher menu</button>
            </aside>

            <main id="conteudo" class="app-shell__main">
                @if (session('status'))
                    <p role="status" class="cartao estado-vazio" style="text-align:left;margin-bottom:16px;">
                        {{ session('status') }}
                    </p>
                @endif
                @yield('conteudo')
            </main>
        </div>

        <div class="app-shell__drawer" data-v3-drawer id="v3-drawer" aria-label="Menu de navegacao">
            <button type="button" class="app-shell__drawer-backdrop" data-v3-drawer-backdrop aria-label="Fechar menu"></button>
            <div class="app-shell__drawer-panel" role="dialog" aria-modal="true" aria-label="Navegacao">
                <button type="button" class="app-shell__rail-toggle" data-v3-drawer-fechar>Fechar menu</button>
                <nav aria-label="Dominios">
                    @foreach ($itensNavegacao as $item)
                        <a class="app-shell__nav-link"
                            href="{{ route($item['rota']) }}"
                            @if ($item['ativo']) aria-current="page" @endif>
                            {{ $item['rotulo'] }}
                        </a>
                    @endforeach
                    {{-- AD-22 - sai do prefixo /v3 e volta para a rota canonica,
                    que resolve de novo o tema persistido (V1 ou V2). --}}
                    <a class="app-shell__nav-link app-shell__voltar-sistema" href="{{ route('rmas.index') }}">Voltar ao sistema</a>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
