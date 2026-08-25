Fazer uma investigação dos dois prompts a baixo, e consolidar em uma instrução como um prompt unico do que precisamos fazer de fato.

Detalhe, na comparação da tela do legado com o novo, eu ainda vejo o legado maior a largura da tela, mesmo em mbos com 100% de zoom, isso deve ser investigado também, um pouco sobre isso foi abordado, mas nada concreto foi apresentado como solução.

-----------------------

PROMPT 1

-----------------------

Quero uma nova rodada OBJETIVA de correção de paridade visual do TEMA V1.

NÃO quero investigação ampla.

NÃO quero subagents.

NÃO quero criar outro roadmap.

NÃO quero somente olhar screenshots e ajustar "a olho".

Quero comparar:

```text
LEGADO
http://localhost:8094/14.6.1/

V3 TEMA V1
http://localhost:8095/
```

e corrigir os defeitos concretos abaixo.

Antes:

```bash
cd ~/github/08.24.1-gerenciador-de-rma

git status
git branch --show-current
git log --oneline -15
```

Preserve integralmente os commits recentes de correção da cascata, fontes, tabelas e tela Concluídos.

Não faça push.

Pode fazer commits locais pequenos.

# REGRA ZERO - NÃO CONFUNDIR ZOOM COM DIFERENÇA DE CSS

Os screenshots humanos fornecidos NÃO estão na mesma escala.

O screenshot do legado mostra explicitamente Chrome em:

```text
110%
```

O screenshot do V3 está em:

```text
100%
```

Uma medição externa dos PNGs encontrou:

```text
Tabela Concluídos legado no screenshot: ~860 px físicos
Tabela Concluídos V3 no screenshot:      ~788 px físicos

860 / 788 = 1,091
```

Isso corresponde praticamente ao zoom de 110%.

Normalizando:

```text
860 / 1,10 = 781,8 px
```

contra aproximadamente:

```text
788 px
```

do V3.

A barra superior confirma a mesma relação:

```text
legado screenshot: ~44 px
V3 screenshot:     ~40 px

44 / 1,10 = 40 px
```

Portanto:

NÃO aumente globalmente o V3 em 10%.

NÃO altere `984px` para 1080px ou equivalente.

O código confirma que o legado possui:

```css
#BASE {
    width:984px;
}

#TOPO {
    width:984px;
}

#CONTEUDO {
    width:984px;
}
```

e o V3 atual já porta:

```scss
$largura-fixa-tema-v1: 984px;
```

Primeiro normalize o ambiente.

Use Playwright para abrir LEGADO e V3 com:

```text
mesmo Chromium
mesma viewport
deviceScaleFactor = 1
zoom = 100%
mesma largura e altura
```

Garanta também:

```javascript
document.body.style.zoom === '' ou '1'
window.devicePixelRatio conhecido
```

Capture `getBoundingClientRect()`.

Quero comparar DOM/CSS, não chrome do navegador.

# MEDIÇÃO OBRIGATÓRIA

Crie uma rotina pequena de diagnóstico Playwright que, para cada superfície comparada, capture:

```text
x
y
width
height
```

e computed styles:

```text
font-family
font-size
font-weight
line-height
letter-spacing

margin-top
margin-right
margin-bottom
margin-left

padding-top
padding-right
padding-bottom
padding-left

border widths
box-sizing

color
background-color
display
float
```

para os elementos relevantes.

Não precisa virar framework.

É ferramenta de diagnóstico desta correção.

Para V1 medir pelo menos:

```text
#BASE
#TOPO
#CONTEUDO
#JS-Novo
#JS-Localizar
.Tabelinha-Table
.TableListarFPEF-TR
.Tabelinha-TD
.title-comicone
.menu-up
.quadro/anotação
sidebar de contadores
```

Só depois mexa no CSS.

# VIS-FIX-01 - ENTRADA AINDA ESTÁ ESTRUTURALMENTE ERRADA

Compare:

```text
legacy-source/14.6.1/page/entrada.php
resources/views/temas/v1/rma/entrada.blade.php
```

O legado começa com:

```html
<p class="title-icone fl">
    <img ... entrada.png width="50" height="50">
</p>

<p class="title-comicone fl">
    Os bds recebidos abaixo...
</p>

<hr class="both">
```

O V3 atual ainda renderiza:

```text
Entrada
Os bds recebidos...
```

sem o ícone histórico.

No screenshot isto é claramente visível.

Corrigir Entrada para a mesma estratégia já aplicada em Concluídos:

```text
omitir H1 artificial
restaurar entrada.png 50x50
restaurar title-icone
restaurar title-comicone
restaurar hr.both
```

O H1 pode permanecer semanticamente em modo `sr-only`, se desejável, mas NÃO pode aparecer visualmente.

# VIS-FIX-02 - RESTAURAR LARGURAS EXATAS DE ENTRADA

O legado define explicitamente:

```text
RECEBIDO      8%
ORIGEM       10%
NF C           6%
NF V           6%
FABRICANTE    13%
DESCRICAO     12%
MODELO        18%
S/N           17%
VALOR          6%
OS             4%
```

A view atual de Entrada ainda deixa todas as colunas automáticas.

Aplicar os valores históricos.

Pode usar `colgroup`, como já foi feito em Concluídos.

Não inventar larguras novas.

# VIS-FIX-03 - CORRIGIR REPRESENTAÇÃO DOS DADOS DE ENTRADA

O PHP legado faz regras de apresentação que ainda não estão reproduzidas integralmente.

Restaurar:

```text
NF C = vazio quando <= 0
NF V = vazio quando <= 0

MERCADO LIVRE -> M LIVRE
Mercado Livre -> M LIVRE
Leilão -> LEILAO
Licitação -> LICITACAO

valor positivo -> number_format com "." decimal
valor zero -> vazio
```

Hoje Entrada ainda usa formatação monetária moderna com vírgula.

TEMA V1 deve reproduzir a representação histórica.

Não altere TEMA V2.

# VIS-FIX-04 - ÁREA CLICÁVEL DAS CÉLULAS

