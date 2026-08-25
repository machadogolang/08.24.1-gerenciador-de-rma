Quero iniciar agora uma correção objetiva de paridade visual do TEMA V2.

IMPORTANTE: NÃO misture os temas durante a comparação.

A matriz correta é:

```text
LEGADO 14.6.1 <-> V3 TEMA V1
LEGADO 15.8.1 <-> V3 TEMA V2
```

Nesta tarefa trabalharemos SOMENTE com:

```text
LEGADO TEMA V2
http://localhost:8094/15.8.1/

V3 TEMA V2
http://localhost:8095/v2/rma
```

NÃO compare `localhost:8095/rmas` com `15.8.1`.

`/rmas` pode estar resolvendo o Tema V1 conforme a preferência do usuário.

Para comparação determinística use explicitamente:

```text
/v2/rma
```

A rota existe justamente para forçar o Tema V2 independentemente de `tema_preferido`.

Antes:

```bash
cd ~/github/08.24.1-gerenciador-de-rma

git status
git branch --show-current
git log --oneline -20
```

O HEAD esperado no início desta frente é posterior a:

```text
2ee170f
#ARQ-RMA - Fecha o checkpoint de paridade estrutural das 4 listagens do Tema V1
```

NÃO reabra a frente V1.

NÃO altere as correções concluídas de:

```text
14.6.1
Tema V1
Entrada V1
Encaminhado V1
Aguardando Crédito V1
Concluído V1
```

Esta é uma frente específica de paridade:

```text
15.8.1 <-> Tema V2
```

Não faça push.

Não faça uma investigação ampla.

Não crie novo roadmap.

Não use o CSS do V1 como referência visual do V2.

==================================================

1. PRIMEIRO GERE O PAR DE SCREENSHOTS CORRETO
   ==================================================

Antes de alterar código, abra com Playwright:

```text
Legacy:
http://localhost:8094/15.8.1/

V3:
http://localhost:8095/v2/rma
```

Use exatamente:

```text
Chromium
zoom 100%
deviceScaleFactor 1
viewport 2048x1152
```

Depois também validar:

```text
1440x1000
1562x1400
1700x1000
```

Capture:

```javascript
window.devicePixelRatio
getComputedStyle(document.documentElement).zoom
getComputedStyle(document.body).zoom
```

e prove que ambos estão equivalentes.

Não ajuste CSS baseado no print antigo do Tema V1.

O print V3 `/rmas` NÃO é referência para esta tarefa.

==================================================
2. ACHADO CONFIRMADO: O SHELL GLOBAL DO V2 ESTÁ ERRADO
======================================================

Esta é provavelmente a principal causa de diferença de largura e posição.

O Legacy 15.8.1 possui no `index.php`:

```html
<header class="row">
    <div style="margin:0 auto;width:1190px;">
        ...
    </div>
</header>
```

e depois:

```html
<div style="width:1190px;margin:0 auto;padding:0px;">
```

Dentro desse wrapper existem duas regiões:

```text
conteúdo principal
.container
width = 990px
float:left

+

menu lateral direito
aproximadamente 195px
margin-left:5px
```

Portanto a composição desktop real é aproximadamente:

```text
|---------------------- 1190px -----------------------|
|                                                       |
| <--------- 990px ----------> | <--- ~195px --->     |
|       CONTEÚDO PRINCIPAL      |    MENU DIREITO       |
|                                                       |
```

O V3 atual NÃO reproduz isto.

Hoje `v2.scss` define somente:

```scss
.container {
    width:990px;
    margin:0 auto;
}
```

em desktop.

Isso centraliza os 990px na viewport e elimina os aproximadamente 200px reservados à direita.

Consequência visual:

```text
V3 parece mais estreito
conteúdo começa em posição X diferente
conteúdo termina em posição X diferente
header não se alinha ao shell histórico
Centro de Avisos fica em posição diferente
não existe sidebar direita
```

CORRIGIR a estrutura.

Criar uma composição V2 equivalente a:

```text
.shell-v2
    width:1190px
    margin:0 auto

    .conteudo-principal-v2
        width equivalente ao .container histórico
        float/flex equivalente

    .menu-direito-v2
        width ~195px
        espaçamento histórico
```

