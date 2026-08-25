Quero que você interrompa temporariamente a sequência normal do checklist visual e faça uma correção estrutural de paridade do TEMA V1.

Não quero uma nova investigação abstrata sobre "por que parece diferente".

Já temos evidências concretas de que a reconstrução visual está portando conceitos do CSS legado de forma aproximada, quando para o TEMA V1 devemos reproduzir o resultado visual do CellSystem RMA 14.6.1 com a maior fidelidade possível.

Projetos:

Novo:

```text
~/github/08.24.1-gerenciador-de-rma
http://localhost:8095
```

Legado:

```text
~/github/08.24.4-legacy-gerenciador-de-rma
http://localhost:8094/14.6.1/
```

Antes de alterar qualquer coisa:

```bash
cd ~/github/08.24.1-gerenciador-de-rma
git status
git branch --show-current
git log --oneline -20
```

Leia também o estado atual da investigação e checklist visual.

Não sobrescreva trabalho alheio.

Não faça push.

Pode fazer commits locais pequenos e rastreáveis.

# OBJETIVO

O TEMA V1 não é uma releitura moderna do legado.

O objetivo é:

```text
mesma aparência
mesma geometria
mesma tipografia
mesmas dimensões
mesmas cores
mesmos espaçamentos
mesmas larguras de coluna
mesmas alturas de linha
mesma composição
mesmos assets
mesmos estados visuais
mesma interação quando ainda fizer sentido
```

A arquitetura pode e deve continuar moderna:

```text
Laravel
Blade
Sass
Vite
casos de uso
domínio
repositories
controllers
assets locais
```

Mas a arquitetura nova NÃO é justificativa para alterar o resultado visual do TEMA V1.

Pense desta forma:

```text
HTML/PHP procedural antigo -> pode ser substituído por Blade/Laravel

resultado visual produzido pelo HTML + CSS antigo -> deve ser preservado
```

# FONTE DE VERDADE

Não tente redesenhar manualmente olhando apenas screenshot.

Leia e use como especificação executável:

```text
legacy-source/14.6.1/index.php
legacy-source/14.6.1/page/
legacy-source/14.6.1/inc/
legacy-source/pattern/14.6.1.css
legacy-source/pattern/15.9.7.css
```

Muito importante:

No legado, `14.6.1/index.php` carrega CSS nesta ordem:

```text
1. pattern/14.6.1.css
2. pattern/15.9.7.css
```

A ordem faz parte da aparência final.

Hoje o `resources/sass/temas/v1.scss` usa:

```scss
@use "compartilhado";
```

antes das regras específicas do V1.

Investigue se isso está invertendo a cascata efetiva do legado.

Não preserve uma arquitetura Sass elegante se ela alterar a ordem dos overrides.

Se necessário, reorganize para algo conceitualmente equivalente a:

```text
_v1-base.scss          -> port do 14.6.1.css
_compartilhado.scss    -> port do 15.9.7.css
v1.scss                -> apenas composição na ordem correta
```

A implementação pode ter outros nomes, mas o CSS compilado deve obedecer à mesma precedência do legado.

Não use `!important` aleatoriamente para esconder problema de cascade.

# ACHADO 1 - TIPOGRAFIA ESTÁ ERRADA

O legado não possui simplesmente "Arial em tudo".

Em `14.6.1.css` existe:

```css
html, html * {
    font-family: "Open Sans","Arial",Roboto;
}

body {
    font-family:"Open Sans","Arial","Fira Sans";
    font-size:12px;
    font-weight:300;
}
```

Depois `15.9.7.css`, carregado por último, possui overrides específicos como:

```css
body {
    font-family:"Arial","Open Sans","Fira mono" !important;
}

tr {
    font-family:"Arial","Open Sans","Fira mono" !important;
}

td {
    font-family:"Arial","Open Sans","Fira mono" !important;
}
```

e outros seletores próprios.

Hoje o V3 simplificou isso para algo equivalente a:

```scss
$fonte: "Arial", "Fira Sans", sans-serif;
```

Isso NÃO reproduz a cascata original.

Resultado visível:

```text
menu superior com fonte diferente
títulos diferentes
texto contextual diferente
tabela diferente
pesos diferentes
métricas horizontais diferentes
```

Corrija a tipografia pelo comportamento real do CSS legado.