No legado os links normalmente envolvem um `<div>` dentro do `<td>`.

Isto faz a região clicável ocupar praticamente a célula inteira.

Não precisa copiar HTML antigo sem necessidade, mas reproduza a mesma área clicável.

Pode usar CSS:

```css
.Tabelinha-TD > a {
    display:block;
    width:100%;
    height:100%;
}
```

ou estrutura equivalente.

Validar pelo computed box.

# VIS-FIX-05 - AUDITAR AS OUTRAS DUAS LISTAGENS COM A MESMA REGRA

Depois de Entrada, revisar SOMENTE:

```text
Encaminhado
Aguardando crédito
```

comparando diretamente:

```text
legacy-source/14.6.1/page/encaminhados.php
legacy-source/14.6.1/page/aguardandocredito.php
```

com seus Blades V1.

Aplicar a elas:

```text
ícone histórico 50x50
ausência do H1 artificial
descrição na posição histórica
hr
larguras exatas por coluna
formatação histórica
classe de linha histórica
```

Não generalizar as quatro telas para uma única regra se o PHP antigo usa classes de linha diferentes.

# VIS-FIX-06 - CONCLUÍDOS AINDA ESTÁ INCOMPLETO

A parte superior de Concluídos melhorou.

Não reescreva o que já está correto.

Porém o Blade atual termina imediatamente depois de:

```html
</table>
```

O legado continua com:

```text
VALOR TOTAL: R$ ...
DATA DO PROCESSAMENTO: ...
Quantidade Total de produtos: ...
Quantidade dos produtos a cima que nao participaram da contagem monetario: ...
```

Restaurar exatamente essa área.

Fonte:

```text
legacy-source/14.6.1/page/concluidos.php
```

Valores necessários:

```text
soma dos valores
quantidade total
quantidade com valor zero
data atual de processamento
```

Não calcular regra no Blade.

Controller/caso de uso/view model deve entregar os dados.

Preservar os textos, inclusive ortografia histórica, no TEMA V1.

Preservar:

```text
VALOR TOTAL à direita
Arial
letter-spacing:2px
font-size:15px nos parágrafos
hr final
margens
```

# VIS-FIX-07 - NÃO FORÇAR A ALTURA DAS LINHAS POR CAUSA DOS DADOS QA

No screenshot V3 de Concluídos aparecem valores como:

```text
OS-QA-00059
OS-QA-00054
EQUIPAMENTO FICTICIO QA 059
BRITO COMERCIAL LTDA.
```

Isso força quebra de linha e aumenta a altura da TR.

O legado apresentado usa valores como:

```text
5947
5479
5185
INTELBRAS
INOVA
CAMERA DOME
```

Não coloque `white-space:nowrap` global apenas para fazer o screenshot ficar menor.

O legado permite quebra quando o conteúdo exige.

Em vez disso, para TESTE VISUAL, prepare uma fixture determinística que possua:

```text
comprimentos de OS semelhantes ao legado
fabricantes de tamanhos semelhantes
modelos semelhantes
descrições curtas e longas semelhantes
serial semelhante
```

Continuar usando somente dados fictícios.

Não copie a base real para o V3.

A fixture visual serve apenas para retirar "tipo de dado" da equação da comparação.

# VIS-FIX-08 - PAINEL NOVO: CHECKBOX ESTÁ COMPLETAMENTE DIFERENTE

No screenshot legado, o controle:

```text
O ITEM E DO ESTOQUE
```

é um toggle grande, verde, com bloco branco deslizante.

No V3 virou checkbox nativo pequeno.

A folha `pattern/15.9.7.css` possui a implementação histórica.

Portar conscientemente:

```css
input[type=checkbox] {
    display:none;
}

input[type=checkbox] + label {
    display:inline-block;
    background-color:#DB574D;
    color:white;
    font-size:12px;
    font-weight:normal;
    height:30px;
    padding-top:10px;
    line-height:20px;
    position:relative;
    text-transform:uppercase;
    width:475px;
}

input[type=checkbox]:checked + label {
    background-color:#67B04F;
}
```

e a geometria histórica do `<i>` deslizante.

Não necessariamente aplicar globalmente se isso puder atingir checkbox que não fazia parte da superfície histórica.

Pode escopar ao TEMA V1/painel Novo mantendo o resultado visual idêntico.

O Blade deve possuir os atributos equivalentes a:

```text
data-text-true="O ITEM E DO ESTOQUE"
data-text-false="ITEM NAO E DO ESTOQUE"
<i></i>
```

Isso também deve corrigir parte da diferença vertical entre os dois painéis.

# VIS-FIX-09 - PAINEL NOVO: DATAS ESTÃO ERRADAS

O legado usa:

```html
<input
    class="novo_formInputDATE"
    type="text"
    placeholder="00/00/2015"
>
```

O V3 mudou para:

```html
type="date"
```

No screenshot V3 aparece:

```text
dd/mm/aaaa
[ícone de calendário]
```

Isso NÃO pertence ao TEMA V1.

Restaurar apresentação histórica:

```text
campo textual
placeholder 00/00/2015
sem widget de calendário nativo
```

A arquitetura moderna pode converter `dd/mm/YYYY` para o formato interno antes da validação/caso de uso.

Não enfraquecer validação.

Separar:

```text
formato da UI V1
formato do domínio/banco
```

# VIS-FIX-10 - PAINEL NOVO: FABRICANTE ESTÁ VISUALMENTE ERRADO

O legado usa:

```html
<input
    class="novo_formInput"
    type="text"
    name="fabricante"
    list="fabricantes"
>
```

O V3 atual deliberadamente trocou isso por:

```html
<select name="fabricante_id">
```

No screenshot isso aparece como um dropdown com `-`.

Agora o critério é fidelidade visual real.

Portanto a decisão antiga deve ser revisada.

Quero no TEMA V1:

```text
input textual/datalist visualmente igual ao legado
```

sem abrir mão da FK moderna.

A camada de apresentação pode enviar o nome e resolver para `fabricante_id` antes do caso de uso.

Não colocar consulta no Blade.

