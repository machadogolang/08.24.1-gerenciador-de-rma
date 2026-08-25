<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Usuários — CellSystem RMA</title>
</head>
<body>
    <h1>Usuários</h1>

    <p><a href="{{ route('identidade.perfil.show') }}">Meu perfil</a></p>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Papel</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>
                        <form method="POST" action="{{ route('identidade.usuarios.update', $usuario) }}">
                            @csrf
                            @method('PUT')
                            <select name="papel">
                                @foreach (\App\Identidade\Dominio\Papel::cases() as $papel)
                                    <option value="{{ $papel->name }}" @selected($usuario->papel === $papel)>
                                        {{ $papel->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit">Salvar papel</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('identidade.usuarios.resetar-senha', $usuario) }}">
                            @csrf
                            <input type="password" name="nova_senha" placeholder="Nova senha" required>
                            <input type="password" name="nova_senha_confirmation" placeholder="Confirmar" required>
                            <button type="submit">Resetar senha</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
