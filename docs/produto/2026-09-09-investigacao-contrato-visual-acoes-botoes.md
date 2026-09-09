# Investigação — contrato visual transversal de ações/botões (V1/V2)

Data: 2026-09-09. Frente: FRONT-003 (H-013), subonda UI-02B/C/D.
Baseline inicial: `78e4719` (HEAD = origin/main, working tree limpa).

Tratamento pedido pelo dono: auditoria transversal de ações, não correção pontual
da tela de Fornecedores. Regra de decisão: primeiro o legado; inconsistências
acidentais não são fidelidade; temas continuam visualmente distintos; o comum é o
contrato de papel/estado (reconhecível, hover, foco, cursor, semântica).

## 1. Causa raiz confirmada no código atual

- `resources/views/temas/v1/parceiros/index.blade.php`: `Novo` e `Editar` são `<a>`
  crus, `Remover` é `<button>` cru.
- `resources/views/temas/v2/parceiros/index.blade.php`: `Novo` usa
  `btn formSubmit`, `Editar` é `<a>` cru e `Remover` usa `btn btn-xs` — três
  linguagens na mesma tabela.
- `resources/sass/temas/_v1-base.scss`: `button` define borda/fundo/cor/altura,
  sem `cursor`; botões V1 herdavam `cursor: default` do browser.
- `resources/views/rma/_acoes_de_transicao.blade.php`: botões crus (comentário
  histórico "sem fidelidade visual") — candidato direto do contrato.
- `resources/views/rma/credito/_conteudo.blade.php`: botão cru recém-integrado ao
  shell.
- Views standalone de relatórios (RPEC/RMPE), quando entrarem no shell (UI-03), têm
  botões `Filtrar` crus.

## 2. Evidência do legado — TEMA V1 (14.6.1)

Fonte: `backup-15.9.7/.../14.6.1/`.

| Superfície | Fonte | Padrão real |
|---|---|---|
| Lista de fornecedores | `menujs-right/fornecedores.php` | Sem ação textual separada: cada célula é `<a href="index.php?page=fornecedor&id=…">` e a linha inteira navega; zebra `Tabelinha-TR1/2`, hover `#904141`. |
| Lista de fabricantes | `menujs-right/fabricantes.php` | Mesmo padrão (idem clientes/assistências, confirmado em `page/{cliente,assistencia_tecnica}.php`). |
| Detalhe/edição de parceiro | `page/{fornecedor,fabricante,cliente,assistencia_tecnica}.php` | Formulário inline; botão `disabled` com texto `USE A 15.8.1 P/ SALVAR` (`border:0;width:236px;height:34px`) — V1 delegava a manutenção ao V2. |
| CSS de botão global | `pattern/14.6.1.css` | `button { border:…; background:#662D37; color:#FFF; height:25px; }` — sem `cursor:pointer`. |
| Ações da sessão | `pattern/14.6.1.css` `.lisessao`, `.formButtonMENU`, `.formButtonSIGNOUT` | Hover `gold`/`#9B3949`; só itens navegáveis declarados como links tinham cursor natural. |

Conclusão V1: não existe contrato de "botão Novo/Editar/Remover em tabela" no
legado. Regra 3/4 da decisão visual: o V3 cria o menor contrato usando a gramática
existente (cantos retos, superfície escura, texto branco, hover na família vermelha
`#9B3949/#904141/#CD5C5C`, sem framework).

## 3. Evidência do legado — TEMA V2 (15.8.1)

Fonte: `backup-15.9.7/.../15.8.1/`.

| Superfície | Fonte | Padrão real |
|---|---|---|
| Menu de parceiro | `inc/menu_{fornecedores,fabricantes,clientes,assistencia_tecnicas}.php` | `Novo` é `<a>` dentro de `<ol class="breadcrumb">` que expande o form inline via `onclick` (`novo_fornecedor()` etc.); link branco, hover `#DDD`. |
| Lista de fornecedores | `subp/listar_fornecedores.php` | Coluna `ACAO` com dois ícones `<a>`: `apagar.png` ("Apagar") e `ver.png` ("Ver"); sem texto cru. |
| Formulário novo | `inc/novo_fornecedor.php` | Grid Bootstrap; submit `class="btn btn-default formButtonCadastrar2"` com rótulo **Cadastrar**. |
| Detalhe/edição | `subp/ver_fornecedor.php` | Formulário editável; submit `class="btn btn-default formButtonCadastrar2"` com rótulo **Salvar**. |
| CSS de ações V2 | `pattern/15.8.1.css` | `.formSubmit` `#224A5D` (88×30, radius 0), `.btn-default` escuro translúcido com hover `#224A5D`, `.buttonSalvar` `#224A5D`, `.buttonSearch` `#333`/hover `#185A78`. |
| Ciclo no detalhe de RMA | `page/rma.php` | Ação em `<select>` + botão `OK` (`btn btn-default formSubmit` / `buttonSalvar`) — ações não eram botões coloridos por semântica. |

