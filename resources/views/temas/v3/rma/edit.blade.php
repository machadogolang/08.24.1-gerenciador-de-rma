@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina">
        @if ($errors->any())
            <div class="resumo-erros cartao" role="alert" aria-live="assertive">
                <p class="resumo-erros__titulo">Verifique os campos abaixo:</p>
                <ul>
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <header class="pagina__cabecalho">
            <h1 class="pagina__titulo">Editar RMA {{ $numeroExibicao }}</h1>
            <p class="pagina__resumo">{{ $registro->descricao }}</p>
        </header>

        @php
            $formAction = rota_tema('rmas.update', ['rma' => $registro->id]);
            $voltarUrl = route('v3.rmas.show', ['rma' => $registro->id]);
        @endphp
        @include('temas.v3.rma._form')
    </div>
@endsection
