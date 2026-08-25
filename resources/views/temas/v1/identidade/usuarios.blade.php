@extends('temas.v1.layout')

@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    <table class="Tabelinha-Table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Papel</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $indice => $usuario)
                <tr class="{{ $indice % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}">
                    <td>{{ $usuario->name }}</td>
                    <td class="usuariosemail">{{ $usuario->email }}</td>
                    <td>
                        <form method="POST" action="{{ route('identidade.usuarios.update', $usuario) }}">
                            @csrf
                            @method('PUT')
                            <select name="papel" class="formSelect">
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
@endsection
