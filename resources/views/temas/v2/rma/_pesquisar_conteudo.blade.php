{{-- CP20 (paridade visual V2) — fonte real `legacy-source/15.8.1/page/pesquisar.php`
+ `subp/pesquisar_rma.php`: breadcrumb de tipo, título "Pesquisar:", campo único e
resultado. `page/inicio.php` inclui este MESMO arquivo por inteiro — não são duas
telas diferentes, por isso o V3 usa um único partial para as abas #inicio/#pesquisar. --}}
<div class="boxtop-subpage">
    @include('temas.v2.rma._breadcrumb_pesquisar')
</div>

<form method="GET" action="{{ rota_tema('rmas.index') }}" class="navbar-form navbar-left" role="search">
    <input type="hidden" name="tipo" value="{{ $tipo }}">
    <div class="form-group">
        <input type="text" name="valor" class="formInputSearch form-control InputSeek" placeholder="Search" value="{{ $valor }}" autofocus>
    </div>
    <button type="submit" class="btn buttonSearch">Enviar pesquisa</button>
</form>
<div style="clear:both;"></div>

@include('temas.v2.rma._tabela_pesquisa')
