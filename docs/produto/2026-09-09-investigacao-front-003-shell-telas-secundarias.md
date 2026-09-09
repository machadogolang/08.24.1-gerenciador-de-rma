# Investigação FRONT-003 - telas secundárias sem shell

Data: 2026-09-09. Causa raiz: controllers retornam `view('rma.*')`/views standalone
com HTML próprio em vez de `view_do_tema()`; Fase 8 deixou essas superfícies como
"funcional sem estilo" (limite de escopo histórico). Decisão do dono: tratar como
lacuna ativa de produto; não reabrir Fase 8.

## Inventário (rota / controller / view atual / tema / estado)

| Rota | Controller | View | Estado |
|---|---|---|---|
| `/rmas-credito` | `CreditoController` | `rma/credito/index` | **Corrigida** (`view_do_tema`, V1/V2 shell; commit df0d02e) |
| `/rmas-relatorios/rcd` | `RelatorioController` | `rma/relatorios/rcd` | Pendente - standalone |
| `/rmas-relatorios/rpec` | `RelatorioController` | `rma/relatorios/rpec` | Pendente - standalone |
| `/rmas-relatorios/rmpe` | `RelatorioController` | `rma/relatorios/rmpe` | Pendente - standalone |
| `/rmas-alertas` | `PainelDeAlertasController` | `rma/_painel_de_alertas` | Pendente - standalone |
| `/rmas-historico` | `HistoricoDeModificacaoController` | `rma/historico/index` | Pendente - standalone |
| `/historico-de-acesso` | `HistoricoDeAcessoController` | `identidade/historico-de-acesso/index` | Pendente - standalone |
| `/rmas-logistica/frete-porto-alegre` | `LogisticaController` | `rma/logistica/frete-porto-alegre` | Pendente - standalone |
| `/rmas/{rma}/boletins-relacionados` | `LogisticaController` | `rma/logistica/boletins-relacionados` | Pendente - standalone |
| `/rmas-controle` | `ControlePainelController` | `temas/v1/rma/controle` | Shell V1 OK; alinhamento representante a revisar |
| `/login` | `SessaoController` | `identidade/login` | Intencional (gateway) - manter |

## Impactos

- V1/V2: páginas sem menu/sidebar/footer; tabelas e inputs default do browser.
- Relatórios: Ctrl+P precisa manter apenas conteúdo útil (sem menu/sidebar/rodapé).
- Controle V1: bloco "ADICIONAR REPRESENTANTE" com três forms sem grade/alinhamento
  robusto; não alterar responsividade global do V1 (layout fixo é fidelidade).

## Ordem

1. UI-01/02 - Crédito (concluído).
2. UI-03 - RCD/RPEC/RMPE com shell + impressão limpa.
3. UI-04 - alertas/históricos/logística.
4. UI-05 - Controle V1 (alinhamento local).
5. UI-06 - auditoria identificador RMA (id × numero_legado × numero_da_empresa).
6. UI-07 - remover views genéricas órfãs após migração (FRONT-006).
7. UI-08 - regressão browser/Playwright.
