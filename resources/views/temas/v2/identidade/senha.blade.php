@extends('temas.v2.layout')

@section('conteudo')
    @include('temas.v2.identidade._menu_controle', ['subpAtual' => 'senha'])
    {{-- PAR-RES-E-04 - "Alterar senha" como superficie separada do TEMA V2 (fonte
    Legacy `15.8.1/subp/senha.php`, subpagina de Controle). O legado trocava a senha
    sem exigir a atual; aqui mantemos `TrocarPropriaSenha` (senha atual + confirmacao
    + `min:8`), mais seguro, e o POST/CSRF moderno. Nao altera V1 nem V3. --}}
    <ol class="breadcrumb submenutitulo">
        <li class="fl"><img alt="Controle" style="margin-top:-2px;" title="Alterar senha" src="{{ asset('images/rma/senha2.png') }}" width="20" height="20"/></li>
        <li class="fl" style="margin-top:0px;">Alterar senha</li>
        <li style="clear:both;"></li>
    </ol>

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

    <div class="col-md-6">
        <form action="{{ route('identidade.perfil.senha.update') }}" method="post">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label style="float:left;color:#EEE;" for="senha_atual">Senha atual</label>
                <input style="clear:both;" type="password" class="form-control Input1" name="senha_atual" id="senha_atual" placeholder="senha atual" required>
            </div>
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="nova_senha">Nova Senha</label>
                <input style="clear:both;" type="password" class="form-control Input1" name="nova_senha" id="nova_senha" placeholder="nova senha" required>
            </div>
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="nova_senha_confirmation">Confirmar nova senha</label>
                <input style="clear:both;" type="password" class="form-control Input1" name="nova_senha_confirmation" id="nova_senha_confirmation" placeholder="confirmar nova senha" required>
            </div>

            <button type="submit" class="btn btn-default" style="float:right;">Cadastrar</button>
        </form>
    </div>
    <div style="clear:both;"></div>
@endsection
