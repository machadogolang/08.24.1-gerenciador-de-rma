# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-25. Fonte granular:
`docs/produto/checklist-master-v3.md`.
Handoff para nova sessão: `docs/produto/handoff-sessao-2026-08-25.md`.

## AGORA

Executar o primeiro lote seguro da F10, sem depender de decisão de produto nem de
mutação do banco histórico:

1. [x] criar `docs/qa/roteiro-paridade-funcional.md`;
2. [x] mapear os 48 `LEG-RMA-*` para teste automatizado, passo manual ou justificativa;
3. [ ] executar os passos manuais possíveis nos ambientes locais `:8094` e `:8095`;
4. [x] corrigir o OpenSpec da F10 para a implementação real de Playwright (`.spec.ts`);
5. [x] rodar a suíte completa e atualizar as evidências.

Saída do lote: nenhuma linha funcional sem método de prova, suíte verde, checklist e
OpenSpec coerentes e commit local próprio.

## CHECKPOINT INCORPORADO — PARIDADE VISUAL TEMA V1

Concluído em 2026-08-25 sem substituir o lote funcional em AGORA: comparação runtime
14.6.1 × V3 em dez superfícies/1440 px, correção estrutural de Blade/CSS, Fira Mono e
logo locais, teste permanente de assets/geometria e matriz em
`docs/produto/paridade-visual-tema-v1.md`. O gate visual F10 permanece aberto para TEMA
V2, demais superfícies e breakpoints previstos no checklist mestre.

O lote funcional foi retomado: itens 1, 2, 4 e 5 de AGORA estão concluídos. A próxima
sessão começa no item 3 (`F10-FUN-07`), pelos smokes somente leitura.

## DEPOIS

1. **F10 visual:** fechar 2 temas × 3 breakpoints × telas principais, reutilizando
   evidências válidas da F8 e capturando somente o que falta.
2. **F10 dados:** conectar a origem histórica de forma controlada, executar `--dry-run`,
   revisar anomalias e importar em base V3 descartável.
3. **F10 fechamento:** decidir ou adiar questões residuais, produzir
   `docs/qa/relatorio-paridade-final.md` e avaliar o gate da Trilha A.
4. **Trilha B:** somente após o gate, seguir a ordem do checklist mestre.

## BLOQUEADOS / DECISÃO EXTERNA

- `LEG-RMA-002`: autocadastro com convite ou criação só por admin.
- Migração histórica: requer conexão controlada e alvo descartável; nunca usar a base
  corrente por inferência.
- Visibilidade do repositório V3: decisão operacional do usuário.

## INVESTIGAÇÕES RESIDUAIS

- RN-12 no TEMA V1: fechar ausência/presença com evidência dirigida.
- Lightbox2 e skin AdminLTE: uso real ou resíduo de template.
- Datas inválidas e `status='retornou'`: decidir apenas se surgirem no dado real.

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
