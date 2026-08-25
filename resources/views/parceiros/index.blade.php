<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} — CellSystem RMA</title>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    <p><a href="{{ route('parceiros.' . $tipo . '.create') }}">Novo</a></p>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Cidade/UF</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $registro)
                <tr>
                    <td>{{ $registro->nome }}</td>
                    <td>{{ $registro->cidade }}{{ $registro->uf ? '/' . $registro->uf->value : '' }}</td>
                    <td>
                        <a href="{{ route('parceiros.' . $tipo . '.edit', $registro) }}">Editar</a>
                        <form method="POST" action="{{ route('parceiros.' . $tipo . '.destroy', $registro) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
