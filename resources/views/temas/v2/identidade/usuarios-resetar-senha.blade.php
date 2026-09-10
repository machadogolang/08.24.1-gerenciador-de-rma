@extends('temas.v2.layout')

@section('conteudo')
    {{-- PAR15-USR-001/PAR15-USR-005 - superficie dedicada "Resetar senha" do TEMA V2
    (fonte `15.8.1/subp/resetar_senha.php`, acionada pelo icone da tabela de usuarios).
    O Legacy gerava senha aleatoria e enviava por e-mail; aqui o operador define a nova
    senha (min 8 + confirmacao) e o POST seguro `identidade.usuarios.resetar-senha`
    (CSRF/Policy/tenant) segue sendo a unica porta. Nao altera V1 nem V3. --}}
    <ol class="breadcrumb submenutitulo">
        <li class="fl"><img alt="Controle" style="margin-top:-2px;" title="Resetar senha" src="{{ asset('images/rma/senha2.png') }}" width="20" height="20"/></li>
        <li class="fl" style="margin-top:0px;">Resetar senha</li>
        <li style="clear:both;"></li>
    </ol>

    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <div class="col-md-6">
        <form action="{{ route('identidade.usuarios.resetar-senha', $usuario) }}" method="post">
            @csrf
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="nova_senha">
                    <img style="margin-top:-2px;" src="{{ asset('images/rma/editar.png') }}" width="18" alt="">
                    Nova senha para <strong>{{ $usuario->name }}</strong>
                </label>
                <input style="clear:both;" type="password" class="form-control Input1" name="nova_senha" id="nova_senha" placeholder="Nova senha" required>
            </div>
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="nova_senha_confirmation">Confirmar</label>
                <input style="clear:both;" type="password" class="form-control Input1" name="nova_senha_confirmation" id="nova_senha_confirmation" placeholder="Confirmar" required>
            </div>
            <button type="submit" class="btn btn-default" style="float:left;margin-top:15px;">RESETAR</button>
        </form>
    </div>
    <div style="clear:both;"></div>
@endsection