Não defina uma única fonte global por conveniência.

Mapeie quais elementos ficam com Open Sans e quais recebem override Arial/Fira Mono.

# FONTES OFFLINE

Não volte a depender do Google Fonts ou cellsystem.com.br em runtime.

Precisamos reproduzir a tipografia localmente.

Verifique primeiro se os arquivos necessários já existem nos dois repositórios.

Portar somente os assets/fontes realmente utilizados pelo TEMA V1 para uma estrutura local apropriada.

Criar `@font-face` local quando necessário.

Nenhuma fonte crítica do TEMA V1 deve depender da internet.

Depois valide no DevTools/Playwright qual `font-family` REAL foi computada para:

```text
body
.menu-up
.title-comicone
.TableListarFPEF-TR th
.Tabelinha-TD
OS
rodapé
```

Registre os computed styles antes/depois.

# ACHADO 2 - CLASSES CSS USADAS NAS VIEWS NÃO FORAM PORTADAS CORRETAMENTE

As novas views usam:

```text
TableListarFPEF-TR
Tabelinha-TD
Tabelinha-Table
```

Mas compare o Sass atual com o CSS legado.

No legado existem definições importantes como:

```css
.TableListarFPEF-TR {
    background-color:#2A2A2A;
    height:35px;
    color:#F8C18B !important;
    border:0px;
    padding:0px;
}

.TableListarFPEF-TR:hover {
    background-color:#2A2A2A;
}

.Tabelinha-TD {
    text-align:center;
    height:22px;
    padding-left:0px;
    font-size:11px;
    letter-spacing:1px;
    text-transform:uppercase;
}

.Tabelinha-Table {
    background-color:#363333;
    border:0px;
    padding:0px;
    width:100%;
    font-size:12px;
}

.Tabelinha-TR1 {
    text-align:left;
    background-color:#3F3F3F;
    height:30px;
    font-size:11px;
    text-transform:uppercase;
}

.Tabelinha-TR2 {
    text-align:left;
    background-color:#3B3B3E;
    height:30px;
    font-size:11px;
    text-transform:uppercase;
}

.Tabelinha-TR3 {
    text-align:left;
    background-color:#232320;
    height:30px;
    font-size:11px;
    text-transform:uppercase;
}
```

Faça um diff sistemático:

```text
seletores utilizados pelo HTML legado
x
seletores disponíveis no CSS legado
x
seletores utilizados pelos Blades V1
x
seletores realmente existentes no Sass V1 atual
```

Toda classe usada pelo Blade mas sem implementação fiel precisa ser corrigida.

Não faça uma tradução "parecida".

Valores numéricos devem vir do CSS legado.

# ACHADO 3 - CONCLUÍDOS ESTÁ USANDO A FAMÍLIA DE LINHA ERRADA

Este é um erro já identificado.

No legado:

```text
legacy-source/14.6.1/page/concluidos.php
```

a tabela NÃO utiliza `TrZebrada1/2` como zebra normal.

Ela utiliza:

```text
Tabelinha-TR1
Tabelinha-TR2
Tabelinha-TR3
```

Regra histórica:

```text
SEM GARANTIA -> Tabelinha-TR3
demais -> alternância Tabelinha-TR1 / Tabelinha-TR2
```

Essas linhas possuem aproximadamente 30px de altura.

Hoje:

```text
resources/views/temas/v1/rma/concluidos.blade.php
```

usa:

```php
classe_css_de_alerta(...)
```

que devolve a família:

```text
TrZebrada1
TrZebrada2
TrInconformidade
TrUrgente
...
```

Essas classes possuem 18px no CSS compartilhado.

Isso explica diretamente parte da diferença brutal de densidade entre os dois prints.

CORRIJA.

Não altere `classe_css_de_alerta()` globalmente de forma que quebre Entrada/Encaminhado.

A apresentação pode escolher a classe apropriada para cada superfície.

O domínio não precisa conhecer CSS.

# ACHADO 4 - AGUARDANDO CRÉDITO TEM O MESMO TIPO DE PROBLEMA

No legado:

```text
page/aguardandocredito.php
```

as linhas usam:

```text
Tabelinha-TR1
Tabelinha-TR2
```

e não a zebra compacta de 18px.

Corrija conscientemente.

