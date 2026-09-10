@extends('temas.v2.layout')

@section('conteudo')
    {{-- PAR15-USR-001/PAR15-USR-004 - superficie de confirmacao "Apagar usuario" do
    TEMA V2 (fonte `15.8.1/subp/apagar_usuario.php`). Preserva a organizacao do Legacy
    (icone -> pagina de confirmacao) sem reproduzir o hard delete cego: apagar um
    usuario cascatearia `modificacoes_de_rma` (auditoria). A acao definitiva segue como
    decisao de produto/seguranca (PAR15-USR-004 / VIS-V1-012). Nao altera V1 nem V3. --}}
    <ol class="breadcrumb submenutitulo">
        <li class="fl"><img alt="Controle" style="margin-top:-2px;" title="Apagar" src="{{ asset('images/rma/apagar.png') }}" width="20" height="20"/></li>
        <li class="fl" style="margin-top:0px;">Apagar usuario</li>
        <li style="clear:both;"></li>
    </ol>

    <div class="col-md-6">
        <div class="form-group">
            <input style="display:none;" type="text" class="form-control" value="{{ $usuario->id }}" name="id_usuario" disabled>
            <div style="float:left;color:#EEE;">
                <img style="margin-top:-2px;" src="{{ asset('images/rma/apagar.png') }}" width="18" alt="">
                Tem certeza que deseja apagar o usuario <strong>{{ $usuario->name }}</strong> ?
            </div>
        </div>
        <div style="clear:both;"></div>
        <p style="color:#EEE;margin-top:15px;">
            Exclusao definitiva pendente de decisao de produto/seguranca
            (<code>PAR15-USR-004</code>): o <em>hard delete</em> do Legacy cascatearia a
            auditoria (<code>modificacoes_de_rma</code>). Enquanto a decisao nao existir,
            use "Mudar permissao" para <strong>Bloqueado</strong>.
        </p>
        <button type="button" class="btn btn-default" style="float:left;margin-top:15px;" disabled>APAGAR</button>
        <a href="{{ rota_tema('identidade.usuarios.permissoes', $usuario) }}" class="btn btn-default" style="float:left;margin-top:15px;margin-left:8px;">Mudar permissao</a>
    </div>
    <div style="clear:both;"></div>
@endsection
