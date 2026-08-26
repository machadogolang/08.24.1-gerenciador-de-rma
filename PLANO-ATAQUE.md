# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-26. Fonte granular:
`docs/produto/checklist-master-v3.md`.
Handoff para nova sessão: `docs/produto/handoff-sessao-2026-08-26.md` (substitui o
handoff de 2026-08-25, mantido só como histórico).

## AGORA

**Interrupção vigente — paridade visual do Tema V1, fase 2.** Por determinação de
2026-08-25, `F10-FUN-07` fica temporariamente pausado.

Fase 1 (`docs/produto/plano-execucao-paridade-estrutural-v1.md`) **fechada**:

1. [x] CP1 — cascata/fontes, captura normalizada, inspeção visual e regressão CDP;
2. [x] CP2 — primitivas reais de tabela;
3. [x] CP3A — composição completa de Concluído;
4. [x] CP3B/CP3C/CP3D — Entrada, Encaminhado e Aguardando crédito;
5. [x] CP4 — resumo inferior de Concluídos sem SQL no Blade;
6. [x] CP5 — propagação, regressões e prova final (1440×1000; 1562×1400/1700×1000
   ficaram para a fase 2, ver CP15-04 abaixo).

Percepção de que o Legacy "parece mais largo" mesmo a 100% de zoom: investigada e
descartada como bug de CSS/DOM (`#BASE`/`#TOPO` idênticos, `devicePixelRatio`/`zoom`
neutros nos dois lados) — ver seção 1 de
`docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-encontrados.md`.

**Fase 2** (`docs/produto/plano-execucao-paridade-visual-v1-fase2.md`, **em
execução, CP6 fechado**) — restante da superfície do Tema V1 que a fase 1 não cobria.
Executar em ordem, um checkpoint por vez, commit local a cada checkpoint fechado:

1. [x] CP6 — Página Inicial sem conteúdo artificial (removidos "RMAs"/"Novo RMA");
2. [x] CP7 — Localizar como painel inline histórico (maior item da fase);
3. [x] CP8 — painel Novo: toggle de estoque, campo de data, box-sizing (CP8-04,
   fabricante como input/datalist, deixado em aberto — reverteria decisão de fase
   anterior sobre `EncontrarOuCriarFabricante`);
4. [x] CP9 — Quadro de Anotações (rows=20, título, remover botão salvar,
   autosave via fetch);
5. [x] CP10 — sidebar de contadores (box model, links reais pras 4 listagens +
   filtro por solução);
6. [x] CP11 — separador antes do Centro de Avisos (`separador2.png`);
7. [~] CP12 — Centro de Avisos: ordem/títulos históricos corrigidos; CP12-05 foi
   reaberto pelo print do usuário. Protocolo aberto não encaminhado fechado e
   comparado em CP12-05A/CMP-V1-2-011; outras 9 tabelas permanecem CP12-05B–J;
8. [x] CP13 — fixture de QA com comprimento de dado realista (`os`/`descricao`
   encurtados; 1 registro com `solucao=PendenteCredito`, Aguardando Crédito deixa
   de ficar sempre vazia);
9. [x] CP14 — investigação da máquina de estados `$TR1` — **achado real, aguardando
   decisão do usuário**: `Rma::classeDeAlerta()` nunca devolve `ClasseDeAlerta::
   Urgente` (prioridade alta / 30-dias-cliente-fora-do-prazo saem com a cor errada,
   `TrInconformidade` em vez de `TrUrgente`); mesmo achado já registrado como
   `[INVESTIGAR]` no CP23 do TEMA V2, agora com evidência completa. Ver
   `CMP-V1-2-009`;
10. [ ] CP15 — gate final da fase 2 (três viewports, prova final, checklist).

Próximo checkpoint após o commit corrente: **CP12-05B**, tabela “Produtos com maior
prioridade sem encaminhamento”. Depois da Home: auditoria integral menu a menu/link a
link, com commit local e próximo item explícito por tela/grupo fechado, em
`docs/produto/plano-execucao-auditoria-navegacional-visual-v1.md`.

Gate adicional: antes de concluir cada item, abrir os prints Legacy/V3; depois da
alteração, gerar e abrir o novo par normalizado. Toda comparação entra no Diário do MD
operacional de cada fase. Pareceres:
`docs/pareceres/parecer-paridade-estrutural-v1-falhas-layout.md` (fase 1);
`docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-encontrados.md` (fase 2).

**Frente paralela — paridade visual do Tema V2**
(`docs/produto/plano-execucao-paridade-v2.md`, **encerrada, CP16-CP25 aprovados**).
Matriz: `LEGADO 15.8.1 <-> V3 /v2/rma` (nunca `/rmas`, nunca `14.6.1`). Independente da
fase 2 do Tema V1 acima — V1 não foi reaberto nesta frente.

1. [x] CP16 — shell global (wrapper 1190px / `.container` 990px / sidebar 195px);
2. [x] CP17 — header e navegação reais (9 itens históricos, dropdown Menu);
3. [x] CP18 — cascata e fontes do V2;
4. [x] CP19 — menu lateral direito (`rightmenu.php`, 14 seções);
5. [x] CP20 — Home e Pesquisar;
6. [x] CP21 — separador antes do Centro de Avisos;
7. [x] CP22 — Centro de Avisos;
8. [x] CP23 — tabelas das 5 abas (Pesquisar/Entrada/Recebido/Encaminhado/Concluído);
9. [x] CP24 — footer;
10. [x] CP25 — gate final (4 viewports, prova final, checklist) — ver `CMP-V2-008`.