# ACHADO 5 - ENTRADA E ENCAMINHADO SÃO DIFERENTES

Entrada e Encaminhado realmente usam:

```text
TrZebrada1
TrZebrada2
TrInconformidade
TrUrgente
```

conforme regras históricas.

Portanto NÃO transforme todas as quatro telas numa única tabela genérica visualmente idêntica.

Compartilhe componente somente onde o resultado final continuar exatamente igual.

Se a abstração exigir muitos `if` ou apagar diferenças históricas, prefira composição de componentes menores.

# ACHADO 6 - LARGURA DAS COLUNAS FOI PERDIDA

No PHP legado as larguras NÃO eram automáticas.

Para `Concluidos`, reproduza exatamente:

```text
DATA        8%
NF C        5%
NF V        5%
FABRICANTE 12%
DESCRICAO  14%
ORIGEM     10%
MODELO     18%
S/N        17%
VALOR       6%
OS          4%
```

Não "corrija" a soma para 100%.

O legado possui esses valores e queremos reproduzir o navegador histórico conscientemente.

Para `Entrada`:

```text
RECEBIDO     8%
ORIGEM      10%
NF C          6%
NF V          6%
FABRICANTE   13%
DESCRICAO    12%
MODELO       18%
S/N          17%
VALOR         6%
OS            4%
```

Para `Encaminhado`:

```text
DATA          8%
ORIGEM       10%
NF C          6%
FABRICANTE   13%
DESCRICAO    13%
MODELO       18%
NF R          6%
PROTOCOLO     8%
DESTINATARIO 14%
OS            4%
```

Para `Aguardando credito`:

```text
ENTRADA       8%
NF C          5%
FABRICANTE   12%
DESCRICAO    13%
ORIGEM        9%
MODELO       18%
NF R          5%
PROTOCOLO     8%
VALOR         6%
DESTINATARIO 12%
OS            4%
```

Pode usar:

```html
<colgroup>
```

ou classes específicas da tabela para evitar style inline.

Mas o resultado calculado deve ser igual.

Não deixe o algoritmo automático do browser redistribuir livremente as colunas.

# ACHADO 7 - ESTRUTURA DO CABEÇALHO INTERNO ESTÁ ERRADA

`Concluidos` no legado começa com:

```text
[icone 50x50] Os produtos abaixo ja retornaram...
-------------------------------------------------
[TABELA]
```

O PHP original usa:

```text
concluido.png
title-icone fl
title-comicone fl
hr.both
```

Hoje a view nova possui somente:

```text
texto
tabela
```

e o layout adiciona ainda:

```text
<h1 class="titulo-v1">Concluido</h1>
```

Esse H1 não pertence à composição original dessa tela.

Corrija.

Não remova cegamente o heading de todas as telas.

Faça o layout permitir que cada superfície V1 reproduza sua composição histórica.

Pode existir um contrato de layout, por exemplo:

```text
mostrarTituloPadrao = false
```

ou seção Blade equivalente.

O importante é não injetar conteúdo visual que o legado não possuía.

# ASSETS DAS QUATRO TELAS

O V3 atualmente possui basicamente:

```text
public/images/tema-v1/ferramenta-logo.png
```

Localize e porte do legado os assets reais necessários:

```text
entrada.png
encaminhado.png
pendente.png
concluido.png
```

Use arquivos locais.

Não use URLs externas.

Preserve:

```text
50x50
posicionamento
margens
float
espaçamento com descrição
```

Não redesenhe os ícones.

# ACHADO 8 - CONCLUÍDOS ESTÁ INCOMPLETO

Depois da tabela, o legado ainda possui:

```text
VALOR TOTAL: R$ ...
DATA DO PROCESSAMENTO: ...
Quantidade Total de produtos: ...
Quantidade dos produtos a cima que nao participaram da contagem monetario: ...
```

O Blade V1 atual não reproduz essa parte.

Restaure o comportamento.

Não execute SQL dentro do Blade.

Calcule por caso de uso/read model/controller e entregue os valores prontos para apresentação.

Preserve inclusive:

```text
font-size
float
margin
letter-spacing
formatação monetária
ordem
textos históricos
```

Se existir erro ortográfico histórico visível, não "melhore" silenciosamente no TEMA V1 sem registrar a decisão.

# FORMATAÇÃO DOS DADOS