Pode usar flex moderno em vez de float se:

```text
x
y
width
height
```

forem iguais ao Legacy.

Não copiar float por obrigação arquitetural.

Copiar o RESULTADO GEOMÉTRICO.

==================================================
3. NÃO CONFUNDIR .container DE 990 COM SHELL DE 1190
====================================================

O `media.php` legado confirma:

A partir de 992px:

```text
.container = 990px
```

A partir de 1280px:

```text
.container = 990px
.nav       = 1190px
```

E o wrapper exterior continua:

```text
1190px
```

Portanto NÃO conclua novamente:

```text
"V2 tem 990px, então está igual"
```

Existem duas medidas distintas:

```text
conteúdo = 990px
composição = 1190px
```

Meça separadamente.

==================================================
4. MEDIR X, NÃO SOMENTE WIDTH
=============================

Para cada elemento capture:

```text
x
y
width
height
right
bottom
```

via:

```javascript
element.getBoundingClientRect()
```

Isso é essencial.

Dois elementos com:

```text
width = 990px
```

podem estar visualmente diferentes se:

```text
Legacy x = 430
V3 x = 515
```

Exemplo de tabela de diagnóstico:

```text
Elemento                 Legacy      V3       Delta
shell width               1190        ...      ...
shell x                   ...         ...      ...
main width                 990        ...      ...
main x                     ...         ...      ...
right menu width           195        ...      ...
right menu x               ...         ...      ...
header inner width        1190        ...      ...
nav width                 1190        ...      ...
```

Não aceite somente:

```text
.container width = 990 OK
```

como prova de paridade.

==================================================
5. HEADER DO V3 V2 É OUTRA INTERFACE
====================================

O Legacy usa:

```text
Inicio
Pesquisar
Novo
Entrada
Recebido
Encaminhado
Concluido
Menu
Logout
```

em UMA barra superior.

Fonte:

```text
legacy-source/15.8.1/inc/menu.php
```

O layout V3 atual cria primeiro uma navbar com:

```text
RMAs
Clientes
Fabricantes
Fornecedores
Assistências
Usuários
usuário
Sair
```

e depois `rma/index.blade.php` cria OUTRA navegação:

```text
Início
Pesquisar
Novo RMA
Entrada
Recebido
Encaminhado
Concluído
```

Isso não é reconstrução do Tema V2.

É uma nova interface.

Remover essa arquitetura visual dupla no TEMA V2.

Quero a barra histórica única.

A arquitetura de rotas pode continuar moderna.

Mapear:

```text
Inicio       -> painel #inicio
Pesquisar    -> #pesquisar
Novo         -> #novo_rma
Entrada      -> #entrada
Recebido     -> #recebido
Encaminhado  -> #encaminhado
Concluido    -> #concluido
Menu         -> dropdown
Logout       -> logout
```

Não mostrar:

```text
RMAs
perfil do usuário
Usuários
Clientes
Fabricantes
Fornecedores
Assistências
```

diretamente na barra superior se no Legacy esses itens estão dentro de `Menu`.

==================================================
6. MENU DROPDOWN PRECISA SER HISTÓRICO
======================================

O dropdown `Menu` legado contém:

```text
Creditos
Assistencias
Fabricantes
Fornecedores
Clientes
Relatorios
Anotacoes
Controle
Trocar p/ 14.6.1
```

As rotas internas podem ser modernas.

O texto, posição, tamanho, alternância de cor e apresentação V2 devem ser equivalentes.

O V3 atualmente importa apenas:

```javascript
bootstrap/js/tab
```

Se o dropdown histórico precisar do plugin Bootstrap:

```javascript
bootstrap/js/dropdown
```

importe somente o necessário ou implemente equivalentemente.

Não importe bundle inteiro sem necessidade.

==================================================
7. HEADER: GEOMETRIA EXATA
==========================

O CSS legado declara:

```css
header {
    padding:0px;
    height:40px;
    z-index:999;
    position:fixed;
    width:100%;
    top:-3px;
    background-color:#C20F41;
    border-bottom:1px solid #333 !important;
}
```