Não alterar a modelagem do banco.

# VIS-FIX-11 - PAINEL NOVO: BOX-SIZING INTRODUZIDO PELO V3

O CSS histórico de:

```text
novo_formInput
novo_formInputDATE
novo_formInputSmall
novo_defeito
formInputObservacao
```

não define:

```css
box-sizing:border-box;
```

O `_v1-base.scss` atual adicionou isso.

Isso altera a largura externa porque o legado usa:

```text
width + padding + border
```

com box model padrão.

Compare computed dimensions no runtime.

Se confirmado, remova o `border-box` ou ajuste de maneira que:

```text
outerWidth legado == outerWidth V3
outerHeight legado == outerHeight V3
```

Não mantenha `border-box` só porque é prática moderna se muda os pixels do TEMA V1.

# VIS-FIX-12 - PÁGINA INICIAL TEM CONTEÚDO ARTIFICIAL

No V3 atual, `rma/index.blade.php` começa com:

```text
RMAs
Novo RMA
Buscar por...
```

O screenshot legado NÃO possui:

```text
RMAs
Novo RMA
```

antes do Localizar.

O H1 vem do layout V3.

O link "Novo RMA" foi adicionado pela view.

Remover visualmente ambos da Pág. Inicial V1.

A rota de Novo continua disponível pelo menu superior, igual ao legado.

Após isto, o painel Localizar deve subir para aproximadamente a mesma distância do header observada no legado.

Na comparação dos screenshots:

```text
legado: Localizar começa praticamente logo abaixo do header
V3: existe cerca de 45-50 px extras antes dele
```

Essa diferença vem do conteúdo artificial, não da largura global.

# VIS-FIX-13 - LOCALIZAR FOI RECONSTRUÍDO COMO OUTRO FORMULÁRIO

Este é um defeito importante.

O legado `menujs-top/localizar.php` possui nesta ordem:

```text
[INPUT DE BUSCA GRANDE]
[QUALQUER UMA SOLUCAO]
[TODOS OS CAMPOS]
[FILTRAR]
```

O V3 atual possui:

```text
Buscar por [Texto]
[input]
[Buscar]
```

Isso não é paridade.

Reconstruir a superfície V1 usando a estrutura histórica.

Preservar funcionalmente as opções existentes no legado:

SOLUÇÃO:

```text
QUALQUER UMA SOLUCAO
GERADO CREDITO
SEM GARANTIA
REPARO
TROCA DO PRODUTO
TROCA DE PECA INTERNA
DEVOLUCAO DO PRODUTO
REEMBOLSO DO DINHEIRO
REPARO PELO RMA
TESTADO TUDO OK
ORCAMENTO PAGO
PROCON
```

CAMPO:

```text
TODOS OS CAMPOS
ORDEM DE SERVICO
FABRICANTE
DESCRICAO
S/N, P/N OR ID/SNID/ETC
MODELO
ORIGEM
EMPRESA
CLIENTE
CODIGO DE RASTREIO
PROTOCOLO
NF
DESTINATARIO
CHAVE
```

Não precisa usar parâmetros HTTP antigos internamente.

Pode criar adapter moderno para `BuscarRmas`.

Mas a superfície V1 deve ser equivalente.

# VIS-FIX-14 - GEOMETRIA EXATA DO LOCALIZAR

Fonte:

```text
pattern/14.6.1.css
```

Restaurar e medir:

```css
.JS-Localizar
min-height:25px
padding:10px
margin-bottom:10px

.JSformLocalizarInput
width:422px
height:30px
padding:10px
font-size:18px
letter-spacing:1px

.JSformLocalizarSelect
margin-left:15px
height:52px
font-size:12px

.JSformLocalizarButton
height:52px
width:100px
margin-left:15px
font-size:14px
letter-spacing:1px
background:#106D78
```

Compare o box model real, pois input utiliza largura + padding conforme CSS histórico.

# VIS-FIX-15 - LOCALIZAR É PAINEL INLINE, NÃO APENAS ÂNCORA

O legado possui:

```javascript
function LocalizarMaximize() {
    document.getElementById("JS-Localizar").style.display = "block";
    document.getElementById("menu-localizar").style.fontWeight = "bold";
}
```

O V3 atualmente aponta para:

```text
/rmas#localizar
```

Reproduzir o comportamento V1:

```text
#JS-Localizar está disponível no DOM
clicar Localizar abre o painel
menu fica bold
não perde a página atual
```

A Pág. Inicial histórica já inicia Localizar aberto.

Manter fallback funcional sem JS se desejar.

Não introduzir jQuery apenas por isso.

# VIS-FIX-16 - QUADRO DE ANOTAÇÕES ESTÁ MUITO MENOR

O screenshot mostra diferença grande.

Legado:

```text
container esquerdo: 675px
margin-left:1px
textarea rows=20
width:674px
padding:5px
font-size:12px
letter-spacing:1px
line-height:1.5
```

V3:

```text
rows=14
```

O V3 ficou muito mais baixo.

Restaurar `rows=20` e a geometria calculada do legado.

Não inventar height manual se `rows=20` + CSS reproduzir corretamente.

# VIS-FIX-17 - TÍTULO DO QUADRO DE ANOTAÇÕES

O legado usa:

```text
panotacao
imganotacao
```

com:

```text
width 664/662 histórico conforme regra + inline
margin-top -16
padding 10
letter-spacing 3px
font-weight bold
ícone deslocado com margins negativas
```

O V3 reduziu isso a:

```text
.quadro-de-anotacoes-titulo {
    font-weight:300;
}
```

Isso é outro componente visual.

Portar o estilo real.

Não criar interpretação nova.

# VIS-FIX-18 - O LEGADO NÃO TEM BOTÃO "SALVAR ANOTAÇÃO"

No screenshot V3 existe:

```text
Salvar anotação
```

No legado não existe botão visível.

O legado salva durante digitação.

Não quero necessariamente copiar o AJAX antigo.

Use arquitetura moderna.

Uma solução aceitável:

```text
input/evento na textarea
debounce
fetch para endpoint existente
CSRF
estado discreto de erro se necessário
```

Mas NÃO coloque botão visual que o legado não possui.

Se existir preocupação de acessibilidade/fallback, mantenha submit não visual ou outro fallback sem alterar a composição histórica.

# VIS-FIX-19 - SIDEBAR DE CONTADORES ESTÁ COM BOX MODEL ERRADO

No legado:

```css
.formLabelStats {
    width:198px;
    padding:5px;
    border:1px;
}

.formInputStats {
    width:45px;
    padding:5px;
    border:1px;
}
```

Não existe `box-sizing:border-box`.

Portanto as larguras históricas são de conteúdo.

O V3 atual adicionou:

```css
box-sizing:border-box;
```

e ainda substituiu o valor por um `<p>`.

Isso encolhe o conjunto.

Restaurar a geometria real.

Preferencialmente reproduzir semanticamente o controle histórico:

```html
<p class="formLabelStats">...</p>
<input class="formInputStats" disabled ...>
```

ou produzir exatamente os mesmos outerWidth/outerHeight.

Parent histórico:

```text
float:right
width:280px
margin-right:-8px
margin-top:-15px
```

Esses margins também precisam ser reproduzidos.

# VIS-FIX-20 - CONTADORES DEVEM CONTINUAR SENDO LINKS

No legado cada contador é envolvido por `<a>` para a listagem correspondente.

Preservar:

```text
ENTRADA -> Entrada
PENDENTE CREDITO -> Aguardando crédito
ENCAMINHADO -> Encaminhado
CONCLUIDO -> Concluído
```

e filtros equivalentes para soluções.

Não deixar a sidebar somente informativa.

# VIS-FIX-21 - FALTA O SEPARADOR GRANDE ANTES DO CENTRO DE AVISOS

O screenshot legado mostra claramente o separador vermelho/preto entre o painel de anotações e:

```text
CENTRO DE AVISOS E RELATORIOS
```

O código histórico usa:

```html
<img
    style="margin-top:50px;float:right;"
    src="../images/separador2.png"
    height="40"
/>
```

O partial atual começa diretamente pelo centro de avisos.

Vendorizar/reutilizar o asset histórico:

```text
separador2.png
```

mesmos bytes quando possível.

Restaurar:

```text
float:right
margin-top:50px
height:40px
clear antes/depois
```

# VIS-FIX-22 - ORDEM DOS ALERTAS ESTÁ ERRADA

O screenshot legado mostra como PRIMEIRO bloco:

```text
PRODUTOS COM MAIOR PRIORIDADE SEM ENCAMINHAMENTO
```

O V3 mostra primeiro:

```text
RECEBIDOS HÁ MAIS DE 30 DIAS SEM ENCAMINHAR
```

`ListarGruposDeAlertas::listar()` usa hoje uma ordem moderna própria.

Não assumir que ordem não importa.

Leia `14.6.1/inc/startpage.php`.

A ordem histórica é definida pelos includes, começando por:

```text
listar_prioridadealta.php
listar_pabertonaoencaminhado.php
listar_semsn.php
listar_semnota.php
listar_prazodestinatario.php
listar_naoencaminhadoprazoestourado.php
listar_pgarantiafornecedorexpirado.php
listar_pmenosde30.php
listar_naovaidargarantia.php
listar_nfpendentelancar.php
...
```

Mapear todos até o fim do arquivo.

A apresentação V1 deve seguir essa ordem.

Não precisa alterar a ordem do caso de uso genérico se isso prejudicar V2.

Pode existir um presenter/ordenação específica da view V1.

# VIS-FIX-23 - OS ALERTAS NÃO SÃO TODOS UMA LISTA GENÉRICA

Hoje `_centro_de_avisos.blade.php` faz basicamente:

```text
ícone
título
Mostrar
#ID - DESCRIÇÃO
```

para todos.

O legado NÃO faz isso.

Cada include histórico possui sua composição real.

Alguns mostram:

```text
Nenhum item foi encontrado
```

outros mostram tabela com colunas próprias.

No screenshot legado, por exemplo:

```text
PROTOCOLO ESTA ABERTO E O PRODUTO NAO ENCAMINHADO
```

possui uma tabela com:

```text
RECEBIDO
T
ORIGEM
NF C
NF V
FORNECEDOR
FABRICANTE
DESCRICAO
MODELO
OS
A
```

Não preciso de PHP procedural.

Preciso da APRESENTAÇÃO equivalente.

Reutilizar casos de uso modernos, mas criar partials/presenters apropriados quando o legado tiver apresentações diferentes.

Não esmagar 10 componentes históricos diferentes numa única lista genérica só por DRY.

# VIS-FIX-24 - ESTADO MOSTRAR/OCULTAR

Comparar o estado inicial de cada bloco no runtime legado.

No screenshot legado existem estados como:

```text
Ocultar
Mostrar
```

Não assumir que todos começam fechados.

Hoje o partial V3 começa todos com:

```html
display:none
Mostrar
```

Verificar cada include/subp e o JS real.

Reproduzir estado inicial e comportamento.

# VIS-FIX-25 - ZEBRA DE ENTRADA NÃO DEVE SER BASEADA CEGAMENTE NO ÍNDICE

Hoje:

```php
classe_css_de_alerta(..., $indice)
```

usa o índice bruto para decidir:

```text
TrZebrada1
TrZebrada2
```

O PHP histórico mantém `$TR1` como estado.

Alguns tipos de alerta alteram esse estado e outros não.

Exemplo:

```text
SEM GARANTIA -> pode alternar $TR1
Cliente/Licitação fora de estoque -> pode alternar $TR1
prioridade urgente/alta -> não necessariamente altera $TR1
linha neutra -> alterna $TR1
```

Portanto uma linha de alerta não pode necessariamente "consumir" a zebra pelo simples índice.

Compare uma sequência real com o legado.

Se houver divergência, criar um presenter de linha V1 que reproduza a máquina de estado histórica.

Não colocar estado CSS no domínio.

