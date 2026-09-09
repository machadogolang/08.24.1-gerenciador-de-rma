{{-- CP20 (paridade visual V2) - fonte real legacy-source/15.8.1/page/pesquisar.php
+ subp/pesquisar_rma.php: breadcrumb de tipo, titulo "Pesquisar:", campo unico e
+ resultado. page/inicio.php inclui este MESMO arquivo por inteiro - nao sao duas
+ telas diferentes, por isso o V3 usa um unico partial para as abas #inicio/#pesquisar. --}}
<div class="boxtop-subpage">
    <div class="submenu-subpage">
        @include('temas.v2.rma._breadcrumb_pesquisar')
    </div>
    <h3 class="box-subpage fl">Pesquisar:</h3>
    <div style="clear:both;"></div>
</div>

<form method="GET" action="{{ rota_tema('rmas.index') }}" class="navbar-form navbar-left" role="search">
    <input type="hidden" name="tipo" value="{{ $tipo }}">
    <div class="form-group">
        <input type="text" id="pesquisa" name="valor" class="formInputSearch form-control InputSeek" placeholder="Search" value="{{ $valor }}" autofocus>
    </div>
    <button type="submit" class="btn btn-default buttonSearch">Enviar pesquisa</button>
</form>
<div style="clear:both;"></div>

@include('temas.v2.rma._tabela_pesquisa')
