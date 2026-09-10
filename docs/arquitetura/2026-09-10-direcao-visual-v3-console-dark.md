# Direcao visual do Tema V3 - "Console Operacional Dark"

Data: 2026-09-10 (America/Sao_Paulo)
Status: APROVADA COMO NORTE VISUAL (documental). Implementacao ampla nas tasks
T3-13+; nesta rodada apenas a entrada segura "Previa V3".
Relacionado: `2026-09-09-refinamento-evo-ux-001-tema-v3-console-operacional.md`
(arquitetura/informacao), `INV-RMA-08-tema-v3-mobile-first.md` (shell/mobile).

## 1. Decisao

O dono definiu uma nova referencia visual para o Tema V3. Ela NAO e copia de
produto: a pagina de erro/debug moderna do Laravel (Ignition) serve como
REFERENCIA DE LINGUAGEM VISUAL, nao como fonte de HTML, componentes ou assets.

DIREICAO ANTERIOR: V3 claro (fundo ~`#f3f5f7`, surface branca, rail azul escuro).
**SUPERADA.**

DIREICAO NOVA: **Console Operacional Dark**. **APROVADA COMO NORTE VISUAL.**

Nao e necessario apagar documentos historicos. A nova decisao e registrada por
data e passa a ter precedencia sobre a direcao anterior.

## 2. Intencao

O V3 deve continuar parecendo descendente do CellSystem V1/V2 (densidade, fundo
escuro, identidade historica, postura de ferramenta de trabalho), mas com
linguagem propria, moderna e tecnica. Ou seja: o V3 nao e "o V2 com outro CSS";
e uma ideia nova, ancorada na mesma historia.

O que chama atencao na referencia conceitual:

- fundo quase preto/grafite;
- aparencia limpa e tecnica;
- muito espaco bem controlado;
- conteudo centralizado com largura maxima clara;
- linhas/bordas extremamente discretas;
- malha/pontos de fundo quase imperceptiveis;
- tipografia branca/cinza com excelente hierarquia;
- detalhes em vermelho somente onde necessario;
- paineis retos/discretos;
- aspecto de console/diagnostico;
- informacao densa sem parecer sistema antigo;
- poucos elementos decorativos;
- contraste alto; sensacao moderna e profissional.

## 3. Tokens (ponto de partida, refinar por contraste/browser)

Nao fixar cegamente: medir contraste e ajustar com o browser.

| Token | Valor inicial |
|---|---|
| `background` principal | `#0d0f12` / `#101216` |
| `surface` | `#15181d` / `#181b20` |
| `surface-elevada` | `#1d2127` |
| `border` | `rgba(255,255,255,.08)` a `.12` |
| texto principal | `#f2f4f7` |
| texto secundario | `#9ca3ad` |
| acento historico (brand) | familia crimson/vinho do CellSystem, ex.: `#b40b3d` / `#c20d47` |
| success/warning/error | semantico, sem reinventar |

Tipografia:

- IDs tecnicos, NF, S/N, protocolo: monoespacada quando agrega leitura;
- texto comum: `Instrument Sans`/system atual.

## 4. Fundo

Malha MUITO discreta inspirada no debug moderno, via
`radial-gradient(...)`/CSS equivalente, sem imagem externa. Ela deve ser
praticamente imperceptivel e nunca virar background chamativo.

## 5. Estrutura

Preservar a arquitetura ja pensada (Topbar, Rail lateral recolhivel, Main,
Drawer mobile), redesenhando o shell para esta linguagem.

- Desktop: topbar baixa/densa; rail grafite; main central; largura maxima por
  categoria; bordas finas; sem card branco.
- Mobile: drawer; sem overflow; alvo de toque >= 44px; mesma hierarquia.

## 6. Componentes

Aplicar a linguagem de paineis finos/secoes (como a pagina de exception divide
stack trace e contexto) em: PageHeader, ContextHeader, ActionBar, FilterBar,
DataTable, RecordCard mobile, FormSection, Timeline, Audit/Event list,
EmptyState, ValidationSummary, AlertPanel.

Exemplo de detalhe de RMA V3 (sem 15 cards coloridos):

```
RMA #2894                         [status] [prioridade]

Produto / identificacao
--------------------------------------------

Fiscal
--------------------------------------------

Parceiros e destino
--------------------------------------------

Logistica
--------------------------------------------

Solucao e credito
--------------------------------------------

Historico
--------------------------------------------
```

## 7. Dashboard

Evitar "dashboard generico SaaS". A aparencia desejada e de WORKBENCH:

1. topo: busca global + `Novo RMA`;
2. `FILAS OPERACIONAIS` (Entrada, Recebidos, Encaminhados, Pendente credito);
3. `EXCECOES / AVISOS`;
4. `ATIVIDADE RECENTE`.

Somente dados REAIS. Nenhum grafico decorativo.

## 8. Tabelas e formularios

- Tabelas: densas, cabecalho sticky quando util, hover discreto, zebra quase
  imperceptivel, status legivel, acoes contextuais, numeros alinhados, sem
  linhas enormes. Mobile: cards equivalentes sem perder informacao ou acao.
- Formularios: label pequeno, campo escuro, borda fina, focus claro, grupos
  logicos, pouco ruido. Nao fazer formulario branco em fundo cinza e NAO
  reproduzir a geometria V1/V2 no V3.

V1/V2 = PARIDADE HISTORICA. V3 = EVOLUCAO.

## 9. Escopo desta rodada

1. DOCUMENTAR a nova direcao (este documento);
2. atualizar OpenSpec/PLANO/T3 quando necessario;
3. implementar apenas o necessario para a entrada segura "Previa V3"
   (`config/temas.php`, entrada discreta no V1/V2, "Voltar ao sistema" no V3);
4. NAO parar o fechamento da paridade V1/V2 para redesenhar telas V3.

O redesenho amplo entra quando as tasks V3 correspondentes voltarem a fila
(T3-13, T3-15, T3-16, T3-18, T3-19 e seguintes).
