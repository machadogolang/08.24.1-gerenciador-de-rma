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
            <h1 class="pagina__titulo">Novo RMA</h1>
            <p class="pagina__resumo">Preencha os dados do boletim para abrir a entrada.</p>
        </header>

        @php
            $formAction = rota_tema('rmas.store');
            $voltarUrl = route('v3.rmas.index');
        @endphp
        @include('temas.v3.rma._form')
    </div>
@endsection