# VIS-FIX-26 - NÃO ALTERAR GLOBAIS QUE JÁ ESTÃO CORRETOS

Depois de normalizar zoom, estes itens parecem hoje muito próximos/corretos:

```text
largura base
centralização
altura da barra superior
largura da tabela
posição horizontal geral
paleta base
menu
fonte base após os commits recentes
```

Não mexer neles sem prova por computed style.

Especialmente NÃO alterar:

```text
984px
```

porque um screenshot em 110% "parece maior".

# VALIDAÇÃO VISUAL OBRIGATÓRIA

Para cada checkpoint gere screenshots de:

```text
LEGADO 100%
V3 100%
```

na MESMA viewport.

Quero pelo menos:

```text
Pág. Inicial
Entrada
Concluído
Concluído com Novo aberto
```

Depois de corrigir, compare em overlay.

Se possível gerar também diff absoluto.

Não quero apenas screenshot lado a lado.

Para os elementos principais, retorne tabela:

```text
Elemento                         Legado       V3        Delta
#BASE width                      ...          ...       ...
#TOPO height                     ...          ...       ...
#JS-Localizar width              ...          ...       ...
#JS-Localizar height             ...          ...       ...
input Localizar width            ...          ...       ...
select solução                   ...          ...       ...
anotação width                   ...          ...       ...
anotação height                  ...          ...       ...
sidebar width                    ...          ...       ...
Tabelinha-Table Entrada          ...          ...       ...
header Entrada                   ...          ...       ...
linha Entrada                    ...          ...       ...
Novo panel width                 ...          ...       ...
Novo panel height                ...          ...       ...
checkbox/toggle width            ...          ...       ...
```

Delta aceitável:

```text
0 px quando o legado define medida fixa
```

Para diferenças produzidas por rasterização de fonte:

```text
documentar 1 px se inevitável
```

mas primeiro confirmar computed style idêntico.

# ORDEM DE EXECUÇÃO

Faça nesta ordem:

```text
1. normalização Playwright 100% x 100%
2. Entrada
3. Encaminhado
4. Aguardando crédito
5. resumo inferior de Concluídos
6. painel Novo
7. Localizar
8. Página Inicial e anotação
9. contadores
10. Centro de Avisos
11. fixture visual
12. comparação final
```

Não tente resolver tudo num commit.

Sugestão:

```text
#VIS-FIX-01 - Corrige cabeçalhos e tabelas V1
#VIS-FIX-02 - Completa a tela Concluídos V1
#VIS-FIX-03 - Restaura painel Novo V1
#VIS-FIX-04 - Restaura Localizar V1
#VIS-FIX-05 - Restaura composição da página inicial V1
#VIS-FIX-06 - Restaura Centro de Avisos V1
#VIS-FIX-07 - Consolida prova visual V1
```

# TESTES

Ao final:

```bash
php artisan test
npm run build
npx playwright test tests/Browser
git status
git log --oneline -15
```

Não faça push.

# CRITÉRIO DE ENCERRAMENTO

Não diga:

```text
"parece igual"
"está bem próximo"
"testes passaram"
```

como única prova.

Só encerrar quando:

```text
estrutura histórica correta
computed dimensions conferidas
computed fonts conferidas
assets corretos
ordem correta
campos corretos
formatação correta
comportamento inline correto
screenshots normalizados lado a lado
diff visual revisado
```

Se o diff ainda mostrar uma diferença relevante, localizar o seletor ou estrutura responsável e continuar.

O objetivo do TEMA V1 é:

```text
arquitetura moderna por baixo
interface 14.6.1 por cima
```

Não modernizar visualmente nada nesta frente.


-----------------------------------

PROMPT 2

-----------------------------------

Acabei de atualizar o repositório e quero que você CONTINUE a frente de paridade visual
do TEMA V1 a partir do estado ATUAL do main.

IMPORTANTE: não repita trabalho que já foi concluído.

O HEAD que deve servir de referência é:

c2f7db2
#ARQ-RMA - Restaurada a aparencia original das telas de Entrada Encaminhado e Aguardando Credito do Tema V1

Antes de qualquer alteração:

cd ~/github/08.24.1-gerenciador-de-rma

git status
git branch --show-current
git log --oneline -15

Leia obrigatoriamente o estado atual de:

docs/produto/plano-execucao-paridade-estrutural-v1.md
docs/produto/checklist-paridade-visual-v1-runtime.md

Não crie nova investigação.

Não crie novo roadmap.

Não use subagents para iniciar outra auditoria ampla.

Não faça push.

Pode fazer commits locais pequenos.

==================================================
1. NÃO REFAZER CP1, CP2 OU CP3
==================================================

Os seguintes checkpoints já foram executados e validados:

CP1 - cascata/fontes
CP2 - primitivas de tabelas
CP3A - Concluído, parte superior/tabela
CP3B - Entrada
CP3C - Encaminhado
CP3D - Aguardando crédito

NÃO volte a alterar essas superfícies somente porque screenshots humanos antigos
parecem diferentes.

Os screenshots humanos anteriores foram feitos em escalas diferentes.

O plano atual já estabeleceu:

Chromium headless
zoom 100%
deviceScaleFactor 1
viewport 1440x1000

e já mediu:

#BASE
#CONTEUDO
tabelas
headers
colunas
fontes

O runtime Legacy e o V3 possuem largura histórica de 984px.

NÃO aumentar a largura geral.

NÃO trocar 984px.

NÃO aplicar escala artificial de 110%.

NÃO mudar fonte/base/header sem nova evidência objetiva.

O commit c2f7db2 já registrou, para Entrada e Encaminhado, comparação direta de:

ícone
title-comicone
header
largura de tabela
larguras de coluna
ausência do H1
zebra

Portanto preserve essas correções.

Aguardando Crédito só precisa de uma confirmação visual final com fixture que produza
pelo menos um registro PENDENTE CREDITO. Não reimplemente a tela.

==================================================
2. COMEÇAR PELO CP4 QUE ESTÁ REALMENTE PENDENTE
==================================================

O plano atual mostra CP4 inteiro pendente.

Execute agora:

CP4 - resumo completo de Concluídos

Fonte de verdade:

legacy-source/14.6.1/page/concluidos.php

O Blade atual termina após a tabela, mas o legado continua com:

VALOR TOTAL: R$ ...
DATA DO PROCESSAMENTO: ...
Quantidade Total de produtos: ...
Quantidade dos produtos a cima que nao participaram da contagem monetario: ...

Quero reprodução literal desta composição visual.

Não calcular SQL ou agregações no Blade.

Criar DTO/read model/presenter simples se necessário.

Entregar para a view:

soma dos valores
quantidade total
quantidade com valor zero
data de processamento

Preservar a apresentação histórica:

VALOR TOTAL alinhado à direita
font-family Arial
margin-top 0
letter-spacing 2px
demais textos com font-size 15px
hr antes
hr final
ordem histórica
grafia histórica
total com ponto decimal
sem separador de milhar se o legado não possui

Congelar relógio no teste da data.

Capturar Legacy e V3 em 100%, mesma viewport, e fechar CP4 conforme o plano existente.

Commit separado.

==================================================
3. DEPOIS DO CP4, FAZER O CP5 COMO REVISÃO DE PROPAGAÇÃO
==================================================

No CP5 NÃO quero uma auditoria genérica.

Quero usar os screenshots fornecidos pelo usuário e o código Legacy como lista
objetiva das divergências que ainda são visíveis.

Os principais alvos agora são:

Pág. Inicial
Localizar
Novo
Quadro de Anotações
Contadores
Centro de Avisos

Entrada, Encaminhado e Concluído superior devem funcionar como referências de coisas
que já foram corrigidas corretamente.

==================================================
4. PÁGINA INICIAL V1 AINDA TEM CONTEÚDO ARTIFICIAL
==================================================

Compare:

legacy-source/14.6.1/inc/startpage.php
legacy-source/14.6.1/inc/menuright.php

com:

resources/views/temas/v1/rma/index.blade.php
resources/views/temas/v1/layout.blade.php

Hoje a view V3 ainda começa com elementos que não pertencem à composição histórica:

RMAs
Novo RMA

O Blade possui explicitamente um link:

Novo RMA

antes do Localizar.

O legado não tem esse link visual ali.

Remover da composição visual V1.

Também não renderizar H1 artificial "RMAs" na Home V1.

Se quiser preservar heading semântico, usar sr-only.

A primeira superfície útil abaixo do header deve assumir praticamente a posição
histórica do Localizar.

Não alterar o TEMA V2.

==================================================
5. LOCALIZAR V1 ESTÁ FUNCIONAL E VISUALMENTE ERRADO
==================================================

Esta é uma divergência concreta e NÃO foi corrigida pelo commit c2f7db2.

Fonte real:

legacy-source/14.6.1/menujs-top/localizar.php
legacy-source/pattern/14.6.1.css
legacy-source/pattern/14.6.1.js

O legado possui:

[INPUT GRANDE]
[QUALQUER UMA SOLUCAO]
[TODOS OS CAMPOS]
[FILTRAR]

Hoje o V3 possui algo equivalente a:

Buscar por [Texto]
[input]
[Buscar]

Isto não é a mesma superfície.

Reproduzir o Localizar histórico.

SOLUÇÃO deve oferecer as opções do legado:

QUALQUER UMA SOLUCAO
GERADO CREDITO
SEM GARANTIA
REPARO
TROCA DO PRODUTO
TROCA DE PECA INTERNA
DEVOLUCAO DO PRODUTO
REEMBOLSO DO DINHEIRO
REPARO PELO RMA
TESTADO TUDO OK
ORCAMENTO PAGO
PROCON

CAMPO deve oferecer:

TODOS OS CAMPOS
ORDEM DE SERVICO
FABRICANTE
DESCRICAO
S/N, P/N OR ID/SNID/ETC
MODELO
ORIGEM
EMPRESA
CLIENTE
CODIGO DE RASTREIO
PROTOCOLO
NF
DESTINATARIO
CHAVE

A arquitetura pode adaptar esses valores aos critérios modernos.

Não precisa copiar parâmetros PHP antigos para o domínio.

Mas a superfície visual V1 deve ser histórica.

==================================================
6. RESTAURAR A GEOMETRIA EXATA DO LOCALIZAR
==================================================

Usar como fonte pattern/14.6.1.css.

Conferir no CSS real, mas as medidas históricas incluem:

.JS-Localizar
min-height:25px
padding:10px
margin-bottom:10px

.JSformLocalizarInput
width:422px
height:30px
padding:10px
letter-spacing:1px
font-size:18px

.JSformLocalizarSelect
margin-left:15px
height:52px
font-size:12px
text-align:center

.JSformLocalizarButton
height:52px
width:100px
margin-left:15px
font-size:14px
letter-spacing:1px
background:#106D78

Não "melhorar" box model.

Comparar outerWidth e outerHeight pelo getBoundingClientRect.

O objetivo é reproduzir o browser histórico.

==================================================
7. LOCALIZAR TAMBÉM PRECISA VOLTAR A SER INLINE
==================================================

O código Legacy possui:

function LocalizarMaximize() {
    document.getElementById("JS-Localizar").style.display = "block";
    document.getElementById("menu-localizar").style.fontWeight = "bold";
}

O v1.js atual implementa Novo, mas ainda não implementa Localizar.

Não quero o item do menu funcionando apenas como:

/rmas#localizar

Faça o mesmo padrão já adotado corretamente para Novo:

#JS-Localizar presente no DOM
oculto quando apropriado
clicar "Localizar" expõe o painel
não navega
mantém a superfície atual abaixo
item Localizar fica bold

Na Pág. Inicial, reproduzir o estado inicial histórico.

Pode extrair um partial:

temas/v1/rma/_form_localizar.blade.php

e incluí-lo pelo layout da mesma forma consciente que foi feita com Novo.

Não duplicar formulário entre páginas.

==================================================
8. PAINEL NOVO: PRESERVAR O QUE JÁ FUNCIONA
==================================================

