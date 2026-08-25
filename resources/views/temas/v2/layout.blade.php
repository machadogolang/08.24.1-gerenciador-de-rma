<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'RMA' }} — CellSystem RMA</title>
    @vite(['resources/js/temas/v2.js'])
</head>
<body>
    @php
        // CP17 (`docs/produto/plano-execucao-paridade-v2.md`) — fonte real
        // `legacy-source/15.8.1/inc/menu.php`: os 7 primeiros itens (Inicio…
        // Concluido) usam âncora + `data-toggle="tab"` só quando `!isset($page)`
        // (isto é, na página estática/índice); em qualquer outra página o legado usa
        // `href` real. V3 ainda não tem páginas próprias para
        // Entrada/Recebido/Encaminhado/Concluido (são painéis de aba dentro de
        // `v2.rmas.index`, não rotas — ver `temas/v2/rma/index.blade.php`), então
        // fora do índice o link volta para lá com a âncora; `v2.js` abre a aba certa
        // lendo `location.hash` no load (equivalente moderno, sem reload).
        $emIndice = request()->routeIs('v2.rmas.index');
        $urlIndice = route('v2.rmas.index');
    @endphp
    <header class="header-v2">
        <div class="header-v2__wrapper">
            <ul class="nav nav-tabs nav-v2">
                <li class="iniciocolor {{ $emIndice ? 'active' : '' }}">
                    <a href="{{ $emIndice ? '#inicio' : $urlIndice }}" @if ($emIndice) data-toggle="tab" @endif>Inicio</a>
                </li>
                <li class="iniciocolor">
                    <a href="{{ $emIndice ? '#pesquisar' : $urlIndice.'#pesquisar' }}" @if ($emIndice) data-toggle="tab" @endif>Pesquisar</a>
                </li>
                <li>
                    <a href="{{ $emIndice ? '#novo_rma' : $urlIndice.'#novo_rma' }}" @if ($emIndice) data-toggle="tab" @endif>Novo</a>
                </li>
                <li>
                    <a href="{{ $emIndice ? '#entrada' : $urlIndice.'#entrada' }}" @if ($emIndice) data-toggle="tab" @endif>Entrada</a>
                </li>
                <li>
                    <a href="{{ $emIndice ? '#recebido' : $urlIndice.'#recebido' }}" @if ($emIndice) data-toggle="tab" @endif>Recebido</a>
                </li>
                <li>
                    <a href="{{ $emIndice ? '#encaminhado' : $urlIndice.'#encaminhado' }}" @if ($emIndice) data-toggle="tab" @endif>Encaminhado</a>
                </li>
                <li>
                    <a href="{{ $emIndice ? '#concluido' : $urlIndice.'#concluido' }}" @if ($emIndice) data-toggle="tab" @endif>Concluido</a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Menu</a>
                    <ul class="dropdown-menu" style="color:#FFF;">
                        {{-- Creditos/Relatorios/Controle não têm rota própria por tema (mesma
                        rota canônica servida para V1 e V2, ver `routes/web.php`) — usar
                        `route()` direto, `rota_tema()` só resolve `v1.*`/`v2.*`. --}}
                        <li class="lidropdown menuz"><a href="{{ route('rmas.credito.index') }}">Creditos</a></li>
                        <li class="lidropdown"><a href="{{ rota_tema('parceiros.assistencias-tecnicas.index') }}">Assistencias</a></li>
                        <li class="lidropdown menuz"><a href="{{ rota_tema('parceiros.fabricantes.index') }}">Fabricantes</a></li>
                        <li class="lidropdown"><a href="{{ rota_tema('parceiros.fornecedores.index') }}">Fornecedores</a></li>
                        <li class="lidropdown menuz"><a href="{{ rota_tema('parceiros.clientes.index') }}">Clientes</a></li>
                        <li class="lidropdown"><a href="{{ route('rmas.relatorios.rcd') }}">Relatorios</a></li>
                        {{-- [GAP] "Anotacoes" era página própria em `15.8.1/page/anotacoes.php`;
                        V3 só tem o widget de anotação pessoal (`identidade.perfil.anotacao.update`,
                        sem página de listagem dedicada) — aponta para o perfil até essa
                        página existir. --}}
                        <li class="lidropdown menuz"><a href="{{ rota_tema('identidade.perfil.show') }}">Anotacoes</a></li>
                        @can('gerenciar', \App\Models\User::class)
                            <li class="lidropdown"><a href="{{ route('rmas.controle.index') }}">Controle</a></li>
                            {{-- Usuários não está no dropdown histórico do 15.8.1, mas precisa
                            estar alcançável em algum lugar do TEMA V2 — mesmo critério já usado
                            no TEMA V1 (achado VIS-V1-008). --}}
                            <li class="lidropdown menuz"><a href="{{ rota_tema('identidade.usuarios.index') }}">Usuários</a></li>
                        @endcan
                        <li class="lidropdown">
                            <form method="POST" action="{{ route('tema.alternar') }}">
                                @csrf
                                <button type="submit" class="link-como-item-dropdown">Trocar p/ 14.6.1</button>
                            </form>
                        </li>
                    </ul>
                </li>
                <li class="logoutx">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="link-como-item-dropdown">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <div class="shell-v2">
        <div class="container">
            @if (session('status'))
                <p class="centrodeavisos">{{ session('status') }}</p>
            @endif

            @yield('conteudo')
        </div>

        <aside class="shell-v2__sidebar upmenuright" id="menuright">
            {{-- CP19 do plano de paridade V2 — as 14 seções de
            `legacy-source/15.8.1/inc/rightmenu.php` (DEU ENTRADA HOJE, RECEBIDOS,
            ENCAMINHADOS, LAST 10 CONCLUIDOS, DESTINATARIOS, TRANSPORTE P/ PORTO A,
            URGENTE, PENDENTE CREDITO, CREDITO DISPONIVEL, FABRICANTES,
            FORNECEDORES, CLIENTES, PRODUTOS DE CLIENTE, TODOS PRODUTOS) ainda não
            foram portadas — este checkpoint (CP16/CP17) só garante a geometria
            correta da coluna. --}}
        </aside>
    </div>

    <p class="designedby container">Designed by <a href="http://scripting.com.br" target="_blank"><strong>Scripting Studios Art</strong></a></p>
    <p class="designedby container">Cópia licenciada para <strong>Cellsystem LTDA</strong></p>
</body>
</html>
