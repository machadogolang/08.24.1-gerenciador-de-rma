<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Meu perfil — CellSystem RMA</title>
</head>
<body>
    <h1>Meu perfil</h1>

    <p>{{ $usuario->name }} — {{ $usuario->email }} — papel: {{ $usuario->papel->name }}</p>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('tema.alternar') }}">
        @csrf
        <button type="submit">Alternar tema (atual: {{ $usuario->tema_preferido->value }})</button>
    </form>

    <h2>Trocar senha</h2>
    <form method="POST" action="{{ route('identidade.perfil.senha.update') }}">
        @csrf
        @method('PUT')

        <div>
            <label for="senha_atual">Senha atual</label>
            <input id="senha_atual" type="password" name="senha_atual" required>
        </div>

        <div>
            <label for="nova_senha">Nova senha</label>
            <input id="nova_senha" type="password" name="nova_senha" required>
        </div>

        <div>
            <label for="nova_senha_confirmation">Confirmar nova senha</label>
            <input id="nova_senha_confirmation" type="password" name="nova_senha_confirmation" required>
        </div>

        <button type="submit">Trocar senha</button>
    </form>

    <h2>Anotação pessoal</h2>
    <form method="POST" action="{{ route('identidade.perfil.anotacao.update') }}">
        @csrf
        @method('PUT')

        <textarea name="anotacao" rows="5" cols="40">{{ old('anotacao', $usuario->anotacao) }}</textarea>

        <button type="submit">Salvar anotação</button>
    </form>
</body>
</html>
