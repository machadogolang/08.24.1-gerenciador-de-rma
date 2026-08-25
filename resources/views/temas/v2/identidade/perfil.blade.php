@extends('temas.v2.layout')

@section('conteudo')
    <p>{{ $usuario->name }} — {{ $usuario->email }} — papel: {{ $usuario->papel->name }}</p>

    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('tema.alternar') }}">
        @csrf
        <button type="submit" class="btn formSubmit">Alternar tema (atual: {{ $usuario->tema_preferido->value }})</button>
    </form>

    <h2>Trocar senha</h2>
    <form method="POST" action="{{ route('identidade.perfil.senha.update') }}" class="form-horizontal">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="col-sm-2 control-label" for="senha_atual">Senha atual</label>
            <div class="col-sm-4"><input class="form-control" id="senha_atual" type="password" name="senha_atual" required></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="nova_senha">Nova senha</label>
            <div class="col-sm-4"><input class="form-control" id="nova_senha" type="password" name="nova_senha" required></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="nova_senha_confirmation">Confirmar nova senha</label>
            <div class="col-sm-4"><input class="form-control" id="nova_senha_confirmation" type="password" name="nova_senha_confirmation" required></div>
        </div>

        <button type="submit" class="btn formSubmit">Trocar senha</button>
    </form>

    <h2>Anotação pessoal <span class="pmo" data-pmo-alvo="#anotacao-textarea">(mostrar/ocultar)</span></h2>
    <form method="POST" action="{{ route('identidade.perfil.anotacao.update') }}">
        @csrf
        @method('PUT')

        <textarea id="anotacao-textarea" class="form-control" name="anotacao" rows="5" cols="40">{{ old('anotacao', $usuario->anotacao) }}</textarea>

        <button type="submit" class="btn formSubmit">Salvar anotação</button>
    </form>
@endsection
