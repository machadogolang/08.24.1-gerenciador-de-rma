@extends('temas.v2.layout')

@section('conteudo')
    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    {{-- PAR-V2-NOVO-01 - fallback de rota /create usa o mesmo formulario inline
    da aba Novo do Tema V2 (15.8.1), sem formulario vertical duplicado. --}}
    @include('temas.v2.rma._form_novo_v2')
@endsection
