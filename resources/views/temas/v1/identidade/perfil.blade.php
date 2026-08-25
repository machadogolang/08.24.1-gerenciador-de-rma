@extends('temas.v1.layout')

@section('conteudo')
    <p>{{ $usuario->name }} — {{ $usuario->email }} — papel: {{ $usuario->papel->name }}</p>

    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
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

        <table class="tablenovo">
            <tr>
                <td><label for="senha_atual">Senha atual</label></td>
                <td><input class="novo_formInput" id="senha_atual" type="password" name="senha_atual" required></td>
            </tr>
            <tr>
                <td><label for="nova_senha">Nova senha</label></td>
                <td><input class="novo_formInput" id="nova_senha" type="password" name="nova_senha" required></td>
            </tr>
            <tr>
                <td><label for="nova_senha_confirmation">Confirmar nova senha</label></td>
                <td><input class="novo_formInput" id="nova_senha_confirmation" type="password" name="nova_senha_confirmation" required></td>
            </tr>
        </table>

        <button type="submit" class="buttonSave">Trocar senha</button>
    </form>

    <h2>Anotação pessoal <span class="pmo" data-pmo-alvo="#anotacao-textarea">(mostrar/ocultar)</span></h2>
    <form method="POST" action="{{ route('identidade.perfil.anotacao.update') }}">
        @csrf
        @method('PUT')

        <textarea id="anotacao-textarea" name="anotacao" rows="5" cols="40">{{ old('anotacao', $usuario->anotacao) }}</textarea>

        <button type="submit" class="buttonSave">Salvar anotação</button>
    </form>
@endsection
