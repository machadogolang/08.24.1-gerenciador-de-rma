<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'RMA' }} — CellSystem RMA</title>
    @vite(['resources/js/temas/v1.js'])
</head>
<body>
    @php
        $painelSessao = request()->routeIs('v1.parceiros.*', 'v1.identidade.usuarios.*');
    @endphp

    <div id="FIXADO">
        <div id="TOPO">
            <a class="image-up" href="{{ rota_tema('rmas.index') }}">
                <img src="{{ asset('images/tema-v1/ferramenta-logo.png') }}" height="35" alt="CellSystem RMA">
            </a>
            <ul>
                <li class="menu-up {{ request()->routeIs('v1.rmas.index') ? 'active' : '' }}">
                    <a href="{{ rota_tema('rmas.index') }}">Pag. Inicial</a>
                </li>
                <li class="menu-up {{ request()->routeIs('v1.rmas.create') ? 'active' : '' }}">
                    <a href="{{ rota_tema('rmas.create') }}">Novo</a>
                </li>
                <li class="menu-up"><a href="{{ rota_tema('rmas.index') }}#localizar">Localizar</a></li>
            </ul>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="formButtonSIGNOUT" type="submit">SIGN OUT</button>
            </form>
            <button id="menu-sessao" class="formButtonMENU fr {{ $painelSessao ? 'active' : '' }}"
                type="button" aria-controls="JS-Sessao" aria-expanded="{{ $painelSessao ? 'true' : 'false' }}">
                MENU
            </button>
        </div>
    </div>

    <div id="BASE">
        <div id="MEIO">
            <div class="menuDivSession" id="JS-Sessao" @style(['display:block' => $painelSessao, 'display:none' => ! $painelSessao])>
                <nav class="JS-SessaoRIGHT" aria-label="Cadastros e administração">
                    <a class="lisessao" href="{{ rota_tema('parceiros.fornecedores.index') }}">Fornecedores</a>
                    <a class="lisessao" href="{{ rota_tema('parceiros.fabricantes.index') }}">Fabricantes</a>
                    <a class="lisessao" href="{{ rota_tema('parceiros.assistencias-tecnicas.index') }}">Assistências</a>
                    <a class="lisessao" href="{{ rota_tema('parceiros.clientes.index') }}">Clientes</a>
                    @can('gerenciar', \App\Models\User::class)
                        <a class="lisessao" href="{{ rota_tema('identidade.usuarios.index') }}">Usuários</a>
                    @endcan
                </nav>
                <div class="JS-SessaoLEFT">
                    <div class="JS-DivLEFT">
                        <h1 class="titulo-v1">{{ $titulo ?? '' }}</h1>
                        @if ($painelSessao)
                            @yield('conteudo')
                        @endif
                    </div>
                </div>
            </div>

            @unless ($painelSessao)
                <div id="CONTEUDO">
                    <h1 class="titulo-v1">{{ $titulo ?? '' }}</h1>
                    @if (session('status'))
                        <p class="centrodeavisos">{{ session('status') }}</p>
                    @endif
                    @yield('conteudo')
                </div>
            @endunless
        </div>

        <div id="RODAPE">
            <p class="p-rodape fl"><strong>Usuário:</strong> {{ auth()->user()?->name }}</p>
            <div class="designedby">Designed by <a href="http://scripting.com.br" target="_blank" rel="noopener"><strong>Scripting Studios Art</strong></a></div>
            <div class="designedby">Cópia licenciada para <strong>Cellsystem LTDA</strong></div>
        </div>
    </div>
</body>
</html>
