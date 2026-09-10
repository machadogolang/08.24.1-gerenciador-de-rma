@extends('temas.v2.layout')

@section('conteudo')
    {{-- PAR15-USR-001/PAR15-USR-006 - superficie dedicada "Mudar permissao" do TEMA
    V2 (fonte `15.8.1/subp/mudar_permissao.php`, acionada pelo icone da tabela de
    usuarios). O Legacy usava select -1/1/2; aqui o select usa o enum `Papel` moderno
    (rotulo historico via `rotuloDePermissaoLegado()`) e o envio e PUT seguro com
    CSRF/Policy/tenant. Nao altera V1 nem V3. --}}
    <ol class="breadcrumb submenutitulo">
        <li class="fl"><img alt="Controle" style="margin-top:-2px;" title="Mudar permissao" src="{{ asset('images/rma/permissao3.png') }}" width="20" height="20"/></li>
        <li class="fl" style="margin-top:0px;">Mudar permissao</li>
        <li style="clear:both;"></li>
    </ol>

    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    @php
        $empresaIdPermissao = app(\App\Compartilhado\Tenant\ContextoDeTenant::class)->empresaId();
        $papelAtual = $empresaIdPermissao !== null
            ? ($usuario->papelNaEmpresa($empresaIdPermissao) ?? $usuario->papel)
            : $usuario->papel;
    @endphp

    <div class="col-md-6">
        <form action="{{ route('identidade.usuarios.update', $usuario) }}" method="post">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="permissao">
                    <img style="margin-top:-2px;" src="{{ asset('images/rma/editar.png') }}" width="18" alt="">
                    Qual a nova permissao para {{ $usuario->name }} ?
                </label>
                <select style="clear:both;" class="form-control formSelect" name="papel" id="permissao" required>
                    @foreach (\App\Identidade\Dominio\Papel::cases() as $papel)
                        <option value="{{ $papel->name }}" @selected($papelAtual === $papel)>
                            {{ $papel->rotuloDePermissaoLegado() }} ({{ $papel->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-default" style="float:left;margin-top:15px;">MUDAR</button>
        </form>
    </div>
    <div style="clear:both;"></div>
@endsection