Conclusão V2: existe paleta pronta (`#224A5D` primária, hover `#185A78`,
`#333`/`#E1DEAC` secundária, família vermelha para alerta/destrutiva), mas nenhum
contrato comum por papel na lista de parceiros. O V3 herda a paleta e cria os
papéis.

## 4. Inventário de superfícies/rotas reais (V3 autenticado)

Rotas reais mapeadas em `routes/web.php` + `routes/tema-{v1,v2}.php`; views
genéricas órfãs (`resources/views/{parceiros,rma}/*` standalone e
`identidade/{usuarios,perfil/senha}/*`) ficam para UI-07/FRONT-006 — não entram
como superfície ativa nesta matriz.

| # | Superfície | Rotas | Ações encontradas |
|---|---:|---|---|
| 1 | Parceiros V1/V2 (4 tipos) | `parceiros.{fornecedores,fabricantes,clientes,assistencias-tecnicas}` | Novo, Editar, Remover |
| 2 | Form parceiro V1/V2 | `…create`, `…edit` | Salvar; Voltar (V1) |
| 3 | RMA index V1/V2 + tabs | `rmas.index`, listagens | Ver, Editar; FILTRAR/Enviar pesquisa; Abrir novo RMA (V2) |
| 4 | Detalhe RMA V1/V2 | `rmas.show` | Editar + ciclo de vida |
| 5 | Form RMA V1/V2 | `rmas.create/edit` | Salvar |
| 6 | `_acoes_de_transicao` | posts de ciclo | Receber, Encaminhar, Concluir, Arquivar, Reverter para Entrada, Salvar solução |
| 7 | Crédito V1/V2 | `rmas.credito.index`, `marcar` | Marcar crédito disponível |
| 8 | Controle V1 | `rmas.controle.index` | ADICIONAR ×3, ARQUIVAR, SALVAR senha |
| 9 | Usuários V1/V2 | `identidade.usuarios.*` | Salvar papel, Resetar senha |
| 10 | Perfil V1/V2 | `identidade.perfil.*` | Alternar tema, Trocar senha, Salvar anotação |
| 11 | Relatórios standalone | `rmas.relatorios.*` | Filtrar (RPEC/RMPE) — UI-03 |
| 12 | Login gateway | `login` | Iniciar (fora dos temas — intencional) |
| 13 | Layouts | navegação | Menu/signout/alternância — fora do contrato de ações (navegação), mas botões devem ter cursor/foco |

## 5. Matriz de ações — decisão e tratamento

Lenda: HTML = elemento atual; V1/V2 = classe atual; L-V1/L-V2 = referência
histórica; Papel = taxonomia de UX; Tratamento = ação nesta rodada.