Pendências explícitas fora desta frente (não bloqueiam o fechamento): zebra fina de
Entrada/Recebido/Encaminhado (`[INVESTIGAR]`), gap "Anotacoes" sem página própria
(`[GAP]`), redesenho por-grupo do Centro de Avisos (cada `subp/listar_*.php` tem seu
próprio layout de colunas, hoje servido por uma partial genérica).

### Lote anterior pausado, não descartado

Executar primeiro a correção P0 descoberta pela auditoria da nova frente, sem depender
de decisão de produto nem de mutação do banco histórico:

1. [x] `ARQ-001`: provar por regressão e corrigir a perda de estado do agregado em
   edição/transições (`Rma::comAlteracoes()` + 7 casos de uso migrados; ver
   `docs/produto/checklist-master-v3.md` H-001);
2. [x] `ARQ-003`: impedir promoção indevida e operações do Supervisor sobre
   SuperAdministrador (`Papel::podeOperarSobrePapel()`; ver checklist H-003);
3. [x] renovar a suíte completa e documentar o checkpoint (331 testes/696 assertions,
   sem falha; ver checklist H-004);
4. [ ] retomar `F10-FUN-07` pelos smokes somente leitura;
5. [x] concluir `ARQ-002` antes de `F10-DAD-04`, tornando dry-run e reconciliação
   confiáveis (trait `ExecutaComRollbackEmDryRun`; ver checklist H-002).

O lote anterior da F10 permanece incorporado e não foi descartado:

1. [x] criar `docs/qa/roteiro-paridade-funcional.md`;
2. [x] mapear os 48 `LEG-RMA-*` para teste automatizado, passo manual ou justificativa;
3. [ ] executar os passos manuais possíveis nos ambientes locais `:8094` e `:8095`;
4. [x] corrigir o OpenSpec da F10 para a implementação real de Playwright (`.spec.ts`);
5. [x] rodar a suíte completa e atualizar as evidências.

Saída: P0 sem regressão, nenhuma linha funcional sem método de prova, suíte verde,
checklist/OpenSpec coerentes e commits locais pequenos por checkpoint.

## CHECKPOINT INCORPORADO — ARQUITETURA, FRONT-END E PARIDADE DE TEMAS

Investigação consolidada em `INV-RMA-10` e matriz em
`docs/produto/matriz-paridade-temas-v1-v2-v3.md`. A nova frente não libera código do
Tema 3 e não substitui F10. Ela corrige a ordem quando um achado compromete integridade,
segurança ou a validade do próprio gate de QA.

## CHECKPOINT REABERTO — PARIDADE VISUAL TEMA V1

O checkpoint que havia sido concluído em 2026-08-25 foi reaberto por evidência visual
estrutural em `INV-RMA-BUG-LAYOUT-falhas.md`. A comparação anterior cobriu
14.6.1 × V3 em dez superfícies/1440 px, correção estrutural de Blade/CSS, Fira Mono e
logo locais, teste permanente de assets/geometria e matriz em
`docs/produto/paridade-visual-tema-v1.md`, mas não detectou cascata invertida, H1/ícones,
famílias de linha, colunas e resumo de Concluído. O gate visual permanece aberto.

O lote funcional volta a `F10-FUN-07` somente depois de CP1–CP15 (fase 1 + fase 2).

## DEPOIS

1. **P0 dados:** fechar `ARQ-002` antes do dry-run histórico.
2. **F10 visual:** fechar 2 temas × 3 breakpoints × telas principais, reutilizando
   evidências válidas da F8 e capturando somente o que falta.
3. **F10 dados:** conectar a origem histórica de forma controlada, executar `--dry-run`,
   revisar anomalias e importar em base V3 descartável.
4. **F10 fechamento:** decidir ou adiar questões residuais, produzir
   `docs/qa/relatorio-paridade-final.md` e avaliar o gate da Trilha A.
5. **Trilha B/Tema 3:** somente após o gate, seguir a seção H do checklist e nunca
   expor Tema 3 antes da matriz integral.

## BLOQUEADOS / DECISÃO EXTERNA

- `LEG-RMA-002`: autocadastro com convite ou criação só por admin.
- Migração histórica: requer conexão controlada e alvo descartável; nunca usar a base
  corrente por inferência.
- Visibilidade do repositório V3: decisão operacional do usuário.

## INVESTIGAÇÕES RESIDUAIS

- RN-12 no TEMA V1: fechar ausência/presença com evidência dirigida.
- Lightbox2 e skin AdminLTE: uso real ou resíduo de template.
- Datas inválidas e `status='retornou'`: decidir apenas se surgirem no dado real.
- Campos históricos editáveis × somente leitura; criação/exclusão de usuários;
  mutações nas rotas prefixadas de QA.

## TRILHA B

- Fundação: `EVO-SAAS-001/002/003`.
- Experiência/capacidades: `EVO-UX-001`, `EVO-CONF-001`, `EVO-ARQ-001`,
  `EVO-DOM-001/002/003`.
- Operação/evolução: `EVO-AUT-001/002`, `EVO-REL-001/002`, `EVO-SEG-001`,
  `EVO-AUD-001`, `EVO-PERF-001`, `EVO-IA-001`.

## NÃO FAZER AINDA

- Não implementar item `EVO-*`.
- Não alterar código-fonte ou dumps históricos.
- Não importar dados sem origem, alvo e rollback explícitos.
- Não publicar nem alterar visibilidade remota sem autorização.
- Não declarar paridade apenas pela existência de código ou fixture.
