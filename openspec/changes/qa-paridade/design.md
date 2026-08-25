# Design — QA de paridade

Critério completo já escrito em `INV-RMA-05` §15. Resumo dos 3 eixos + o gate final.

## Eixo 1 — Funcional

Por `LEG-RMA-NNN`: linha em `PARIDADE` **e** (teste automatizado que falharia se a V3
divergisse do legado sem justificativa) **ou** (passo manual documentado em
`docs/qa/roteiro-paridade-funcional.md`, com resultado esperado × observado).
`NÃO RECONSTRUIR`/`RETOMAR IDEIA` contam como fechados com só a justificativa já
registrada na matriz, sem exigir teste.

## Eixo 2 — Visual

3 breakpoints de QA (390/768/1440) mapeados para os breakpoints reais de cada tema
(tabela completa em `INV-RMA-05` §15 — TEMA V1 é fixo/984px sem `@media`, TEMA V2 tem
6 breakpoints próprios via `css/media.php`). Critério: screenshot da V3 lado a lado com
LEGACY-RUNTIME (`:8094`) sem diferença estrutural (disposição de elementos, paleta —
diferença de rasterização de fonte não conta). Divergência real vira pendência
rastreável ou correção aprovada pelo usuário, nunca fica "diferente e não documentada".

## Eixo 3 — Dados

O `RelatorioDeReconciliacao` da Fase 9 **é** a evidência — não uma verificação isolada.
QA confirma: contagem origem×destino bate (ou a diferença é exatamente anomalias +
conversões assistidas + registros intencionalmente não migrados); nenhuma anomalia sem
linha correspondente rastreável até a chave de origem.

## Gate de conclusão do projeto (Trilha A → habilita Trilha B)

1. `paridade-v2-v3.md` 100% em `PARIDADE` (excluindo `NÃO RECONSTRUIR`/`RETOMAR IDEIA`).
2. `sail test` verde na suíte inteira (full-regression).
3. Eixo 2 fechado nos 3 breakpoints × 2 temas × telas principais, com evidência em
   `tests/Browser/` + screenshots arquivados.
4. Relatório de reconciliação da Fase 9 sem divergência não explicada.
5. Todas as pendências reais registradas ao longo do projeto (`LEG-RMA-002`, `EVO-AUD-001`,
   fonte Open Sans, assimetria pós-login, as 4 de `INV-RMA-06`) decididas pelo usuário
   ou explicitamente adiadas para o backlog evolutivo — nenhuma esquecida, mas nenhuma
   precisa virar código antes do gate passar.

## Arquivos

- `docs/qa/roteiro-paridade-funcional.md`
- `docs/qa/relatorio-paridade-final.md`
- `tests/Browser/ParidadeVisualTest.php`
