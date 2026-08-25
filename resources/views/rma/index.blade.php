<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} — CellSystem RMA</title>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    <p><a href="{{ route('rmas.create') }}">Novo RMA</a></p>

    <form method="GET" action="{{ route('rmas.index') }}">
        <label>Buscar por
            <select name="tipo">
                <option value="texto" @selected($tipo === 'texto')>Texto</option>
                <option value="serial" @selected($tipo === 'serial')>Serial</option>
                <option value="nota_fiscal" @selected($tipo === 'nota_fiscal')>Nota fiscal</option>
            </select>
        </label>
        <input type="text" name="valor" value="{{ $valor }}">
        <button type="submit">Buscar</button>
    </form>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Descrição</th>
                <th>Defeito</th>
                <th>Origem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rmas as $registro)
                <tr>
                    <td>{{ $registro->id }}</td>
                    <td>{{ $registro->descricao }}</td>
                    <td>{{ $registro->defeito }}</td>
                    <td>{{ $registro->origem }}</td>
                    <td>
                        <a href="{{ route('rmas.show', $registro->id) }}">Ver</a>
                        <a href="{{ route('rmas.edit', $registro->id) }}">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
