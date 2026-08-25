<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'RMA' }} — CellSystem RMA</title>
    @vite(['resources/js/temas/v2.js'])
</head>
<body>
    <nav class="navbar" style="background-color:#18354B;margin-bottom:0;border-radius:0;">
        <div class="container">
            <ul class="nav navbar-nav breadcrumb" style="margin-bottom:0;">
                <li><a href="{{ rota_tema('rmas.index') }}">RMAs</a></li>
                <li><a href="{{ rota_tema('parceiros.clientes.index') }}">Clientes</a></li>
                <li><a href="{{ rota_tema('parceiros.fabricantes.index') }}">Fabricantes</a></li>
                <li><a href="{{ rota_tema('parceiros.fornecedores.index') }}">Fornecedores</a></li>
                <li><a href="{{ rota_tema('parceiros.assistencias-tecnicas.index') }}">Assistências</a></li>
                @can('gerenciar', \App\Models\User::class)
                    <li><a href="{{ rota_tema('identidade.usuarios.index') }}">Usuários</a></li>
                @endcan
                <li class="active"><a href="{{ rota_tema('identidade.perfil.show') }}">{{ auth()->user()?->name }}</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" style="margin-top:8px;">
                        @csrf
                        <button type="submit" class="formSubmit">Sair</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container box-content" style="margin-top:15px;padding:15px;">
        <h1>{{ $titulo ?? '' }}</h1>

        @if (session('status'))
            <p class="centrodeavisos">{{ session('status') }}</p>
        @endif

        @yield('conteudo')
    </div>

    <p class="designedby container">TEMA V2 — CellSystem RMA (reconstrução V3)</p>
    {{-- Rodapé real do legado (`15.8.1/inc/rodape.php`) — texto estático de identidade
    visual, reproduzido como está (não é dado dinâmico). --}}
    <p class="designedby container">Designed by <a href="http://scripting.com.br" target="_blank"><strong>Scripting Studios Art</strong></a></p>
    <p class="designedby container">Cópia licenciada para <strong>Cellsystem LTDA</strong></p>
</body>
</html>
