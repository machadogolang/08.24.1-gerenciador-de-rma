# INV-RMA-BUG-LAYOUT

Quero interromper temporariamente qualquer tentativa de considerar a paridade visual do TEMA V1 concluída.

Exemplo de falhas visuais em: /home/legionario/github/08.24.1-gerenciador-de-rma/docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT

Observação: Não somente se limita a diferenças visuais, como também não foi possível reproduzir algumas telas do legacy no V3, como tela de Entrada e Concluido.

Temos **nova evidência de runtime mostrando que o checkpoint visual anterior produziu falsos positivos**.

O objetivo do TEMA V1 continua sendo:

> reproduzir visual e interação do CellSystem RMA 14.6.1 com a maior fidelidade possível, preservando a arquitetura moderna Laravel no backend.

Não quero uma interpretação moderna do 14.6.1.

Não quero “inspirado no legado”.

Para o **TEMA V1**, quero reprodução consciente do legado.

## FONTES DE VERDADE

Projeto novo:

```text
machadogolang/08.24.1-gerenciador-de-rma
http://localhost:8095
```

Legado:

```text
machadogolang/08.24.4-legacy-gerenciador-de-rma
http://localhost:8094/14.6.1/
```

Fonte principal do legado:

```text
legacy-source/14.6.1/
legacy-source/pattern/14.6.1.css
legacy-source/pattern/14.6.1.js
legacy-source/pattern/15.9.7.css
legacy-source/pattern/15.9.7.js
```

O runtime real de `:8094` prevalece sobre suposições feitas a partir de nomes de classes.

---

# 1. PRIMEIRO: REABRIR O CHECKPOINT VISUAL INCORRETO

Hoje temos:

```text
docs/produto/paridade-visual-tema-v1.md
```

declarando o checkpoint desktop 1440px como concluído.

Temos também no checklist mestre itens como:

```text
[x] F10-V1-01
[x] F10-V1-02
[x] F10-V1-03
[x] F10-V1-04
[x] F10-V1-07
[x] F10-V1-08
```

Porém a inspeção humana atual encontrou divergências estruturais que contradizem essa conclusão.

NÃO apague o histórico.

Registre que o checkpoint foi **reaberto por nova evidência de runtime** e reabra os itens cuja conclusão não é mais defensável.

Particularmente, reavalie obrigatoriamente:

```text
F10-V1-01 — comparação runtime
F10-V1-02 — auditoria HTML/CSS
F10-V1-03 — cabeçalho/menu/painel
F10-V1-04 — usuários/busca/novo RMA
F10-V1-07 — regressão Playwright
F10-V1-08 — diferenças classificadas como conscientes
```

Assets/fontes que estiverem realmente corretos não precisam ser artificialmente reabertos.

A regra passa a ser:

> `[x]` anterior não é prova de paridade se a comparação visual atual demonstrar o contrário.

---

# 2. ACHADOS JÁ CONFIRMADOS — NÃO PRECISA REINVESTIGAR DO ZERO

Estes problemas já foram percebidos visualmente e devem entrar imediatamente no checklist.

## VIS-V1-001 — menu superior incompleto

Legado:

```text
Pag. Inicial
Novo
Localizar
Entrada
Encaminhado
Aguardando credito
Concluido!
MENU
SIGN OUT
```

V3 atual:

```text
Pag. Inicial
Novo
Localizar
MENU
SIGN OUT
```

Faltam no header do TEMA V1:

```text
Entrada
Encaminhado
Aguardando credito
Concluido!
```

Investigue as rotas modernas correspondentes e restaure os atalhos no mesmo posicionamento e comportamento visual do legado.

Não crie links mortos.

Se alguma capacidade ainda não existir, isso vira também pendência funcional F10.

---

# 3. VIS-V1-002 — “NOVO” FOI TRANSFORMADO EM OUTRA EXPERIÊNCIA

No legado:

```text
Novo
```

não é simplesmente uma página independente.

`NovoMaximize()` torna visível:

```text
#JS-Novo
```

dentro da própria superfície atual.

Isso permite, por exemplo, estar em:

```text
Aguardando credito
```

abrir `Novo` e continuar vendo abaixo a listagem/contexto de Aguardando Crédito.

Foi exatamente isso que apareceu no runtime original comparado agora.