Hoje o layout V3 usa inclusive:

```text
background-color:#18354B
```

na navbar externa.

Isso está errado para esta superfície.

Restaurar:

```text
background #C20F41
height 40px
position fixed
top -3px
border inferior histórico
```

A aba ativa no Legacy usa:

```text
#FE0048
```

Não usar o azul petróleo como estado ativo da navegação principal.

O azul `#224A5D` pertence a outros controles.

==================================================
8. LARGURA DOS ITENS DO MENU
============================

`media.php` define:

até determinados breakpoints:

```text
.nav-tabs li = 12.5%
```

e a partir de 1280px:

```text
.nav = 1190px
.nav-tabs li = 11.1%
```

O `v2.scss` atual portou as larguras de `.container`, mas NÃO portou estas regras da navegação.

Adicionar os breakpoints históricos.

Não deixar Bootstrap distribuir os itens automaticamente.

Comparar:

```text
x de cada item
width de cada item
height
padding
line-height
```

Legacy x V3.

==================================================
9. TAB-CONTENT E POSIÇÃO VERTICAL
=================================

O Legacy usa:

```css
.tab-content {
    margin:0px -15px;
    margin-top:40px;
    min-height:450px;
    padding:0px;
}
```

O V3 usa atualmente uma estrutura completamente diferente:

```html
<div class="container box-content"
     style="margin-top:15px;padding:15px;">
```

com:

```html
<h1>...</h1>
```

Essa camada não existe no shell histórico.

Não continue compensando isto com classes como:

```text
painel-inicio-fundo-escuro
margin negativo
```

se a causa é o wrapper errado.

Primeiro reproduza a árvore estrutural correta.

Depois veja se a compensação ainda é necessária.

==================================================
10. REMOVER H1 ARTIFICIAL DO LAYOUT V2
======================================

O layout V3 injeta globalmente:

```html
<h1>{{ $titulo }}</h1>
```

O Legacy não injeta esse título em todas as telas.

Remover a injeção visual automática.

Cada superfície deve produzir seu próprio cabeçalho conforme o PHP histórico.

Pode manter heading `sr-only` para semântica.

==================================================
11. FONTE: COMPARAR COMPUTED STYLE REAL
=======================================

Não quero uma discussão abstrata:

```text
"é Open Sans"
"é Arial"
"deveria ser Fira"
```

Use o navegador.

Para cada elemento capture:

```javascript
getComputedStyle(element).fontFamily
getComputedStyle(element).fontSize
getComputedStyle(element).fontWeight
getComputedStyle(element).lineHeight
getComputedStyle(element).letterSpacing
```

Comparar pelo menos:

```text
body
header
.nav-tabs
.nav-tabs li
.nav-tabs li a
breadcrumb de pesquisar
Pesquisar:
input Search
botão Enviar pesquisa
Centro de Avisos
títulos de alertas
th
td
menu direito
footer
```

==================================================
12. EXISTE UMA DIVERGÊNCIA DE FONTE CONFIRMADA NO CÓDIGO
========================================================

O `pattern/15.9.7.css` legado termina impondo em vários elementos:

```css
font-family:"Arial","Open Sans","Fira mono" !important;
```

inclusive:

```text
body
tr
td
headings
breadcrumb
nav-tabs li
upmenuright
```

Porém `_compartilhado.scss` atual contém, por exemplo:

```scss
.breadcrumb {
    font-family:"Fira Mono", "Arial", "Fira Sans";
}
```

Isso não corresponde ao override final do CSS legado.

Corrigir a partir do computed style real.

Não aplicar simplesmente Arial globalmente.

Preservar os seletores específicos do Legacy.

==================================================
13. CASCATA DO CSS V2 DEVE SER A MESMA
======================================

O Legacy carrega nesta ordem:

```text
1 Bootstrap 3.3.5
2 font-opensans.css
3 Fira
4 pattern/15.8.1.css
5 pattern/15.9.7.css
6 css/media.php
```

O V3 atual faz conceitualmente:

```scss
@use "compartilhado";
@import Bootstrap;
regras v2;
```