Compare também saída real dos valores.

O legado usa, por exemplo:

```php
number_format($x, 2, '.', '')
```

em diversas listagens.

O V3 atualmente usa em alguns lugares:

```php
number_format($valor, 2, ',', '.')
```

Isso é funcionalmente mais brasileiro, mas NÃO é reprodução literal.

TEMA V1 deve seguir a representação histórica quando ela fizer parte da interface.

TEMA V2/V3 podem possuir apresentação moderna.

Compare ainda:

```text
origem
datas
NF vazia versus zero
OS
uppercase
quebra de linha
```

# LINKS NAS CÉLULAS

No legado vários valores são:

```html
<td>
    <a>
        <div>valor</div>
    </a>
</td>
```

O V3 em vários casos envolve somente o texto no `<a>`.

Isso altera a área clicável.

Não precisa reproduzir HTML inválido ou antigo, mas reproduza a área clicável e o comportamento.

Exemplo moderno aceitável:

```css
.Tabelinha-TD > a {
    display:block;
    width:100%;
    height:100%;
}
```

se isso reproduzir o comportamento original.

# MENU SUPERIOR

Depois da correção tipográfica, revise novamente o header.

Deve ficar visualmente equivalente ao legado:

```text
[raposa]
Pag. Inicial
Novo
Localizar
Entrada
Encaminhado
Aguardando credito
Concluido!
                                      MENU SIGN OUT
```

Observe que o legado usa:

```text
Concluido!
```

com exclamação.

Não aceite pequenas diferenças de:

```text
fonte
peso
posição vertical
espaçamento horizontal
altura da barra
tamanho dos botões
alinhamento
```

somente porque os links já funcionam.

# TAMANHO FIXO DO TEMA V1

Não modernize o layout.

O legado possui:

```css
#BASE {
    width:984px;
    padding-left:10px;
    padding-right:10px;
}

#TOPO {
    width:984px;
}

#CONTEUDO {
    width:984px;
}
```

O TEMA V1 deve continuar fixo.

Não introduza:

```text
max-width responsivo
Bootstrap container
grid responsivo
width:90%
flex-grow para preencher viewport
media query
```

onde o legado não possuía.

# MUITO IMPORTANTE - NORMALIZAR O AMBIENTE DE COMPARAÇÃO

Os screenshots humanos enviados apresentam diferença de escala global.

Antes de mexer em largura para "bater o print", elimine zoom como variável.

Use Playwright com:

```text
browser zoom 100%
deviceScaleFactor 1
mesma viewport
mesma família de navegador
mesmo DPR
```

Comece com:

```text
1440 x 1000
```

e também faça:

```text
1562 x 1400
1700 x 1000
```

se necessário.

Não compare uma janela manual `:8094` possivelmente em 110% com `:8095` em 100% e depois altere CSS para compensar.

Primeiro prove as dimensões por `getBoundingClientRect()`.

Capture para Legado e V3:

```text
#BASE
#TOPO
#CONTEUDO
.Tabelinha-Table
.TableListarFPEF-TR
primeiro tbody tr
primeiro td
cada th
.title-comicone
imagem do título
```

Para cada elemento registre:

```text
x
y
width
height
font-family
font-size
font-weight
line-height
letter-spacing
color
background-color
margin
padding
border
```

O objetivo não é estimar.

O objetivo é medir.

# CORRIJA A FUNDAÇÃO ANTES DAS TELAS

Ordem obrigatória:

## CP1 - Cascade e fontes

Corrigir:

```text
ordem 14.6.1.css x 15.9.7.css
Open Sans local
Arial/Fira Mono nos seletores corretos
font sizes
font weights
line heights
letter spacing
```

Validar header + uma tabela.

Commit local.

## CP2 - Primitivas reais da tabela V1

Portar fielmente:

```text
Tabelinha-Table
TableListarFPEF-TR
Tabelinha-TD
Tabelinha-TR1
Tabelinha-TR2
Tabelinha-TR3
TrZebrada1
TrZebrada2
TrInconformidade
TrUrgente
```

Não duplicar regra já fiel em `_compartilhado.scss`.

Commit local.

## CP3 - Composição das quatro listagens

Corrigir:

```text
Entrada
Encaminhado
Aguardando credito
Concluido
```

Restaurar:

```text
ícones
title-comicone
hr
classes corretas de linha
colunas exatas
área clicável
formatação
```

Commit local.

## CP4 - Concluídos completo

Restaurar:

```text
VALOR TOTAL
DATA DO PROCESSAMENTO
quantidades
```

sem SQL no Blade.

Commit local.

## CP5 - Propagação

Depois de as primitivas estarem corretas, procure em TODAS as views V1 por:

```text
Tabelinha-Table
TableListarFPEF-TR
Tabelinha-TD
Tabelinha-TR1
Tabelinha-TR2
Tabelinha-TR3
TrZebrada
title-comicone
title-icone
menu-up
```

Identifique quais telas já serão corrigidas automaticamente pela fundação.

Não faça patches redundantes por tela.

Se uma regra é realmente global no legado, deve ser global no TEMA V1 moderno também.

# NÃO QUERO

Não quero:

```text
"aproximadamente igual"
"visualmente semelhante"
"mais moderno"
"melhor UX"
"responsivo"
"ajustei a olho"
"Bootstrap equivalente"
"tabela genérica"
"font-family parecida"
```

Não quero números inventados.

Se no legado existe:

```css
height:30px
```

use 30px.

Se existe:

```css
margin-left:15px
```

use 15px.

Se existe:

```css
color:#F8C18B
```

use #F8C18B.

A arquitetura pode ser nova.

Os números visuais não precisam ser reinventados.

# NÃO COPIAR CEGAMENTE O PHP ANTIGO

A fidelidade é de comportamento e apresentação.

Não portar:

```text
SQL no Blade
session procedural
PHP procedural
JavaScript inseguro
dependência externa antiga
código morto
```

Portar:

```text
CSS efetivamente computado
assets
geometria
tipografia
estados
interação
ordem das informações
formatação visível
```

# TESTES

Depois das correções:

1. rode os testes focados;
2. rode a suíte completa;
3. rode build Vite;
4. faça Playwright visual;
5. faça comparação Legado x V3.

Não atualize golden screenshot simplesmente porque mudou.

Golden só pode ser atualizado quando a nova imagem estiver comprovadamente mais próxima do runtime legado.

Adicione regressões objetivas para:

```text
largura #BASE = 984px
largura #CONTEUDO = 984px
Concluidos header = 35px
Concluidos row = 30px
larguras das colunas
fonte computada do menu
fonte computada da tabela
presença do ícone 50x50
ausência do H1 artificial em Concluidos
presença do resumo inferior
```

Evite teste acoplado a detalhes que não são contratos históricos, mas estes valores são contratos de paridade do TEMA V1.

Comparação:

Tela com problema (V3): /home/legionario/github/08.24.1-gerenciador-de-rma/docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/(V3) Tela de concluido.png

Referência (Legado): /home/legionario/github/08.24.1-gerenciador-de-rma/docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/(Legado) Tela de concluido.png

# CHECKLIST

Não crie roadmap paralelo.

Atualize:

```text
docs/produto/checklist-master-v3.md
docs/produto/checklist-paridade-visual-v1-runtime.md
```

Se algum item anteriormente marcado `[x]` for contradito por esta correção, reabra-o e registre a evidência.

Não apague o histórico.

# CRITÉRIO DE ACEITE

Não encerre a tarefa dizendo somente:

```text
build passou
testes passaram
```

Quero no retorno final uma tabela de prova semelhante a:

```text
Elemento             Legado      V3         Resultado
#BASE width           984px       984px      OK
#CONTEUDO width       984px       984px      OK
header tabela         35px        35px       OK
linha Concluidos      30px        30px       OK
DATA                   8%          8%         OK
NF C                   5%          5%         OK
...
menu font             Open Sans   Open Sans  OK
td font               Arial       Arial      OK
icone                 50x50       50x50      OK
```

Inclua também os caminhos dos screenshots Legado e V3 gerados na mesma viewport.

Se ainda houver diferença perceptível, NÃO declare paridade concluída.

Identifique o seletor responsável e continue.

Comece agora por CP1, CP2 e pela tela `Concluido`, porque ela demonstra de forma objetiva quase todos os problemas sistêmicos atuais.

Depois aplique a fundação corrigida em Entrada, Encaminhado e Aguardando Crédito antes de continuar para outra frente.
