<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'RMA' }} — CellSystem RMA</title>
    @vite(['resources/js/temas/v1.js'])
</head>
<body>
    @php
        $painelSessao = request()->routeIs(
            'v1.parceiros.*',
            'v1.identidade.usuarios.*',
            'rmas.controle.*',
            'rmas.credito.*',
            'rmas.relatorios.*',
        );
        // VIS-V1-007 — `/rmas/create` (fallback funcional de VIS-V1-002) não tinha
        // heading `<h1>` no runtime original (`menujs-top/novo.php` começa pelo
        // ícone/texto próprios, ver `_form_novo.blade.php`); o H1 de `#CONTEUDO`
        // continua no DOM (acessibilidade/semântica), só fica visualmente oculto
        // aqui — não é uma auditoria tela-a-tela de VIS-V1-007, só o impacto direto
        // encontrado nesta correção.
        $ocultarTituloVisual = request()->routeIs('rmas.create', 'v1.rmas.create');
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
                {{-- VIS-V1-002 — `NovoMaximize()` original só expande `#JS-Novo`
                (abaixo) sem navegar; `href` continua apontando pra rota real como
                fallback funcional (sem JS, ou clique do meio/ctrl-clique), mas o
                clique normal é interceptado por `v1.js`. --}}
                <li id="menu-novo" class="menu-up {{ request()->routeIs('v1.rmas.create') ? 'active' : '' }}">
                    <a href="{{ rota_tema('rmas.create') }}">Novo</a>
                </li>
                <li class="menu-up"><a href="{{ rota_tema('rmas.index') }}#localizar">Localizar</a></li>
                <li class="menu-up {{ request()->routeIs('rmas.entrada') ? 'active' : '' }}">
                    <a href="{{ route('rmas.entrada') }}">Entrada</a>
                </li>
                <li class="menu-up {{ request()->routeIs('rmas.encaminhados') ? 'active' : '' }}">
                    <a href="{{ route('rmas.encaminhados') }}">Encaminhado</a>
                </li>
                <li class="menu-up {{ request()->routeIs('rmas.aguardando-credito') ? 'active' : '' }}">
                    <a href="{{ route('rmas.aguardando-credito') }}">Aguardando credito</a>
                </li>
                <li class="menu-up {{ request()->routeIs('rmas.concluidos') ? 'active' : '' }}">
                    <a href="{{ route('rmas.concluidos') }}">Concluido!</a>
                </li>
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
                        <a class="lisessao" href="{{ route('rmas.controle.index') }}">Controle</a>
                    @endcan
                    <a class="lisessao" href="{{ route('rmas.credito.index') }}">Créditos</a>
                    <a class="lisessao" href="{{ route('rmas.relatorios.rcd') }}">Relatórios</a>
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

            {{-- VIS-V1-002 — equivalente a `inc/menuright.php` (`#JS-Novo`, sempre no
            DOM, oculto por `style="display:none;"`, `NovoMaximize()` só troca pra
            `block`). Presente em toda página do TEMA V1 (não só `/rmas/create`) para
            que "Novo" abra o formulário sem perder o conteúdo da tela atual embaixo —
            ver `_form_novo.blade.php`. Fabricantes vêm de `View::composer` scoped a
            essa view (`AppServiceProvider::boot()`), mesmo padrão do legado (a query
            de `listar_nome_de_fabricantes()` roda em toda carga de página, painel
            oculto ou não). --}}
            @unless ($ocultarTituloVisual)
                <div class="JS-Novo tam" id="JS-Novo" style="display:none;">
                    @include('temas.v1.rma._form_novo')
                </div>
            @endunless

            @unless ($painelSessao)
                <div id="CONTEUDO">
                    <h1 class="titulo-v1 {{ $ocultarTituloVisual ? 'sr-only' : '' }}">{{ $titulo ?? '' }}</h1>
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
