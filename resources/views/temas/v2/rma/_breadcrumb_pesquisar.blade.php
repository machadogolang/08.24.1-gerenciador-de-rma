{{-- CP20 (paridade visual V2) — fonte real `legacy-source/15.8.1/inc/menu_pesquisar.php`.
"Qualquer campo"/"Nota fiscal"/"Número de série" mapeiam para os 3 critérios já
existentes em `CriterioDeBusca` (texto/nota_fiscal/serial) — nenhuma regra de busca
nova, só a UI histórica (breadcrumb) no lugar do `<select>` genérico que existia
antes. --}}
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
<h3 class="fl">Pesquisar:</h3>
<div style="clear:both;"></div>