| Tela | Ação | HTML | V1 atual | V2 atual | L-V1 | L-V2 | Semântica | Papel | Problema | Tratamento |
|---|---|---|---|---|---|---|---|---|---|---|
| Parceiros | Novo | `<a href>` | cru | `btn formSubmit` | linha navegável | link breadcrumb | GET/create | PRIMARY | não parece ação no V1 | `.acao .acao--primaria` |
| Parceiros | Editar | `<a href>` | cru | cru | detalhe inline | ícone Ver → form Salvar | GET/edit | SECONDARY/compacta | texto cru na coluna | `.acao .acao--secundaria .acao--compacta` |
| Parceiros | Remover | `<button>` em form DELETE | cru | `btn btn-xs` | — (V2 ícone Apagar) | ícone Apagar | POST/DELETE | DANGER/compacta | sem cursor; mistura | `.acao .acao--perigo .acao--compacta` |
| Form parceiro | Salvar | `<button>` | `buttonSave` | `btn formSubmit` | botão disabled delegava | `btn-default formButtonCadastrar2` | POST/store | PRIMARY | classes de componente ok | cursor global; papel documentado (CONFORME) |
| RMA index V1/V2 | Ver | `<a href>` | cru | cru (`_tabela`) | linha navegável | ícone Ver | GET/show | SECONDARY/compacta | texto cru | `.acao .acao--secundaria .acao--compacta` |
| RMA index | Editar | `<a href>` | cru | cru | detalhe inline | detalhe editável | GET/edit | SECONDARY/compacta | texto cru | `.acao .acao--secundaria .acao--compacta` |
| RMA show | Editar | `<a href>` | cru | `btn formSubmit` | detalhe inline | detalhe editável | GET/edit | PRIMARY (superfície) | V1 cru | `.acao .acao--primaria` |
| Ciclo | Receber | `<button>` | cru | cru | select acao + OK | select acao + OK | POST | OPERACIONAL | sem contrato | `.acao .acao--operacional` |
| Ciclo | Encaminhar | `<button>` | cru | cru | select acao + OK | select acao + OK | POST | OPERACIONAL | sem contrato | `.acao .acao--operacional` |
| Ciclo | Concluir | `<button>` | cru | cru | select acao + OK | select acao + OK | POST | OPERACIONAL | sem contrato | `.acao .acao--operacional` |
| Ciclo | Arquivar | `<button>` | cru | cru | painel Controle | — | POST | OPERACIONAL (reversível) | sem contrato | `.acao .acao--operacional` |
| Ciclo | Reverter para Entrada | `<button>` | cru | cru | painel Controle | — | POST | OPERACIONAL (reversível) | sem contrato | `.acao .acao--operacional` |
| Ciclo | Salvar solução | `<button>` | cru | cru | select solução no form | select solução no form | POST | PRIMARY | sem contrato | `.acao .acao--primaria` |
| Crédito | Marcar crédito disponível | `<button>` | cru | cru | painel Créditos | painel Créditos | POST | PRIMARY | cru no shell novo | `.acao .acao--primaria` |
| Controle V1 | ADICIONAR/ARQUIVAR/SALVAR | `<button>` | `formButtonEnviarPanel` | — | mesma classe real | — | POST | COMPACT/componente histórico | fidelidade específica | cursor + foco; classe histórica preservada (UI-05) |
| Usuários | Salvar papel / Resetar | `<button>` | `formButtonEnviarPanel` | `btn formSubmit` | — | forms próprios | PUT/POST | SECONDARY/DANGER operacional | tema distinto | cursor + foco; papel documentado |
| Perfil | Alternar tema/Trocar/Salvar | `<button>` | `buttonSave`/cru | `btn formSubmit` | — | forms próprios | POST/PUT | SECONDARY/PRIMARY | V1 Alternar cru | `.acao .acao--secundaria` em Alternar tema; demais CONFORME |
| RPEC/RMPE | Filtrar | `<button>` | — | — | — | relatório filtro | GET | SECONDARY | cru standalone | aplicar contrato quando entrar no shell (UI-03) |

## 6. Contrato mínimo por tema

HTML carrega **a mesma semântica de papel** (classes comuns de significado), o CSS
de cada tema entrega a aparência própria:

| Papel | Classe semântica no HTML | Comportamento comum |
|---|---|---|
| Ação base | `.acao` | inline-block, alvo mínimo de ~25px, cantos do tema, texto não sublinhado, cursor pointer quando habilitada |
| Primária | `.acao--primaria` | ação principal da superfície (Novo, Salvar, Salvar solução) |
| Secundária | `.acao--secundaria` | ação de navegação/inspeção (Editar, Ver, Voltar) |
| Perigo | `.acao--perigo` | mutação destrutiva (Remover/Excluir); não cria modal (pendência UX-002) |
| Operacional | `.acao--operacional` | transições de ciclo (Receber, Encaminhar, Concluir, Arquivar, Reverter) |
| Compacta | `.acao--compacta` | variante de tabela; geometria reduzida e alinhada na coluna de ações |