Isto não representa a ordem histórica.

Inspecionar o CSS FINAL produzido pelo Vite, não somente o SCSS fonte.

Garantir ordem equivalente:

```text
Bootstrap
base V2 15.8.1
compartilhado 15.9.7
media V2
```

As fontes locais podem ser carregadas de forma moderna.

Não depender da internet.

O que deve permanecer histórico é a precedência dos seletores.

==================================================
14. O MENU LATERAL DIREITO ESTÁ INTEIRAMENTE AUSENTE
====================================================

Este é outro defeito visual estrutural enorme.

O Legacy possui `inc/rightmenu.php`.

No screenshot ele aparece à direita com:

```text
DEU ENTRADA HOJE
RECEBIDOS
ENCAMINHADOS
LAST 10 CONCLUIDOS
DESTINATARIOS
TRANSPORTE P/ PORTO A
URGENTE
PENDENTE CREDITO
CREDITO DISPONIVEL
FABRICANTES
FORNECEDORES
CLIENTES
PRODUTOS DE CLIENTE
TODOS PRODUTOS
...
```

O repositório V3 atual não possui implementação equivalente de:

```text
menuright
upmenuright
LRTOP1
LRTOP2
LiRight1
LiRight2
```

Não esconda isso dizendo que é "conteúdo auxiliar".

Ele faz parte do layout 15.8.1 e altera inclusive:

```text
largura geral
posição horizontal
densidade
equilíbrio visual
```

Reproduzir o menu lateral.

Não executar SQL na Blade.

Criar read models/queries modernas para alimentar cada seção.

Pode começar pela estrutura visual e pelos contadores/dados já disponíveis, mas não declare paridade enquanto faltar alguma seção do runtime Legacy.

==================================================
15. GEOMETRIA DO MENU DIREITO
=============================

O `index.php` Legacy cria:

```text
margin-top:44px
width:195px
margin-left:5px
border:1px solid #444
```

dentro do shell de 1190px.

Preservar estas dimensões calculadas.

Medir:

```text
x
width
y
height inicial
```

Não posicionar com `position:absolute` simplesmente para "parecer no lugar".

Ele deve participar corretamente da composição.

==================================================
16. HOME DO V2 ESTÁ SIMPLIFICADA DEMAIS
=======================================

O Legacy `page/inicio.php` começa incluindo:

```text
page/pesquisar.php
```

Depois:

```text
separador2.png
CENTRO DE AVISOS E RELATORIOS
alertas
```

O V3 atual adicionou:

```text
Bem-vindo(a), usuário.
```

Isto não aparece na composição Legacy mostrada.

Remover do TEMA V2 se não existir no runtime histórico.

==================================================
17. PESQUISAR DA HOME
=====================

O Legacy possui no topo direito:

```text
Qualquer campo / Nota fiscal / Número de série
```

Fonte:

```text
inc/menu_pesquisar.php
```

Depois:

```text
Pesquisar:
[Search] [Enviar pesquisa]
```

O V3 Home atualmente contém apenas um formulário simplificado.

Restaurar:

```text
breadcrumb de tipos
posição à direita
título Pesquisar:
campo
botão
```

A rota interna pode continuar moderna.

==================================================
18. NÃO USE SELECT GENÉRICO PARA SUBSTITUIR O BREADCRUMB
========================================================

A aba Pesquisar do V3 atualmente usa:

```html
<select>
    Texto
    Serial
    Nota fiscal
</select>
```

O Legacy usa navegação breadcrumb:

```text
Qualquer campo / Nota fiscal / Número de série
```

Visualmente são componentes completamente diferentes.

O Tema V2 deve reproduzir o breadcrumb histórico.

==================================================
19. SEPARADOR GRANDE
====================

O Legacy Home possui:

```html
<img
    style="margin-top:50px;float:right;"
    src="separador2.png"
    height="40">
```

Restaurar posição real.

Não apenas:

```css
.separador-alerta {
    margin:5px 0;
}
```

O SCSS compartilhado atual usa `margin:5px 0`, que NÃO corresponde a esta ocorrência específica do Legacy.

Pode existir:

```text
separador principal
separadores entre alertas
```

com regras distintas.

Não generalize ambos numa única classe se as geometrias forem diferentes.

==================================================
20. CENTRO DE AVISOS
====================

O Legacy usa:

```text
lembrete.png 40px
CENTRO DE AVISOS E RELATORIOS
hrup com divider.png
```

Depois inclui cada alerta individualmente, separado por:

```text
separador.png
```

O V3 utiliza um partial genérico.

Comparar visualmente e estruturalmente.

Preservar:

```text
ícones
ordem
espaçamento
hrup
separadores
estado Mostrar/Ocultar
tipo de apresentação
```

Não transformar tabelas históricas em simples listas.

==================================================
21. AS TABELAS DO TEMA V2 AINDA NÃO SÃO O LEGADO
================================================

Este é um problema estrutural confirmado.

O partial atual:

```text
resources/views/temas/v2/rma/_tabela.blade.php
```

possui somente:

```text
#
Descrição
Defeito
Origem
Ações
```

Isso NÃO corresponde às tabelas 15.8.1.

Por exemplo, a busca geral Legacy possui:

```text
DT ENTRADA  9%
ORIGEM      8%
NF C        6%
NF V        6%
FABRICANTE 12%
DESCRICAO  13%
MODELO      20%
S/N         16%
OS           5%
S            2%
A            2%
```

Não reutilize uma tabela CRUD genérica para todas as abas se cada tabela histórica é diferente.

Para cada aba:

```text
Pesquisar
Entrada
Recebido
Encaminhado
Concluído
```

ler o PHP equivalente de `15.8.1/page/` e reproduzir:

```text
colunas
larguras
ordem
ícones
formatação
classes de linha
links
área clicável
```

==================================================
22. NÃO CONFUNDIR DRY COM PARIDADE
==================================

Uma abstração como:

```text
_tabela.blade.php
```

é boa somente se as superfícies históricas realmente forem iguais.

Se:

```text
Entrada
Recebido
Encaminhado
Concluído
```

possuem colunas diferentes, não force todas para:

```text
#
Descrição
Defeito
Origem
Ações
```

Pode compartilhar:

```text
células
presenters
classes de zebra
formatadores
```

sem compartilhar a tabela inteira.

==================================================
23. ESTADO DAS LINHAS
=====================

O Legacy utiliza:

```text
TrZebrada1
TrZebrada2
TrSemGarantia1
TrSemGarantia2
TrInconformidade
```

dependendo de:

```text
status
solução
origem
estoque
prioridade
prazo
```

O V3 já possui parte dessa regra moderna.

Não reescreva a regra de negócio sem necessidade.

Compare apenas:

```text
classe final da TR
cor
altura
text-shadow
hover
```

para os mesmos cenários.

==================================================
24. FOOTER TEM CONTEÚDO EXTRA
=============================

O layout V3 atual imprime:

```text
TEMA V2 - CellSystem RMA (reconstrução V3)
```

O Legacy não possui essa linha.

Remover da interface V2.

Preservar somente:

```text
Designed by Scripting Studios Art
Cópia licenciada para Cellsystem LTDA
```

com:

```text
posição
font-size
cor
letter-spacing
alinhamento
```

históricos.

==================================================
25. NÃO USAR BOOTSTRAP COMO DESCULPA PARA DIFERENÇA
===================================================

Os dois usam Bootstrap 3.3.5.

Portanto, quando houver diferença de:

```text
padding
container
nav
form-control
row
col-md
button
```

compare:

```text
Legacy Bootstrap 3.3.5
+
CSS 15.8.1
+
CSS 15.9.7
+
media.php
```

contra o CSS compilado V3.

Não adicionar overrides inventados antes de descobrir qual regra histórica está faltando.

==================================================
26. COMPUTED STYLE OBRIGATÓRIO
==============================

Criar uma helper temporária Playwright que receba um seletor e retorne:

```javascript
{
    rect: {
        x,
        y,
        width,
        height,
        right,
        bottom
    },
    style: {
        display,
        position,
        float,
        boxSizing,

        marginTop,
        marginRight,
        marginBottom,
        marginLeft,

        paddingTop,
        paddingRight,
        paddingBottom,
        paddingLeft,

        borderTopWidth,
        borderRightWidth,
        borderBottomWidth,
        borderLeftWidth,

        fontFamily,
        fontSize,
        fontWeight,
        lineHeight,
        letterSpacing,

        color,
        backgroundColor
    }
}
```

Executar no Legacy e V3.

Não considerar elemento equivalente somente porque "parece parecido".

==================================================
27. SELETORES A MEDIR
=====================

No mínimo:

```text
body
header
wrapper 1190
nav
cada nav li
cada nav a
tab-content
container principal
Pesquisar
breadcrumb
input Search
botão Enviar pesquisa
separador2
Centro de Avisos
uma regra de alerta
uma tabela de alerta
menu lateral
uma LRTOP
uma LiRight
footer
```

Quando os nomes de classe forem diferentes, comparar por função equivalente.

==================================================
28. CRITÉRIO PIXEL A PIXEL
==========================

Após corrigir estrutura:

Gerar:

```text
legacy-v2-home.png
v3-v2-home.png
```

com viewport idêntica.

Depois gerar:

```text
overlay 50%
diff absoluto
```

Inspecionar visualmente.

Não use somente teste numérico.

O teste numérico prova geometria.

O screenshot prova composição.

Os dois são necessários.

==================================================
29. ORDEM DE CORREÇÃO
=====================

Executar nesta ordem:

```text
V2-01 shell 1190 + main 990 + sidebar
V2-02 header/nav real
V2-03 cascade/fontes
V2-04 menu direito
V2-05 Home/Pesquisar
V2-06 Centro de Avisos
V2-07 tabelas das abas
V2-08 footer
V2-09 comparação final
```

Fazer commits locais separados.

Não tentar resolver tudo em um commit gigante.

==================================================
30. NÃO MODIFICAR O TEMA V1
===========================

O Tema V1 acabou de fechar uma frente extensa de paridade.

Todas as regras novas do Tema V2 devem estar:

```text
v2.scss
_v2-base.scss
views/temas/v2/
js/temas/v2.js
```

ou componentes explicitamente compartilhados quando a regra for comprovadamente a mesma.

Se precisar alterar `_compartilhado.scss`, prove antes:

```text
a regra pertence ao 15.9.7.css compartilhado
e
não provoca regressão no V1
```

Rodar comparação V1 depois de qualquer modificação no compartilhado.

==================================================
31. PROVA FINAL
===============

Ao final quero uma tabela como:

```text
Elemento                 Legacy V2    V3 V2      Delta
shell width               1190         1190       0
shell x                   ...          ...        0
main width                 990          990       0
main x                     ...          ...        0
sidebar width              195          195       0
sidebar x                  ...          ...        0
header height               40           40       0
header inner width        1190         1190       0
nav width                 1190         1190       0
nav item width            ...          ...        0
tab-content y             ...          ...        0
font body                 ...          ...        OK
font nav                  ...          ...        OK
font tabela               ...          ...        OK
Pesquisar x               ...          ...        0
Pesquisar width           ...          ...        0
Centro Avisos x           ...          ...        0
menu direito              presente     presente   OK
```

E caminhos para:

```text
screenshot Legacy
screenshot V3
overlay
diff
```

==================================================
32. TESTES
==========

Rodar:

```bash
php artisan test
npm run build
npx playwright test tests/Browser
```

Também executar os testes de V1 após qualquer alteração em código compartilhado.

Não faça push.

==================================================
33. REGRA DE ENCERRAMENTO
=========================

Não declare paridade porque:

```text
testes passaram
largura .container deu 990
Bootstrap é o mesmo
parece próximo
```

O shell completo precisa coincidir.

A navegação precisa coincidir.

A sidebar precisa existir.

A posição horizontal precisa coincidir.

A fonte computada precisa coincidir.

As tabelas precisam possuir as colunas históricas.

A Home precisa possuir a composição histórica.

Objetivo:

```text
15.8.1 visualmente por cima
Laravel/V3 arquiteturalmente por baixo
```

Não redesenhar o Tema V2.
