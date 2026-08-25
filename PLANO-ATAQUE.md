# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-25. Fonte granular:
`docs/produto/checklist-master-v3.md`.
Handoff para nova sessão: `docs/produto/handoff-sessao-2026-08-25.md`.

## AGORA

Executar primeiro a correção P0 descoberta pela auditoria da nova frente, sem depender
de decisão de produto nem de mutação do banco histórico:

1. [x] `ARQ-001`: provar por regressão e corrigir a perda de estado do agregado em
   edição/transições (`Rma::comAlteracoes()` + 7 casos de uso migrados; ver
   `docs/produto/checklist-master-v3.md` H-001);
2. [x] `ARQ-003`: impedir promoção indevida e operações do Supervisor sobre
   SuperAdministrador (`Papel::podeOperarSobrePapel()`; ver checklist H-003);
3. [ ] renovar a suíte completa e documentar o checkpoint;
4. [ ] retomar `F10-FUN-07` pelos smokes somente leitura;
5. [ ] concluir `ARQ-002` antes de `F10-DAD-04`, tornando dry-run e reconciliação
   confiáveis.

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

## CHECKPOINT INCORPORADO — PARIDADE VISUAL TEMA V1

Concluído em 2026-08-25 sem substituir o lote funcional em AGORA: comparação runtime
14.6.1 × V3 em dez superfícies/1440 px, correção estrutural de Blade/CSS, Fira Mono e
logo locais, teste permanente de assets/geometria e matriz em
`docs/produto/paridade-visual-tema-v1.md`. O gate visual F10 permanece aberto para TEMA
V2, demais superfícies e breakpoints previstos no checklist mestre.

O lote funcional foi retomado: itens 1, 2, 4 e 5 de AGORA estão concluídos. A próxima
sessão começa no item 3 (`F10-FUN-07`), pelos smokes somente leitura.

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
