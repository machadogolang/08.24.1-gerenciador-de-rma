@extends('temas.v2.layout')

{{-- PAR15-USR-001 - organizacao historica da tela de usuarios do TEMA V2 (fonte
`15.8.1/subp/usuarios.php`): colunas Nome, E-mail, QT Login, Ultimo login,
Permissao e 3 acoes compactas por icone. A arquitetura continua moderna (Policy,
CSRF, superficies dedicadas); o que voltou ao contrato do Legacy foi a composicao
da tela. `QT Login`/`Ultimo login` sao projecao de `tentativas_de_acesso`
(`ResumoDeAcessoDosUsuarios`), nunca coluna duplicada (PAR15-DATA-001/002). --}}
@section('conteudo')
    @include('temas.v2.identidade._menu_controle', ['subpAtual' => 'usuarios'])
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    @php
        $empresaIdUsuarios = $empresa_id ?? null;
    @endphp

    <table class="Tabelinha-Table tabela-usuarios-v2">
        <thead>
            <tr class="SuperTr">
                <th style="width:22%">Nome</th>
                <th style="width:31%">E-mail</th>
                <th style="width:10%">QT Login</th>
                <th style="width:15%">Ultimo login</th>
                <th style="width:18%">Permissao</th>
                <th colspan="3" style="width:6%">Acao</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $indice => $usuario)
                @php
                    $papelDoUsuario = $empresaIdUsuarios !== null
                        ? ($usuario->papelNaEmpresa($empresaIdUsuarios) ?? $usuario->papel)
                        : $usuario->papel;
                    $resumoDoUsuario = $resumoDeAcesso[$usuario->id] ?? [];
                    $qtLoginDoUsuario = $resumoDoUsuario['quantidade'] ?? 0;
                    $ultimoLoginDoUsuario = $resumoDoUsuario['ultimo'] ?? null;
                @endphp
                <tr class="{{ $indice % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}" style="height:30px;">
                    <td class="Tabelinha-TD">{{ $usuario->name }}</td>
                    <td class="Tabelinha-TD usuariosemail">{{ $usuario->email }}</td>
                    <td class="Tabelinha-TD">{{ $qtLoginDoUsuario }}</td>
                    <td class="Tabelinha-TD">{{ $ultimoLoginDoUsuario?->format('d/m/Y') ?? '' }}</td>
                    <td class="Tabelinha-TD">{{ $papelDoUsuario->rotuloDePermissaoLegado() }}</td>
                    <td class="Tabelinha-TD usuarios-acao">
                        <a href="{{ rota_tema('identidade.usuarios.resetar-senha.form', $usuario) }}" title="Resetar senha">
                            <img src="{{ asset('images/rma/senha2.png') }}" alt="Resetar senha" height="22">
                        </a>
                    </td>
                    <td class="Tabelinha-TD usuarios-acao">
                        <a href="{{ rota_tema('identidade.usuarios.permissoes', $usuario) }}" title="Mudar permissao">
                            <img src="{{ asset('images/rma/permissao3.png') }}" alt="Mudar permissao" height="22">
                        </a>
                    </td>
                    <td class="Tabelinha-TD usuarios-acao">
                        <a href="{{ rota_tema('identidade.usuarios.apagar', $usuario) }}" title="Apagar">
                            <img src="{{ asset('images/rma/apagar.png') }}" alt="Apagar" height="22">
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
