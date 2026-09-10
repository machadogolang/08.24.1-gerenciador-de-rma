@extends('temas.v2.layout')

@section('conteudo')
    {{-- PAR-RES-E-04 - o /perfil do TEMA V2 segue existindo como rota moderna/
    compatibilidade (identidade + troca de tema), mas NAO concentra mais
    perfil+senha+anotacao numa unica tela. A organizacao volta a do Legacy:
    Anotacoes em `page/anotacoes.php` e Alterar senha em `subp/senha.php`, cada uma
    com superficie propria acessivel pelos atalhos abaixo. --}}
    <p>{{ $usuario->name }} - {{ $usuario->email }} - papel: {{ $usuario->papelAtivo()->name }}</p>

    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('tema.alternar') }}">
        @csrf
        <button type="submit" class="btn formSubmit">Alternar tema (atual: {{ $usuario->tema_preferido->value }})</button>
    </form>

    <ul class="perfil-v2-atalhos">
        <li><a href="{{ rota_tema('identidade.anotacoes.index') }}">Quadro de Anotacoes</a></li>
        <li><a href="{{ rota_tema('identidade.perfil.senha') }}">Alterar senha</a></li>
    </ul>
@endsection
