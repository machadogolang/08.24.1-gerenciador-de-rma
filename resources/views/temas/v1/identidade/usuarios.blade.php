@extends('temas.v1.layout')

@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    <table class="Tabelinha-Table tabela-usuarios-v1">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Endereço de e-mail</th>
                <th>Permissão</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $indice => $usuario)
                <tr class="{{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                    <td>{{ $usuario->name }}</td>
                    <td class="usuariosemail">{{ $usuario->email }}</td>
                    <td>
                        <form class="form-usuario-v1" method="POST" action="{{ route('identidade.usuarios.update', $usuario) }}">
                            @csrf
                            @method('PUT')
                            <select name="papel" class="formSelectPanel" aria-label="Permissão de {{ $usuario->name }}">
                                @foreach (\App\Identidade\Dominio\Papel::cases() as $papel)
                                    <option value="{{ $papel->name }}" @selected($usuario->papel === $papel)>{{ $papel->name }}</option>
                                @endforeach
                            </select>
                            <button class="formButtonEnviarPanel" type="submit">SALVAR</button>
                        </form>
                    </td>
                    <td>
                        <form class="form-usuario-v1" method="POST" action="{{ route('identidade.usuarios.resetar-senha', $usuario) }}">
                            @csrf
                            <input class="formInputPanel" type="password" name="nova_senha" placeholder="Nova senha" required>
                            <input class="formInputPanel" type="password" name="nova_senha_confirmation" placeholder="Confirmar" required>
                            <button class="formButtonEnviarPanel" type="submit">RESETAR</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
