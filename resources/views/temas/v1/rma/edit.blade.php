@extends('temas.v1.layout')

@section('conteudo')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ rota_tema('rmas.update', ['rma' => $registro->id]) }}">
        @csrf
        @method('PUT')
        @include('temas.v1.rma._campos', ['registro' => $registro, 'fabricantes' => $fabricantes, 'fornecedores' => $fornecedores])
        <button type="submit" class="buttonSave">Salvar</button>
    </form>
@endsection
