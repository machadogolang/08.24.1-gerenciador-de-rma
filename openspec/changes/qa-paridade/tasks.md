# Tasks — QA de paridade

**Pré-requisito:** Fases 1-9 concluídas (esta fase verifica, não implementa).

- [x] `docs/qa/roteiro-paridade-funcional.md` (passos manuais para itens sem teste
      automatizável)
- [ ] `tests/Browser/*.spec.ts` (Playwright, 3 breakpoints × 2 temas ×
      telas principais, screenshot diff contra `:8094`)
- [ ] Rodar full-regression `sail test`
- [ ] Confirmar `paridade-v2-v3.md` 100% `PARIDADE` (exceto `NÃO RECONSTRUIR`/
      `RETOMAR IDEIA`)
- [ ] Revisar relatório de reconciliação da Fase 9 — zero divergência não explicada
- [ ] Listar todas as pendências reais abertas ao longo do projeto e confirmar que cada
      uma tem decisão registrada (implementar / `EVO-*` / não fazer)
- [ ] `docs/qa/relatorio-paridade-final.md` (consolida os 3 eixos + checklist de gate)
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 10 concluída, projeto em
      paridade — Trilha B liberada)
- [ ] Commit `#F10 - QA de paridade (gate de conclusao da Trilha A)`
