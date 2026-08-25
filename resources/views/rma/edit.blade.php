<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} — CellSystem RMA</title>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('rmas.update', $registro->id) }}">
        @csrf
        @method('PUT')
        @include('rma._campos', ['registro' => $registro])
        <button type="submit">Salvar</button>
    </form>
</body>
</html>
