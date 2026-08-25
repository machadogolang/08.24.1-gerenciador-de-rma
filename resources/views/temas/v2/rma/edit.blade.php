@extends('temas.v2.layout')

@section('conteudo')
    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ rota_tema('rmas.update', ['rma' => $registro->id]) }}" class="form-horizontal">
        @csrf
        @method('PUT')
        @include('temas.v2.rma._campos', ['registro' => $registro, 'fabricantes' => $fabricantes, 'fornecedores' => $fornecedores])
        <button type="submit" class="btn formSubmit">Salvar</button>
    </form>
@endsection