VIS-V1-002 já está correto:

clicar Novo
abre inline
não muda a URL
mantém a tela atual abaixo
POST continua funcional

NÃO refazer essa mecânica.

Corrigir apenas as divergências visuais e de apresentação restantes.

==================================================
9. PAINEL NOVO: RESTAURAR O TOGGLE DE ESTOQUE
==================================================

Hoje o Blade atual usa checkbox nativo:

<input type="checkbox" ...>
<label>O ITEM E DO ESTOQUE</label>

No Legacy o mesmo controle é um toggle horizontal grande.

Fonte:

legacy-source/14.6.1/menujs-top/novo.php
legacy-source/pattern/15.9.7.css

O legado possui estrutura semelhante a:

<input type="checkbox" ... checked>
<label
    data-text-true="O ITEM E DO ESTOQUE"
    data-text-false="ITEM NAO E DO ESTOQUE">
    <i></i>
</label>

e estado checked verde.

Portar a implementação visual histórica.

Escopar a regra se necessário para não alterar outros checkboxes do sistema.

Não usar checkbox nativo visível no TEMA V1.

Preservar a semântica moderna de envio:

marcado = true
desmarcado = false

==================================================
10. PAINEL NOVO: DATAS NÃO DEVEM USAR WIDGET NATIVO
==================================================

O Blade atual usa:

type="date"

O Legacy usa:

type="text"
placeholder="00/00/2015"

O screenshot evidencia a diferença:

V3 mostra dd/mm/aaaa + ícone de calendário
Legacy mostra 00/00/2015 como campo textual

Para o TEMA V1, restaurar a apresentação histórica.

Isso NÃO significa armazenar texto no banco.

A camada HTTP pode converter:

dd/mm/YYYY

para o formato interno antes de validar/persistir.

Não enfraquecer a validação.

Não alterar V2.

==================================================
11. PAINEL NOVO: FABRICANTE AINDA NÃO TEM O CONTROLE HISTÓRICO
==================================================

O Legacy usa:

<input
    class="novo_formInput"
    type="text"
    name="fabricante"
    list="fabricantes"
>

O V3 atual usa:

<select name="fabricante_id">

Visualmente são controles diferentes.

Quero no TEMA V1 o input/datalist equivalente ao Legacy.

Não abandonar a FK moderna.

Resolver nome -> fabricante_id na camada de apresentação/aplicação antes de chamar o
caso de uso.

Não colocar query no Blade.

Não alterar modelagem de banco.

==================================================
12. PAINEL NOVO: REVISAR BOX MODEL
==================================================

O port atual adicionou:

box-sizing:border-box

em vários controles:

novo_formInput
novo_formInputDATE
novo_formInputSmall
novo_defeito
formInputObservacao

O CSS histórico não tinha necessariamente isso.

Não remova cegamente.

Meça Legacy e V3.

Para cada um comparar:

width CSS
padding
border
box-sizing
getBoundingClientRect().width
getBoundingClientRect().height

Se o border-box estiver alterando a geometria real, reproduzir o box model Legacy.

A regra é:

outerWidth Legacy = outerWidth V3
outerHeight Legacy = outerHeight V3

e não "border-box é mais moderno".

==================================================
13. QUADRO DE ANOTAÇÕES DA HOME AINDA ESTÁ DIFERENTE
==================================================

Fonte:

legacy-source/14.6.1/inc/startpage.php
legacy-source/pattern/14.6.1.css

O Legacy possui aproximadamente:

container width 675px
margin-left 1px
textarea width 674px
rows 20
padding 5px
font-size 12px
letter-spacing 1px
line-height 1.5

O V3 atual usa:

rows="14"

e por isso a área está muito mais baixa.

Restaurar a geometria histórica.

Também comparar o cabeçalho:

panotacao
imganotacao

No Legacy há:

ícone
padding
letter-spacing 3px
font-weight bold
margens específicas

Não reduzir isso a somente font-weight.

==================================================
14. BOTÃO "SALVAR ANOTAÇÃO" NÃO EXISTE NO LEGADO
==================================================

O V3 introduziu um botão visual:

Salvar anotação

O Legacy salva durante digitação e não mostra esse botão.

No TEMA V1, retirar o botão da composição visual.

Não copiar o AJAX antigo.

Use endpoint Laravel existente.

Pode implementar:

input/change
debounce
fetch
CSRF

para salvar de forma moderna.

Preservar tratamento de erro.

O objetivo é comportamento equivalente com implementação moderna.

==================================================
15. SIDEBAR DOS CONTADORES AINDA TEM BOX MODEL DIFERENTE
==================================================

Fonte:

legacy-source/14.6.1/inc/startpage.php
legacy-source/pattern/14.6.1.css

Legacy:

parent:
width:280px
float:right
margin-right:-8px
margin-top:-15px

.formLabelStats:
width:198px
padding:5px
border:1px

.formInputStats:
width:45px
padding:5px
border:1px

O V3 atual usa box-sizing:border-box e troca o input de valor por um <p>.

Não assumir equivalência.

Medir outerWidth/outerHeight.

Restaurar o resultado histórico.

Também restaurar os links dos contadores.

No Legacy os contadores são navegação:

ENTRADA
PENDENTE CREDITO
ENCAMINHADO
CONCLUIDO
SEM GARANTIA
GERADO CREDITO
...

Cada item leva para a listagem/filtro correspondente.

No V3 não devem ser meros números decorativos.

==================================================
16. FALTA O SEPARADOR GRANDE ANTES DO CENTRO DE AVISOS
==================================================

O Legacy possui, após anotação/contadores:

<img
    style="margin-top:50px;float:right;"
    src="../images/separador2.png"
    height="40"
/>

Nos screenshots fornecidos este separador grande vermelho/preto é claramente visível.

O V3 atual vai diretamente para:

CENTRO DE AVISOS E RELATORIOS

Portar/vendoriar o mesmo asset:

separador2.png

se ainda não estiver disponível.

Preservar:

height 40
margin-top 50px
float right
clear antes/depois

Não desenhar uma nova versão em CSS.