No V3, `Novo` navega para:

```text
/rmas/create
```

e troca a superfície inteira.

Isso NÃO é paridade de interação.

Investigue como reproduzir o comportamento do legado dentro do TEMA V1 sem introduzir PHP procedural.

Pode usar Blade + JavaScript moderno, mas o resultado visual/interacional deve equivaler ao original.

TEMA V2 e futuro TEMA V3 não precisam copiar essa apresentação.

A funcionalidade deve continuar compartilhada.

---

# 4. VIS-V1-003 — NOVO RMA ESTÁ ESTRUTURALMENTE ERRADO

O runtime atual do V3 apresenta aproximadamente:

```text
Novo RMA

DESCRIÇÃO       [             ]
FABRICANTE      [             ]
FORNECEDOR      [             ]
MODELO          [             ]
SN              [             ]
OS              [             ]
ORIGEM          [             ]
EMPRESA         [             ]
CLIENTE         [             ]
DEFEITO         [             ]
OBSERVAÇÃO      [             ]

[ Salvar ]
```

Isso é muito diferente do legado.

O legado apresenta uma composição horizontal compacta semelhante a:

```text
[ÍCONE] Você pode adicionar um boletim de defeito para o Setor de RMA

DESCRICAO [........]  ORIGEM [........]  NF C [......] DATA [00/00/2015]
SNID      [........]  FABRICANTE [...]   NF V [......] DATA [00/00/2015]
S/N       [........]  MODELO [........]  P/N  [......] OS   [........]
EMPRESA   [........]  DEFEITO [........................................]
CLIENTE   [........]  OBS     [........................................]

                                                   [ CRIAR BD ]

[          O ITEM E DO ESTOQUE          ][ controle correspondente ]
```

Além do layout, existem diferenças funcionais.

O legado possui campos que não aparecem no formulário V1 atual, entre eles:

```text
SNID
NF de compra
data da NF de compra
NF de venda
data da NF de venda
P/N
marcação de item em estoque
```

Não simule esses campos apenas para o print ficar igual.

Para cada um:

1. verificar se o domínio V3 já possui o dado;
2. verificar caso de uso;
3. persistência;
4. validação;
5. migração;
6. exposição nos três temas.

Se já existir no domínio, conectar corretamente.

Se estiver faltando na reconstrução, abrir pendência funcional e implementar conforme o plano.

Visual sem função NÃO será aceito.

---

# 5. VIS-V1-004 — CSS DO NOVO RMA NÃO CORRESPONDE AO ORIGINAL

Já existe diferença objetiva no código.

No legado:

```css
.tablenovo {
    height: 22px;
    text-align: left;
    margin: 0;
    border: 0;
    font-size: 13px;
    width: 700px;
}

.novo_formInput {
    height: 22px;
    width: 100%;
    color: #C3FF00;
    padding-left: 2px;
    padding-right: 2px;
}
```

Também existem:

```text
.novo_formInputDATE
.novo_formInputSmall
.formSelectempresa
.formButtonEnviarNovo
.novo_defeito
.formInputObservacao
```

No V3 atual, `.tablenovo` foi generalizada para `width:100%` e vários desses comportamentos foram simplificados.

Isso explica parte relevante da diferença vista no print.

Não aplique correção aproximada.

Leia os seletores efetivamente utilizados pelo HTML do legado e reproduza a geometria correspondente no TEMA V1.

---

# 6. VIS-V1-005 — QUADRO DE ANOTAÇÕES

No legado:

* largura de aproximadamente 675px;
* sidebar aproximadamente 280px;
* título/ícone próprios;
* textarea segue geometria própria;
* o salvamento ocorre através do comportamento do campo;
* não existe aquele grande botão permanente “Salvar anotação” mostrado atualmente no V3.

No V3 atual existe:

```text
[ Salvar anotação ]
```

alterando tanto o visual quanto a interação.

Investigue e reproduza no TEMA V1 a experiência correspondente ao legado, mas mantendo um mecanismo seguro e moderno de persistência.

Se usar autosave:

* debounce;
* feedback discreto;
* proteção contra chamadas concorrentes;
* tratamento de erro.

Não trazer de volta implementação insegura do JavaScript antigo.

---

# 7. VIS-V1-006 — HOME / RMA LISTAGEM ESTÁ COM COMPOSIÇÃO DIFERENTE