Estados obrigatórios no CSS de cada tema: `:hover`, `:focus-visible` e
`button:not(:disabled)` com `cursor:pointer`; `:disabled` sem pointer e com
opacidade coerente. Links `<a>` mantêm semântica GET; mutações continuam `<button>`
em `<form>` com CSRF/Gates/rotas — nenhum JS de navegação novo.

## 7. Implementação V1 (paleta legada 14.6.1)

- Primária: superfície `#662D37` (cor real de `button` do legado), hover
  `#9B3949`, foco outline visível.
- Secundária: superfície neutra escura `rgba(0,0,0,0.2)`, hover na família
  `#9B3949`/texto branco.
- Perigo: família vermelha escura do tema — base `#904141`, hover/focus
  `#CD5C5C` (claro só como estado de interação, não como fundo permanente), texto
  branco. Calibração pós-validação do dono: `#CD5C5C` como danger-base dominava
  listagens densas; ficou apenas como hover/estado.
- Operacional: variante escura com letter-spacing e caixa alta, hover `#904141`
  (cor de atenção do legado).
- Compacta: altura ~22–25px, padding lateral pequeno, cantos retos.

## 8. Implementação V2 (paleta legada 15.8.1)

- Primária: `#224A5D` (azul petróleo do `.formSubmit`/`.btn-default:hover`), hover
  `#185A78`, branco.
- Secundária: `#333`/texto `#E1DEAC` (gramática do `.buttonSearch`), hover
  `#185A78`/branco.
- Perigo: `#904141` (vermelho de atenção do tema), hover `#F67D7D`.
- Operacional: fundo translúcido `.btn-default` com borda sutil, hover `#224A5D`.
- Compacta: menor altura (~25px), radius 0, padding reduzido.

## 9. Ordem de execução

- UI-02B: esta investigação + contrato + OpenSpec (commit DOC).
- UI-02C: Parceiros V1/V2 (ciclo 1) → RMA listagens/detalhe (ciclo 2) → ciclo de
  vida (ciclo 3) → formulários/crédito/identidade (ciclo 4), cada um com commit.
- UI-02D: varredura residual automatizada, prova de consistência e Playwright
  dirigido (inclui `computedStyle.cursor`, hover, TAB e semântica).
- UI-03 em diante já nasce com o contrato.

## 10. Fora de escopo desta frente

- UX-002 (confirmação de remoção de parceiro) — permanece separada.
- UX-003/UX-004, responsividade global do V1, modal genérico, biblioteca/framework.
- Views genéricas órfãs — UI-07/FRONT-006.

## 11. Execução registrada (UI-02B/C/D)

- `694732d` — `#DOC-RMA`: auditoria/contrato (este documento + OpenSpec + plano).
- `624e548` — `#FRONT-RMA`: Parceiros V1/V2 (Novo primary; Editar secondary compact;
  Remover danger compact; cursor/hover/focus).
- `1c1a1e6` — `#FRONT-RMA`: RMA listagens/detalhe (Ver/Editar compactos; Editar do
  detalhe primário; V2 mantém ícone "Ver" no índice por fidelidade do legado).
- `5ce8654` — `#FRONT-RMA`: ciclo de vida (`rma._acoes_de_transicao`) com papéis
  semânticos, sem tocar rotas/CSRF/Gates.
- `42a4235` — `#FRONT-RMA`: formulários/crédito/identidade (Alternar tema V1
  secundária, Voltar V1 secundária, crédito primária, Abrir novo RMA V2 primária,
  foco global).
- `7a17120` — `#QA-RMA`: Playwright dirigido (`ContratoVisualAcoes.spec.ts`).
- Calibração desta revisão — Tema V1 danger-base `#CD5C5C` → `#904141` (hover
  `#CD5C5C`); Tema V2 permanece `#904141`/`#F67D7D`.

Prova: PHPUnit completo 477 testes / 1242 assertions; Playwright 2 testes (V1+V2:
Parceiros com computed cursor/hover/TAB; detalhe RMA + crédito); Vite build verde.

Resíduo classificado (sem contrato proposital): views genéricas órfãs (UI-07),
relatórios standalone RPEC/RMPE (UI-03, nascerão com contrato), componentes
históricos de geometria própria (`buttonSave`, `formSubmit`, `formButtonEnviarPanel`,
`JSformLocalizarButton`) — agora com cursor/foco globais por tema.
