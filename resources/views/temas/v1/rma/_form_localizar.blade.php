{{-- CP7 (fase 2, `plano-execucao-paridade-visual-v1-fase2.md`) — painel "Localizar" do
TEMA V1, fonte real `menujs-top/localizar.php` (lido por inteiro). Ordem real do HTML:
input(fl) + 3 blocos `float:right` na ordem botão/campo/solução — com `float:right`,
cada bloco novo entra à ESQUERDA do anterior, então a ordem visual final (medida no
Legacy, CMP-V1-2-002) é: input, select SOLUÇÃO, select CAMPO, botão FILTRAR.

Partial compartilhado, incluído uma vez em `temas.v1.layout` (dentro de `#JS-Localizar`,
oculto por padrão, igual ao padrão já usado por `#JS-Novo`/`_form_novo.blade.php`).

`campo`/`solucao` traduzidos na camada de apresentação (`RmaController::index()`) para
`CriterioDeBusca` — ver o mapeamento e os `[GAP]` documentados lá; esta partial só
preserva rótulos/valores/ordem literais do `<select>` histórico, não decide o que cada
opção realmente filtra. --}}
<form method="GET" action="{{ rota_tema('rmas.index') }}">
    <div class="fl">
        <input class="JSformLocalizarInput" type="text" name="valor" maxlength="50" value="{{ $valor ?? '' }}">
    </div>
    <div class="fr">
        <button class="JSformLocalizarButton" type="submit">FILTRAR</button>
    </div>
    <div class="fr">
        <select class="JSformLocalizarSelect" name="campo">
            <option value="TUDO" @selected(($campo ?? 'TUDO') === 'TUDO')>TODOS OS CAMPOS</option>
            <option value="os" @selected(($campo ?? '') === 'os')>ORDEM DE SERVICO</option>
            <option value="fabricante" @selected(($campo ?? '') === 'fabricante')>FABRICANTE</option>
            <option value="descricao" @selected(($campo ?? '') === 'descricao')>DESCRICAO</option>
            <option value="SNPNSNID" @selected(($campo ?? '') === 'SNPNSNID')>S/N, P/N OR ID/SNID/ETC</option>
            <option value="modelo" @selected(($campo ?? '') === 'modelo')>MODELO</option>
            <option value="origem" @selected(($campo ?? '') === 'origem')>ORIGEM</option>
            <option value="empresa" @selected(($campo ?? '') === 'empresa')>EMPRESA</option>
            <option value="cliente" @selected(($campo ?? '') === 'cliente')>CLIENTE</option>
            <option value="rastreio_ida" @selected(($campo ?? '') === 'rastreio_ida')>CODIGO DE RASTREIO</option>
            <option value="protocolo" @selected(($campo ?? '') === 'protocolo')>PROTOCOLO</option>
            <option value="NF" @selected(($campo ?? '') === 'NF')>NF</option>
            <option value="destinatario" @selected(($campo ?? '') === 'destinatario')>DESTINATARIO</option>
            <option value="numero" @selected(($campo ?? '') === 'numero')>CHAVE</option>
        </select>
    </div>
    <div class="fr">
        <select class="JSformLocalizarSelect" name="solucao">
            <option value="%" @selected(($solucao ?? '%') === '%')>QUALQUER UMA SOLUCAO</option>
            <option value="GERADO CREDITO" @selected(($solucao ?? '') === 'GERADO CREDITO')>GERADO CREDITO</option>
            <option value="SEM GARANTIA" @selected(($solucao ?? '') === 'SEM GARANTIA')>SEM GARANTIA</option>
            <option value="REPARO" @selected(($solucao ?? '') === 'REPARO')>REPARO</option>
            <option value="TROCA DO PRODUTO" @selected(($solucao ?? '') === 'TROCA DO PRODUTO')>TROCA DO PRODUTO</option>
            <option value="TROCA DE PECA INTERNA" @selected(($solucao ?? '') === 'TROCA DE PECA INTERNA')>TROCA DE PECA INTERNA</option>
            <option value="DEVOLUCAO DO PRODUTO" @selected(($solucao ?? '') === 'DEVOLUCAO DO PRODUTO')>DEVOLUCAO DO PRODUTO</option>
            <option value="REEMBOLSO DO DINHEIRO" @selected(($solucao ?? '') === 'REEMBOLSO DO DINHEIRO')>REEMBOLSO DO DINHEIRO</option>
            <option value="REPARO PELO RMA" @selected(($solucao ?? '') === 'REPARO PELO RMA')>REPARO PELO RMA</option>
            <option value="TESTADO TUDO OK" @selected(($solucao ?? '') === 'TESTADO TUDO OK')>TESTADO TUDO OK</option>
            <option value="ORCAMENTO PAGO" @selected(($solucao ?? '') === 'ORCAMENTO PAGO')>ORCAMENTO PAGO</option>
            <option value="PROCON" @selected(($solucao ?? '') === 'PROCON')>PROCON</option>
        </select>
    </div>
    <div class="both"></div>
</form>
