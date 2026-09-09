{{-- CP20 (paridade visual V2) - fonte real legacy-source/15.8.1/page/pesquisar.php
+ subp/pesquisar_rma.php. O menu-subp fica DENTRO de submenu-subpage (alinhado a
+ direita no Legacy); o titulo Pesquisar: fica fora, como filho direto de
+ boxtop-subpage. --}}
<div class="menu-subp">
    <ol class="breadcrumb">
        <li class="{{ $tipo === 'texto' ? 'active' : '' }}">
            <a href="{{ rota_tema('rmas.index', ['tipo' => 'texto', 'valor' => $valor]) }}">Qualquer campo</a>
        </li>
        <li class="{{ $tipo === 'nota_fiscal' ? 'active' : '' }}">
            <a href="{{ rota_tema('rmas.index', ['tipo' => 'nota_fiscal', 'valor' => $valor]) }}">Nota fiscal</a>
        </li>
        <li class="{{ $tipo === 'serial' ? 'active' : '' }}">
            <a href="{{ rota_tema('rmas.index', ['tipo' => 'serial', 'valor' => $valor]) }}">Número de série</a>
        </li>
    </ol>
</div>