==================================================
17. CENTRO DE AVISOS: ORDEM NÃO ESTÁ HISTÓRICA
==================================================

O caso de uso atual `ListarGruposDeAlertas` retorna uma ordem própria.

O Legacy define a ordem pelos includes de:

14.6.1/inc/startpage.php

Começando por:

listar_prioridadealta.php
listar_pabertonaoencaminhado.php
listar_semsn.php
listar_semnota.php
listar_prazodestinatario.php
listar_naoencaminhadoprazoestourado.php
listar_pgarantiafornecedorexpirado.php
listar_pmenosde30.php
listar_naovaidargarantia.php
listar_nfpendentelancar.php

Leia o restante do arquivo até o fim e reproduza a ordem exata.

Não alterar a ordem global se ela for necessária ao TEMA V2.

Pode usar presenter/ordenação específica para o Tema V1.

==================================================
18. CENTRO DE AVISOS: NÃO GENERALIZAR TODAS AS REGRAS
==================================================

O partial moderno atual apresenta praticamente todas as regras como:

ícone
título
Mostrar
#id - descrição

O Legacy não faz isso.

Existem blocos com estruturas diferentes.

Alguns apresentam apenas:

Nenhum item foi encontrado

Outros apresentam tabelas completas.

Exemplo visível no screenshot:

PROTOCOLO ESTA ABERTO E O PRODUTO NAO ENCAMINHADO

tem tabela com colunas próprias.

Leia cada `subp/listar_*.php` usado pela Home.

Não portar SQL.

Reutilizar os casos de uso modernos já existentes.

Criar partials/presenters somente quando a apresentação Legacy realmente for diferente.

DRY não pode apagar diferenças históricas reais.

==================================================
19. MOSTRAR/OCULTAR DOS ALERTAS
==================================================

Hoje o V3 inicializa genericamente os blocos fechados com display:none.

Confira o runtime/código Legacy para saber quais começam:

Mostrar

e quais começam:

Ocultar

Reproduzir o estado inicial e interação.

Não assumir que todos são iguais.

==================================================
20. NÃO MEXER NA BASE GLOBAL SEM EVIDÊNCIA
==================================================

Neste ponto já foram comprovados:

984px de conteúdo
tipografia principal
header
menu
cores base
primitivas de tabela

Preserve tudo.

Se uma correção da Home parecer exigir alterar:

#BASE
#TOPO
#CONTEUDO
body global
menu-up
Tabelinha-Table global

pare e primeiro prove pelo computed style que a regra global está errada.

Provavelmente o defeito estará na superfície específica.

==================================================
21. DADOS DE QA E COMPARAÇÃO VISUAL
==================================================

Não tente fazer:

OS-QA-00059

caber como:

5947

mudando CSS global.

Os dados têm tamanhos diferentes.

Para evidência visual final, pode criar fixture QA determinística com comprimentos
semelhantes aos dados históricos, sempre fictícia.

Não copiar registros reais da base Legacy para o banco V3.

Para Aguardando Crédito, garantir pelo menos um registro fictício com:

solucao=PENDENTE CREDITO

e finalmente produzir a comparação visual que faltou no CP3D.

Se ela confirmar a implementação atual, não alterar nada.

==================================================
22. GATE FINAL
==================================================

Após CP4 e as correções específicas acima, completar o CP5 já existente.

Não criar CP6 ou outro plano paralelo.

Comparar:

Legacy 100%
V3 100%

nas viewports já previstas no plano:

1440x1000
1562x1400
1700x1000

Quero screenshots normalizados pelo Playwright.

Para pelo menos:

Pág. Inicial
Entrada
Encaminhado
Aguardando Crédito
Concluído
Concluído com Novo aberto
Home com Localizar aberto
Home com Centro de Avisos

Entrada/Encaminhado devem permanecer iguais aos resultados aprovados no commit c2f7db2.

Se regredirem, corrigir a regressão, não redesenhar.

Gerar tabela final de medidas:

Elemento                      Legacy       V3       Delta
#BASE width                   ...          ...      ...
#TOPO                         ...          ...      ...
#CONTEUDO                     ...          ...      ...
#JS-Localizar                 ...          ...      ...
Input Localizar               ...          ...      ...
Select solução                ...          ...      ...
Select campo                  ...          ...      ...
Botão FILTRAR                 ...          ...      ...
#JS-Novo                      ...          ...      ...
toggle estoque                ...          ...      ...
inputs Novo                   ...          ...      ...
textarea anotação             ...          ...      ...
sidebar contadores            ...          ...      ...
separador2                    ...          ...      ...
Centro de Avisos              ...          ...      ...
Tabelinha Entrada             ...          ...      ...
Tabelinha Concluído           ...          ...      ...

Para medidas fixas vindas diretamente do CSS histórico, quero delta 0 sempre que
tecnicamente possível.

Diferenças de rasterização de fonte podem ser documentadas somente depois de provar
computed styles e fonte efetivamente rasterizada.

==================================================
23. TESTES
==================================================

Ao final:

php artisan test
npm run build
npx playwright test tests/Browser
git status
git log --oneline -20

Não fazer push.

Atualizar apenas os documentos de paridade já existentes.

Se algum item atualmente marcado [x] for contradito por evidência objetiva nova,
não apagar o histórico.

Registrar que o subcritério visual foi reaberto e por quê.

Não reabrir Entrada/Encaminhado/Aguardando Crédito sem nova evidência.

==================================================
24. RETORNO FINAL
==================================================

Quero no retorno:

1. commits criados
2. checkpoints fechados
3. tabela Legacy x V3 de medidas
4. caminhos dos screenshots finais
5. testes executados
6. eventuais diferenças que ainda restaram e motivo técnico

Não diga apenas:

"testes passaram"
"parece igual"
"ficou próximo"

O Tema V1 só pode ser declarado visualmente equivalente quando a diferença for
comprovadamente eliminada ou explicada por uma limitação objetiva do browser/runtime.

Objetivo final:

arquitetura moderna em Laravel
interface visual e comportamento do 14.6.1 preservados