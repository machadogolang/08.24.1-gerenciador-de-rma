@extends('temas.v3.layout')

@php
    $empresaIdView = $empresa_id ?? null;
    $ator = auth()->user();
    $papelAtor = $ator?->papelAtivo();
@endphp

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Administração - Usuários</h1>
                <p class="pagina__resumo">
                    {{ $usuarios->count() }} {{ $usuarios->count() === 1 ? 'usuário cadastrado' : 'usuários cadastrados' }} na plataforma.
                </p>
            </div>
            @can('gerenciar', \App\Models\User::class)
                <a class="botao pagina__cabecalho-acao" href="{{ rota_tema('identidade.usuarios.create') }}">
                    + Novo usuário
                </a>
            @endcan
        </div>

        @if (session('status'))
            <div class="cartao" style="border-left: 4px solid #177245; background-color: #f4fbf7; margin-bottom: 20px; padding: 12px 16px; color: #177245;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="cartao" style="border-left: 4px solid #b3261e; background-color: #fff8f7; margin-bottom: 20px; padding: 12px 16px; color: #b3261e;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="barra-busca">
            <input class="barra-busca__campo" type="search" id="filtro-usuarios"
                placeholder="Filtrar por nome, e-mail ou papel..."
                aria-label="Filtrar usuários" data-v3-filtro-tabela>
        </div>

        @if ($usuarios->isEmpty())
            <div class="estado-vazio">
                <p>Nenhum usuário cadastrado.</p>
                <p>
                    <a href="{{ rota_tema('identidade.usuarios.create') }}" class="botao botao--secundario">
                        Cadastrar primeiro usuário
                    </a>
                </p>
            </div>
        @else
            <div class="tabela-wrapper">
                <table class="tabela-v3" data-v3-tabela-usuarios>
                    <thead>
                        <tr>
                            <th scope="col">Nome</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">Papel</th>
                            <th scope="col">Último login</th>
                            <th scope="col" style="text-align: right;">Ações contextuais</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            @php
                                $papelDoUsuario = $empresaIdView !== null
                                    ? ($usuario->papelNaEmpresa($empresaIdView) ?? $usuario->papel)
                                    : $usuario->papel;
                                $resumoDoUsuario = $resumoDeAcesso[$usuario->id] ?? [];
                                $qtLoginDoUsuario = $resumoDoUsuario['quantidade'] ?? 0;
                                $ultimoLoginDoUsuario = $resumoDoUsuario['ultimo'] ?? null;
                                $podeOperar = $papelAtor?->podeOperarSobrePapel($papelDoUsuario) ?? false;
                            @endphp
                            <tr data-linha-parceiro>
                                <td>
                                    <strong>{{ $usuario->name }}</strong>
                                </td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    @if ($podeOperar)
                                        <form method="POST" action="{{ rota_tema('identidade.usuarios.update', $usuario) }}"
                                            style="display: flex; gap: 6px; align-items: center;">
                                            @csrf
                                            @method('PUT')
                                            <select name="papel" class="campo__controle" style="min-height: 32px; padding: 2px 8px; font-size: 0.875rem;">
                                                @foreach (\App\Identidade\Dominio\Papel::cases() as $papelCase)
                                                    @if ($papelAtor?->podeOperarSobrePapel($papelCase))
                                                        <option value="{{ $papelCase->name }}" @selected($papelDoUsuario === $papelCase)>
                                                            {{ $papelCase->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button type="submit" class="botao botao--secundario botao--compacto">
                                                Salvar
                                            </button>
                                        </form>
                                    @else
                                        <span class="status-badge">{{ $papelDoUsuario->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $ultimoLoginDoUsuario?->format('d/m/Y H:i') ?? 'Nunca' }}</div>
                                    <div style="font-size: 0.8125rem; color: #5b6b7b;">{{ $qtLoginDoUsuario }} logins</div>
                                </td>
                                <td style="text-align: right;">
                                    @if ($podeOperar)
                                        <details class="acoes-contextuais__detalhe" style="display: inline-block; text-align: left;">
                                            <summary class="botao botao--secundario botao--compacto" style="list-style: none; cursor: pointer;">
                                                Resetar senha
                                            </summary>
                                            <div class="cartao" style="position: absolute; right: 24px; margin-top: 8px; z-index: 10; width: 280px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); padding: 12px;">
                                                <form method="POST" action="{{ rota_tema('identidade.usuarios.resetar-senha', $usuario) }}">
                                                    @csrf
                                                    <div style="margin-bottom: 8px;">
                                                        <label style="font-size: 0.8125rem; font-weight: 600; display: block; margin-bottom: 4px;">Nova senha</label>
                                                        <input type="password" name="nova_senha" required minlength="8" class="campo__controle" style="width: 100%; min-height: 32px; padding: 4px 8px; font-size: 0.875rem;">
                                                    </div>
                                                    <div style="margin-bottom: 12px;">
                                                        <label style="font-size: 0.8125rem; font-weight: 600; display: block; margin-bottom: 4px;">Confirmar senha</label>
                                                        <input type="password" name="nova_senha_confirmation" required minlength="8" class="campo__controle" style="width: 100%; min-height: 32px; padding: 4px 8px; font-size: 0.875rem;">
                                                    </div>
                                                    <button type="submit" class="botao botao--perigo botao--compacto" style="width: 100%;">
                                                        Confirmar redefinição
                                                    </button>
                                                </form>
                                            </div>
                                        </details>
                                    @else
                                        <span style="color: #5b6b7b; font-size: 0.8125rem;">Sem ações</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="cartoes-usuario cartoes-parceiro">
                @foreach ($usuarios as $usuario)
                    @php
                        $papelDoUsuario = $empresaIdView !== null
                            ? ($usuario->papelNaEmpresa($empresaIdView) ?? $usuario->papel)
                            : $usuario->papel;
                        $resumoDoUsuario = $resumoDeAcesso[$usuario->id] ?? [];
                        $qtLoginDoUsuario = $resumoDoUsuario['quantidade'] ?? 0;
                        $ultimoLoginDoUsuario = $resumoDoUsuario['ultimo'] ?? null;
                        $podeOperar = $papelAtor?->podeOperarSobrePapel($papelDoUsuario) ?? false;
                    @endphp
                    <article class="cartao-parceiro" data-linha-parceiro>
                        <div class="cartao-parceiro__cabecalho">
                            <strong>{{ $usuario->name }}</strong>
                            <span class="status-badge">{{ $papelDoUsuario->name }}</span>
                        </div>
                        <p>{{ $usuario->email }}</p>
                        <p style="font-size: 0.875rem; color: #5b6b7b;">
                            Último login: {{ $ultimoLoginDoUsuario?->format('d/m/Y H:i') ?? 'Nunca' }} ({{ $qtLoginDoUsuario }} acessos)
                        </p>

                        @if ($podeOperar)
                            <div class="cartao-parceiro__acoes" style="flex-direction: column; gap: 8px;">
                                <form method="POST" action="{{ rota_tema('identidade.usuarios.update', $usuario) }}"
                                    style="display: flex; gap: 6px; align-items: center; width: 100%;">
                                    @csrf
                                    @method('PUT')
                                    <select name="papel" class="campo__controle" style="flex: 1; min-height: 36px; padding: 4px 8px;">
                                        @foreach (\App\Identidade\Dominio\Papel::cases() as $papelCase)
                                            @if ($papelAtor?->podeOperarSobrePapel($papelCase))
                                                <option value="{{ $papelCase->name }}" @selected($papelDoUsuario === $papelCase)>
                                                    {{ $papelCase->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="submit" class="botao botao--secundario botao--compacto">
                                        Mudar papel
                                    </button>
                                </form>

                                <details class="acoes-contextuais__detalhe" style="width: 100%;">
                                    <summary class="botao botao--secundario botao--compacto" style="width: 100%; text-align: center; cursor: pointer;">
                                        Resetar senha
                                    </summary>
                                    <div class="cartao" style="margin-top: 8px; padding: 12px;">
                                        <form method="POST" action="{{ rota_tema('identidade.usuarios.resetar-senha', $usuario) }}">
                                            @csrf
                                            <div style="margin-bottom: 8px;">
                                                <label style="font-size: 0.8125rem; font-weight: 600; display: block; margin-bottom: 4px;">Nova senha</label>
                                                <input type="password" name="nova_senha" required minlength="8" class="campo__controle" style="width: 100%; min-height: 36px;">
                                            </div>
                                            <div style="margin-bottom: 12px;">
                                                <label style="font-size: 0.8125rem; font-weight: 600; display: block; margin-bottom: 4px;">Confirmar senha</label>
                                                <input type="password" name="nova_senha_confirmation" required minlength="8" class="campo__controle" style="width: 100%; min-height: 36px;">
                                            </div>
                                            <button type="submit" class="botao botao--perigo botao--compacto" style="width: 100%;">
                                                Confirmar redefinição
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