Nos prints atuais o legado possui no topo a área de localização com:

```text
[campo de pesquisa]

[ QUALQUER UMA SOLUCAO ]
[ TODOS OS CAMPOS ]
[ FILTRAR ]
```

seguida pelo quadro de anotações e sidebar.

No V3 atual temos outra composição:

```text
Buscar por [Texto]
[campo]
[Buscar]

Nenhum RMA encontrado.
```

antes do quadro de anotações.

Não considere equivalentes apenas porque ambos “possuem pesquisa”.

Compare:

* número de controles;
* ordem;
* labels;
* valores disponíveis;
* largura;
* altura;
* posicionamento;
* comportamento;
* resultado;
* filtros;
* estados.

Reproduzir no TEMA V1 o fluxo histórico ou registrar justificativa objetiva para qualquer diferença.

---

# 8. VIS-V1-007 — TÍTULOS QUE NÃO EXISTIAM NA COMPOSIÇÃO ORIGINAL

O layout V1 atual injeta:

```text
<h1 class="titulo-v1">...</h1>
```

nas superfícies.

Isso gera, por exemplo:

```text
Novo RMA
```

acima do formulário atual.

Verifique tela por tela se o legado realmente apresentava esse heading.

Não padronize H1 apenas porque é uma prática moderna.

No Tema V1, a hierarquia visual deve seguir o legado.

Semântica/acessibilidade pode continuar existindo de maneira não destrutiva, mas não deve redesenhar a tela.

---

# 9. VIS-V1-008 — MENU ADMINISTRATIVO INCOMPLETO

O MENU do legado possui:

```text
Fornecedores
Fabricantes
Assistencias
Clientes
Controle
Creditos
Relatorios
Usuarios
Trocar p/ 15.8.1
```

além de uma entrada temporária em determinado contexto.

O V3 atualmente possui basicamente:

```text
Fornecedores
Fabricantes
Assistências
Clientes
Usuários
```

Investigue cada item ausente.

Classifique como:

```text
MANTER
SUBSTITUÍDO
NÃO RECONSTRUIR
PENDENTE FUNCIONAL
PENDENTE VISUAL
NÃO APLICÁVEL
```

Não remover silenciosamente.

`Trocar p/ 15.8.1`, por exemplo, pode legitimamente não fazer sentido na arquitetura nova.

Mas essa decisão precisa ser explícita.

Controle, Créditos e Relatórios não podem simplesmente desaparecer se a funcionalidade correspondente existe no V3.

---

# 10. INVESTIGAÇÃO COMPLETA TELA A TELA

Agora lance SUBAGENTS DE INVESTIGAÇÃO, SOMENTE LEITURA.

Não permita que vários agents alterem os mesmos arquivos simultaneamente.

Quero pelo menos:

## Agent A — inventário do runtime legado

Mapear todas as superfícies acessíveis a partir de:

```text
http://localhost:8094/14.6.1/
```

Percorrer:

### navegação superior

```text
Pag. Inicial
Novo
Localizar
Entrada
Encaminhado
Aguardando credito
Concluido!
```

### MENU

```text
Fornecedores
Fabricantes
Assistências
Clientes
Controle
Créditos
Relatórios
Usuários
```

### demais fluxos descobertos

Incluindo:

```text
detalhes
edição
resultados de busca
status
soluções
destinatários
histórico
ações internas
```

Não limitar o inventário a esta lista.

Percorra também:

```text
legacy-source/14.6.1/page/
legacy-source/14.6.1/inc/
legacy-source/14.6.1/menujs-top/
legacy-source/14.6.1/menujs-right/
```

e derive as telas reais.

---

## Agent B — comparador visual/runtime

Para cada superfície legada, encontrar a equivalente em:

```text
http://localhost:8095
```

Capturar os dois lados no MESMO viewport.

Começar por:

```text
1440px
```

e repetir os pontos necessários do plano em:

```text
768px
390px
```

Como os prints humanos que reabriram esta investigação também foram feitos em desktop largo, faça ainda uma amostra em aproximadamente:

```text
1700px
```

para eliminar qualquer falso positivo relacionado somente à largura da viewport.

---

## Agent C — comparador de interação

Não olhar apenas screenshot.

Comparar:

