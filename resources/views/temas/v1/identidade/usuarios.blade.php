@extends('temas.v1.layout')

{{-- PAR14-USR-001 - contrato real do TEMA V1 (`14.6.1/menujs-right/usuarios.php`):
NOME, ENDERECO DE E-MAIL, PERMISSAO e N LOGIN, sem controles inline por linha. As
acoes administrativas do V1 continuam na organizacao propria do Controle; o N LOGIN e
projecao de `tentativas_de_acesso` (`ResumoDeAcessoDosUsuarios`), sem coluna
duplicada. Nao copia a organizacao do V2. --}}
@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    <div style="display:block;clear:both;">
        <table width="100%" class="Tabelinha-Table tabela-usuarios-v1" style="margin-bottom:0px;">
            <thead>
                <tr class="SuperTr">
                    <th width="25%">NOME</th>
                    <th width="35%">ENDERECO DE E-MAIL</th>
                    <th width="30%">PERMISSAO</th>
                    <th width="10%">N LOGIN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $indice => $usuario)
                    @php
                        $papelDoUsuario = $empresa_id !== null
                            ? ($usuario->papelNaEmpresa($empresa_id) ?? $usuario->papel)
                            : $usuario->papel;
                        $resumoDoUsuario = $resumoDeAcesso[$usuario->id] ?? [];
                        $qtLoginDoUsuario = $resumoDoUsuario['quantidade'] ?? 0;
                    @endphp
                    <tr class="{{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}" style="height:30px;">
                        <td style="text-align:center;padding:5px;">{{ $usuario->name }}</td>
                        <td class="usuariosemail" style="text-align:center;padding:5px;">{{ $usuario->email }}</td>
                        <td style="text-align:center;padding:5px;">{{ $papelDoUsuario->rotuloDePermissaoLegado() }}</td>
                        <td style="text-align:center;padding:5px;">{{ $qtLoginDoUsuario }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
