@extends('temas.v1.layout')

@section('conteudo')
    <div class="JS-Novo tam">
        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ rota_tema('rmas.store') }}">
            @csrf
            @include('temas.v1.rma._campos', ['registro' => null, 'fabricantes' => $fabricantes, 'fornecedores' => $fornecedores])
            <button type="submit" class="buttonSave">Salvar</button>
        </form>
    </div>
@endsection