* clique;
* expansão;
* recolhimento;
* menus;
* hover relevante;
* active;
* formulário;
* pesquisa;
* filtros;
* submit;
* autosave;
* listagens;
* contexto preservado depois da ação.

Exemplo já comprovado:

```text
Novo
```

no legado EXPANDE uma área.

No V3 NAVEGA para outra página.

Um screenshot isolado pode não revelar essa divergência.

---

# 11. CHECKLIST VIVO

Não crie outro roadmap paralelo.

Use:

```text
docs/produto/checklist-master-v3.md
```

como fonte operacional principal.

Crie ou adapte um documento auxiliar específico para esta auditoria, indexado pela seção F10 visual, por exemplo:

```text
docs/produto/checklist-paridade-visual-v1-runtime.md
```

Cada divergência encontrada deve imediatamente virar:

```text
[ ] VIS-V1-XXX — nome objetivo
```

Formato obrigatório:

```text
ID:
Tela:
Legacy:
V3:
Categoria:
  [ ] estrutura
  [ ] geometria
  [ ] tipografia
  [ ] cor
  [ ] espaçamento
  [ ] controle ausente
  [ ] comportamento
  [ ] funcionalidade
  [ ] asset

Evidência:
Problema:
Fonte Legacy:
Fonte V3:
Arquivos candidatos:
Critério de aceite:
Screenshot antes:
Screenshot depois:
Teste:
Status:
```

Não marcar `[x]` pela intenção.

Só marcar quando existir:

```text
runtime Legacy
+
runtime V3
+
comparação depois da correção
+
teste
```

---

# 12. PROCESSO CONTÍNUO: ENCONTROU → REGISTROU → CORRIGIU

Não quero:

```text
Investiguei 42 telas.
Aqui está um documento.
Aguardando autorização para corrigir.
```

Quero:

```text
Tela 1:
encontrou divergências
→ adicionou ao checklist
→ corrigiu
→ testou
→ screenshot depois
→ marcou concluído

Tela 2:
...
```

Continue até acabar o lote seguro.

Somente pare quando:

* existir decisão humana;
* faltar regra de negócio que não possa ser inferida;
* houver risco de dados;
* houver conflito real com trabalho de outra frente.

---

# 13. ORDEM INICIAL DE EXECUÇÃO

Comece pelos defeitos já comprovados:

```text
1. Cabeçalho/menu superior
2. Home /rmas
3. interação de Novo
4. formulário Novo RMA
5. Localizar/filtros
6. Aguardando Crédito
7. Entrada
8. Encaminhado
9. Concluído
10. Detalhe/edição
11. Usuários
12. Clientes
13. Fabricantes
14. Fornecedores
15. Assistências
16. Controle
17. Créditos
18. Relatórios
19. demais telas descobertas
```

A ordem pode ser ajustada somente por dependência objetiva.

---

# 14. NÃO CONFUNDIR DADOS DIFERENTES COM ERRO VISUAL

Os bancos podem possuir massas diferentes.

Exemplo:

```text
Legacy: 1379 itens
V3 QA: 60 itens
```

Isso sozinho NÃO é defeito visual.

Ignore conteúdo dinâmico ao comparar:

* IDs;
* nomes;
* quantidades;
* datas;
* valores.

Mas compare obrigatoriamente:

* existência do bloco;
* quantidade de colunas;
* ordem das colunas;
* largura;
* alinhamento;
* estilo;
* hierarquia;
* controles;
* navegação;
* comportamento.

---

# 15. NÃO COPIAR O PHP LEGADO

O legado é a especificação visual/funcional.

NÃO é a arquitetura a copiar.

Não quero de volta:

```text
SQL em view
include procedural
$_POST espalhado
JS global desorganizado
PHP misturado sem fronteira
```

O resultado deve ser:

```text
arquitetura moderna
+
domínio atual
+
casos de uso atuais
+
Laravel
+
aparência/interação fiel ao legado no TEMA V1
```

---

# 16. FUNCIONALIDADE DESCOBERTA NO LEGADO

Se durante a comparação aparecer uma funcionalidade que o V3 perdeu:

NÃO falsifique a tela para parecer que existe.

Adicione simultaneamente:

```text
[ ] VIS-V1-XXX — restaurar apresentação
[ ] PAR/FUN-XXX — restaurar capacidade funcional
```

