{{-- VIS-V1-002/003/004 — painel "Novo" do TEMA V1, fonte real `menujs-top/novo.php`
(+ `inc/menuright.php`, que envolve esse include num `<div id="JS-Novo" style="display:
none;">`). Estrutura de 5 colunas / 5 linhas fixa (`.tablenovo`, 700px) — NÃO é a tabela
vertical de 2 colunas de `_campos.blade.php` (essa continua servindo só a Edição, cuja
composição real do legado é outra tela, `detalhes.php`, fora do escopo desta correção).

Partial compartilhado, incluído uma vez em `temas.v1.layout` (dentro de `#JS-Novo`,
oculto por padrão) e também por `create.blade.php` (rota `/rmas/create`, fallback
funcional). POST normal para `rmas.store` — nenhum caso de uso novo, nenhum fetch/AJAX.

Campos do legado não reproduzidos aqui, por classificação explícita (VIS-V1-003):
- `Fornecedor`: NÃO existe em `menujs-top/novo.php` — é campo que só o V3/V2 moderno
  adicionou (`fornecedor_id`); mantê-lo aqui tornaria o TEMA V1 visualmente diferente do
  runtime original só porque o domínio evoluiu. Continua disponível na Edição/V2.
- `Fabricante` continua `<select fabricante_id>` (FK), não o `<input list>` de texto
  livre do legado — mudança já existente no domínio (não é uma lacuna desta correção;
  reverter para texto livre exigiria um caso de uso de resolução por nome que não
  existe hoje para Fabricante, diferente do Cliente que já tem `EncontrarOuCriarCliente`).
- `Descricao`/`Origem`/`Modelo`/`Empresa` eram `<input list="...">` com sugestões
  carregadas do banco no legado; aqui viram input simples — perda de autocomplete, sem
  impacto no dado persistido/estrutura, fora do critério de aceite (estrutura/geometria/
  campos, não autocomplete). --}}
<p class="novoIconTitleTop fl"><img src="{{ asset('images/tema-v1/novo.png') }}" width="50" height="50" alt=""></p>
<p class="title-comicone fl">Voce pode adicionar um boletim de defeito para o Setor de RMA</p>
<hr class="both">
<br>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $erro)
            <li>{{ $erro }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('rmas.store') }}">
    @csrf
    <button class="formButtonEnviarNovo fr" type="submit">CRIAR BD</button>
    <table class="tablenovo">
        <tr>
            <td width="7%">Descricao:</td>
            <td width="21%"><input class="novo_formInput" type="text" name="descricao" value="{{ old('descricao') }}" maxlength="255" required></td>
            <td width="12%"><div style="margin-left:10px;">Origem:</div></td>
            <td width="21%"><input class="novo_formInput" type="text" name="origem" value="{{ old('origem') }}" maxlength="255"></td>
            <td width="8%"><div style="margin-left:10px;">NF C:</div></td>
            <td width="14%"><input class="novo_formInputSmall" type="text" name="nfcompra" value="{{ old('nfcompra') }}" maxlength="255"></td>
            <td width="4%"><div style="margin-left:10px;">DATA:</div></td>
            <td width="14%"><input class="novo_formInputDATE" type="date" name="nfcompra_emissao" value="{{ old('nfcompra_emissao') }}"></td>
        </tr>
        <tr>
            <td>SNID:</td>
            <td><input class="novo_formInput" type="text" name="snid" value="{{ old('snid') }}" maxlength="255"></td>
            <td><div style="margin-left:10px;">Fabricante:</div></td>
            <td>
                <select class="novo_formInput" name="fabricante_id">
                    <option value="">—</option>
                    @foreach ($fabricantes as $fabricante)
                        <option value="{{ $fabricante->id }}" @selected(old('fabricante_id') == $fabricante->id)>{{ $fabricante->nome }}</option>
                    @endforeach
                </select>
            </td>
            <td><div style="margin-left:10px;">NF V:</div></td>
            <td><input class="novo_formInputSmall" type="text" name="nfvenda" value="{{ old('nfvenda') }}" maxlength="255"></td>
            <td><div style="margin-left:10px;">DATA:</div></td>
            <td><input class="novo_formInputDATE" type="date" name="nfvenda_emissao" value="{{ old('nfvenda_emissao') }}"></td>
        </tr>
        <tr>
            <td>S/N:</td>
            <td><input class="novo_formInput" type="text" name="sn" value="{{ old('sn') }}" maxlength="255"></td>
            <td><div style="margin-left:10px;">Modelo:</div></td>
            <td><input class="novo_formInput" type="text" name="modelo" value="{{ old('modelo') }}" maxlength="255"></td>
            <td><div style="margin-left:10px;">P/N:</div></td>
            <td><input class="novo_formInputSmall" type="text" name="pn" value="{{ old('pn') }}" maxlength="255"></td>
            <td><div style="margin-left:10px;">OS:</div></td>
            <td><input class="novo_formInputSmall" type="text" name="os" value="{{ old('os') }}" maxlength="255"></td>
        </tr>
        <tr>
            <td>Empresa:</td>
            <td><input class="novo_formInput" type="text" name="empresa" value="{{ old('empresa') }}" maxlength="255"></td>
            <td><div style="margin-left:10px;">Defeito:</div></td>
            <td colspan="5"><input class="novo_defeito" type="text" name="defeito" value="{{ old('defeito') }}" maxlength="255" required></td>
        </tr>
        <tr>
            <td>Cliente:</td>
            <td><input class="novo_formInput" type="text" name="cliente_nome" value="{{ old('cliente_nome') }}" maxlength="255"></td>
            <td style="font-family:Arial;"><div style="margin-left:10px;">OBS:</div></td>
            <td colspan="5"><input class="formInputObservacao" type="text" name="observacao" value="{{ old('observacao') }}" maxlength="255"></td>
        </tr>
    </table>

    <div style="padding:5px 0;clear:both;">
        <input type="checkbox" id="marcarestoque" name="marcarestoque" value="1" @checked(old('marcarestoque', true))>
        <label for="marcarestoque">O ITEM E DO ESTOQUE</label>
    </div>
</form>
<p class="both"></p>
