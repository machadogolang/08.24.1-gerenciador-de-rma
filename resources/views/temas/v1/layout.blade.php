<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'RMA' }} — CellSystem RMA</title>
    @vite(['resources/js/temas/v1.js'])
</head>
<body>
    <div id="FIXADO">
        <div id="TOPO">
            <span class="fl">CellSystem RMA — <span class="acento">TEMA V1</span></span>
            <ul class="fr">
                <li class="fl" style="padding-left:10px;">
                    <a href="{{ rota_tema('identidade.perfil.show') }}">{{ auth()->user()?->name }}</a>
                </li>
                <li class="fl" style="padding-left:10px;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Sair</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div id="BASE">
        <ul class="breadcrumb">
            <li><a href="{{ rota_tema('rmas.index') }}">RMAs</a></li>
            <li><a href="{{ rota_tema('parceiros.clientes.index') }}">Clientes</a></li>
            <li><a href="{{ rota_tema('parceiros.fabricantes.index') }}">Fabricantes</a></li>
            <li><a href="{{ rota_tema('parceiros.fornecedores.index') }}">Fornecedores</a></li>
            <li><a href="{{ rota_tema('parceiros.assistencias-tecnicas.index') }}">Assistências</a></li>
            @can('gerenciar', \App\Models\User::class)
                <li><a href="{{ rota_tema('identidade.usuarios.index') }}">Usuários</a></li>
            @endcan
        </ul>

        <div id="MEIO">
            <div id="CONTEUDO">
                <h1>{{ $titulo ?? '' }}</h1>

                @if (session('status'))
                    <p class="centrodeavisos">{{ session('status') }}</p>
                @endif

                @yield('conteudo')
            </div>
        </div>

        <div id="RODAPE">
            <p class="designedby">TEMA V1 — CellSystem RMA (reconstrução V3)</p>
        </div>
    </div>
</body>
</html>