E verifique Tema V2.

Toda funcionalidade de produto deve continuar disponível nos temas suportados, ainda que cada tema a apresente de forma diferente.

O TEMA V1 deve ser fiel ao legado.

O TEMA V2 pode possuir sua própria composição.

O futuro TEMA V3 terá sua própria composição.

Mas nenhum deles deve perder capacidade funcional.

---

# 17. PLAYWRIGHT ATUAL DEU FALSO POSITIVO

Isto é especialmente importante.

Hoje existe:

```text
tests/Browser/ParidadeVisualTemaV1.spec.ts
```

e mesmo assim chegamos a um estado onde o teste passou enquanto:

* menu estava incompleto;
* Novo tinha outra navegação;
* formulário tinha outra estrutura;
* campos estavam ausentes;
* composição da home era diferente.

Portanto não aceite simplesmente:

```text
Playwright verde = paridade visual
```

Audite o teste.

Descubra POR QUE ele não detectou essas diferenças.

Acrescente cobertura para:

### Header

```text
itens existentes
ordem
posição
active
```

### Novo

```text
abre inline
não destrói contexto da tela
estrutura dos campos
ordem dos campos
campos obrigatórios
checkbox estoque
botão
ícone/título
```

### Home

```text
localização/filtros
quadro de anotações
sidebar
centro de avisos
```

### Layout

Use `getBoundingClientRect()` nos blocos importantes e compare:

```text
x
y
width
height
```

Compare também computed styles relevantes:

```text
display
position
margin
padding
font-family
font-size
font-weight
line-height
letter-spacing
background
border
color
width
height
```

Para screenshot regression, mascare apenas valores realmente dinâmicos.

Não masque a estrutura da página.

---

# 18. TESTE VISUAL NÃO PODE SER FRACO DEMAIS

Quero distinguir:

```text
PARIDADE ESTRUTURAL
PARIDADE GEOMÉTRICA
PARIDADE VISUAL
PARIDADE DE INTERAÇÃO
PARIDADE FUNCIONAL
```

Uma tela não fica `[x]` porque apenas:

```text
#BASE = 984px
```

bateu.

A estrutura interna também precisa bater.

---

# 19. COMMITS

Faça commits locais pequenos.

Exemplos:

```text
#F10-VIS - Reabre gate V1 após divergências comprovadas em runtime

#F10-VIS - Restaura navegação superior do Tema V1

#F10-VIS - Reproduz composição histórica da home do Tema V1

#F10-VIS - Restaura interação inline do Novo RMA

#F10-VIS - Reproduz geometria e campos históricos do Novo RMA

#F10-VIS - Reforça regressão Playwright contra falsos positivos

#F10-VIS - Fecha paridade da tela Aguardando Crédito
```

Use IDs do checklist quando definidos.

Não faça push.

---

# 20. PRIMEIRO RETORNO SOMENTE DEPOIS DE EXECUTAR

Não quero outro parecer sem código.

Quero um retorno depois de pelo menos o primeiro conjunto de correções contendo:

```text
HEAD inicial:
HEAD final:

GATE REABERTO
- itens:
- motivo:

DIVERGÊNCIAS JÁ CONHECIDAS
- VIS-V1-001 ...
- VIS-V1-002 ...
...

NOVAS DIVERGÊNCIAS ENCONTRADAS
- ...

CORRIGIDAS NESTA SESSÃO
- ...

TELAS COMPARADAS
Legacy                     V3                     status
/                          /rmas                  ...
Novo inline                ...                    ...
?page=entrada              ...                    ...
?page=aguardandocredito    ...                    ...
...

PLAYWRIGHT
- por que o teste anterior deu falso positivo:
- quais asserts foram acrescentados:

EVIDÊNCIA
- screenshots antes:
- screenshots depois:

TESTES
- PHPUnit:
- Vite:
- Playwright:

COMMITS
- hash — mensagem

PRÓXIMA TELA
- ...
```

A prioridade agora é:

> **não avançar para Tema V3 fingindo que o Tema V1 já atingiu baseline. Primeiro tornar o Tema V1 realmente fiel ao 14.6.1, tela por tela, e transformar essa fidelidade em regressão automatizada suficientemente forte para que o mesmo problema não volte a acontecer.**
