# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-25 (comparação viva V3×Legado completa — Fases 1-9
reconfirmadas em produção real via `curl`/`tinker` autenticado, `sail test` 308/308; ver
`docs/produto/comparacao-v3-legado-final.md`).

## AGORA

**Trilha A (reconstrução) praticamente fechada:** Fases 1-9 (Identidade → Migração)
implementadas, testadas e commitadas. `sail test` 308/308 verde, 593 assertions,
reconfirmado nesta sessão. Amostragem viva (rotas HTTP autenticadas + `tinker`) em
~15 funcionalidades espalhadas pelas 9 fases não encontrou nenhuma regressão real —
tudo que `paridade-v2-v3.md` marca `PARIDADE` respondeu e funcionou de verdade. **Só a
Fase 10 (QA de paridade) resta para fechar a Trilha A** — ainda não começou
(`openspec/changes/qa-paridade/tasks.md` 100% `[ ]`), é o único próximo passo real.
Dentro da Fase 10 mora explicitamente a pendência de rodar o migrador da Fase 9 contra o
banco real do Legacy (nunca rodou operacionalmente, só contra fixture — bloqueio de
rede documentado, não é falha do migrador).

**Trilha B aberta, 3 investigações concluídas, nenhuma implementação iniciada** (por
decisão deliberada — só depois da Fase 10 fechar a baseline de paridade):
`INV-RMA-07` (SaaS multiempresa), `INV-RMA-08` (tema V3 mobile-first), `INV-RMA-09`
(anexos de RMA + hub de configuração admin) — todas em `docs/arquitetura/`, backlog
correspondente em `docs/produto/backlog-evolutivo.md`.

Checklist granular por fase em `docs/produto/checklist-master-v3.md` continua sendo o
mapa operacional — este arquivo é só o resumo de fase/dependência/critério de saída.

## DEPOIS — o que falta em cada fase (detalhe granular, espelha `checklist-master-v3.md`)

### Fases 5-9 — todas CONCLUÍDAS, reconfirmadas nesta sessão (2026-08-25)

Alertas e regras (5), Créditos e relatórios (6), Auditoria (7), Apresentação/Temas V1-V2
(8) e Migração V2→V3 (9) — implementadas, testadas (`sail test` 308/308) e reconfirmadas
por amostragem viva (rotas HTTP autenticadas + `tinker`) nesta comparação. Nenhuma
regressão real encontrada. Detalhe completo de cada uma em `checklist-master-v3.md`
Parte 3 e `docs/produto/log-implementacao-v3.md`. Pendências reais que sobreviveram às 5
fases (nenhuma bloqueia a Fase 10, todas registradas para decisão do usuário):

- `EVO-AUD-001` (Fase 7) — log de modificação usa snapshot estruturado, não diff
  campo-a-campo; decisão do usuário ainda não tomada.
- Fase 9 — o migrador está codificado e testado (fixture determinística), mas **nunca
  rodou operacionalmente contra o banco real do Legacy** (bloqueio de rede: porta
  `3309` do Legacy só em `127.0.0.1` do host, não alcançável do container V3). Passa a
  ser trabalho explícito da Fase 10 ("paridade de dados").

### Fase 10 — QA de paridade (única fase restante da Trilha A)

OpenSpec: `openspec/changes/qa-paridade/`. Critério objetivo por eixo já fechado em
`INV-RMA-05` §15. **Ainda não começou** (`tasks.md` 100% `[ ]`). Falta:

- Paridade funcional por `LEG-RMA-NNN` — a matriz já está 44/48 `PARIDADE` (2 `NÃO
  RECONSTRUIR`, 1 `RETOMAR IDEIA`, 1 `PENDENTE` por decisão de produto:
  `LEG-RMA-002`), reconfirmada viva nesta sessão; falta o roteiro manual formal para os
  itens sem teste automatizável (`docs/qa/roteiro-paridade-funcional.md`, não escrito
  ainda)
- Paridade visual (screenshot V2×V3, Playwright, 390/768/1440, `tests/Browser/
  ParidadeVisualTest.php` não escrito ainda — Fase 8 já tem Playwright por tema, falta
  o diff direto contra `:8094`)
- Paridade de dados — depende de **rodar o migrador de verdade** contra o Legacy (ver
  bloqueio de rede acima); sem isso, "paridade de dados" fica só teórica (fixture)
- Revisar o relatório de reconciliação real (não o de fixture) — zero divergência não
  explicada
- Confirmar que toda pendência registrada ao longo do projeto tem decisão explícita
  (implementar / `EVO-*` / não fazer) — nesta comparação, as que seguem em aberto são:
  `LEG-RMA-002` (autocadastro), `EVO-AUD-001` (diff estruturado), bloqueio de rede da
  Fase 9, bug do `mariadb-admin` em `legacy-reset.sh` (Legacy, não corrigido por regra
  desta sessão), decisão de visibilidade do repositório V3 no GitHub
- `docs/qa/roteiro-paridade-funcional.md` e `docs/qa/relatorio-paridade-final.md`
- Atualizar `checklist-master-v3.md` (Fase 10 concluída, Trilha B liberada), commit
  `#F10 - QA de paridade (gate de conclusao da Trilha A)`

### Trilha B (SaaS, tema V3, anexos/config admin)

3 investigações concluídas (`INV-RMA-07`/`08`/`09`), implementação só depois do gate de
conclusão da Fase 10. Nada a fazer agora além do que `INV-RMA-07`/`08`/`09` e
`backlog-evolutivo.md` já registram.

### Itens residuais, não bloqueantes

- RN-12 (threshold R$75) — confirmar ausência/presença em TEMA V1 com leitura linha a
  linha completa, se necessário.
- `LEG-RMA-002` (autocadastro com convite) — decisão de produto pendente desde a Fase 1
  (opção A: segredo em `.env`; opção B: só admin cria usuário).

## DEPENDÊNCIAS

- INV-RMA-05/06/07 — todas escritas.
- MIG-V3 (migrador real, Fase 9) depende de Fases 4/5 em código (schema/enums finais
  precisam existir de verdade, não só especificados).
- Implementação da V3 depende de OpenSpec madura por funcionalidade — nunca "legado →
  interpretação rápida → código". Todas as 10 fases já têm OpenSpec madura.
- Trilha B (SaaS) depende da Trilha A completa e validada (paridade funcional/visual/de
  dados) — `INV-RMA-07` §13.

## CRITÉRIO DE SAÍDA da arqueologia — ATINGIDO

- [X] 48 funcionalidades com situação conhecida nos dois temas (1 residual de baixo
      risco: RN-12).
- [X] Árvore RMA V2/TEMA V1/TEMA V2 documentada sem contradição.
- [X] Inventário de segurança cobrindo os dois temas + camada compartilhada.
- [X] Identidade visual dos dois temas registrada (1ª passada, com evidência de
      runtime real — faltam só screenshots/telas internas, não bloqueante).
- [ ] Mapa legado→V3 por tabela/campo — pendente, é o próximo passo (`MIG-V3`/
      `INV-RMA-06`), não faz parte do critério de saída da arqueologia em si.

## Catálogo de OpenSpec (todas escritas — `openspec/changes/`)

Nascido do inventário funcional (48 itens `LEG-RMA-NNN`), agrupado por capacidade
coerente, não por botão nem num bloco só:

| Change proposta | Itens `LEG-RMA` cobertos |
|---|---|
| `autenticacao-usuarios` | 001–006, 043 |
| `rma-cadastro-e-localizacao` | 007–010 |
| `rma-ciclo-de-vida` | 011–017 (receber/encaminhar/concluir/arquivar/rollback) |
| `rma-alertas-e-prioridade` | 018–029 (as 10 regras + classificação visual + threshold) |
| `parceiros` (cliente/fabricante/fornecedor/assistência) | 030–035 |
| `rma-creditos-e-relatorios` | 036–039, 048 |
| `rma-logistica-e-historico` | 040–041, 044–047 |
| `temas-v1-v2` | apresentação — decisão de arquitetura de tema, não itens LEG-RMA específicos |
| `migracao-v2-v3` | MIG-01/02, não itens LEG-RMA |

Todas as 9 changes acima (mais `qa-paridade`, Fase 10) estão escritas. Cada uma só foi
escrita quando houve decisão de arquitetura madura o bastante (`INV-RMA-05`) para
descrever "como será na V3", não só "como era na V2".

## NÃO FAZER AINDA

- Não criar migration, model, controller, view Laravel da V3 sem OpenSpec correspondente.
- Não implementar nenhum item do `backlog-evolutivo.md`, incluindo `EVO-SAAS-001` —
  `INV-RMA-07` decide arquitetura, não implementa (nenhum `company_id`/`tenant_id`,
  nenhuma tabela `companies`, nenhum billing/plano/assinatura antes da baseline de
  paridade da Trilha A).
- Não fazer melhoria estrutural de banco antes da baseline V3 validada (ver regra de
  evolução do banco, na investigação arquivada §10).
- Não editar o código-fonte legado dentro de `08.24.4-legacy-gerenciador-de-rma/
  legacy-source/` — qualquer adaptação de ambiente é documentada como configuração de
  container, nunca como edição da fonte histórica.
- Não publicar o LEGACY-RUNTIME fora de `localhost`; não usar credencial real; não
  permitir envio de e-mail real (Mailpit obrigatório — já validado).
- Não `git push`, não PR, não merge remoto, não alterar os repositórios/backup
  históricos.

## Checkpoints ARQ-RMA / LEGACY-RUNTIME / MIG-V3

| Código | Título | Critério de fechamento | Status |
|---|---|---|---|
| ARQ-00 | Inventário do backup 15.9.7 | SHA-256, estrutura, tecnologias registrados | `[X]` |
| ARQ-01 | Arqueologia TEMA V2/15.8.1 | Regras/entidades/segurança documentadas | `[X]` |
| ARQ-02 | Arqueologia TEMA V1/14.6.1 | Idem | `[X]` |
| ARQ-03 | Arqueologia 14.10.2 | Confirmado protótipo sem banco, descartado como fonte principal | `[X]` |
| ARQ-04 | Camada compartilhada 15.9.7 | `metodo.php`/`conexao.php`/`trocarapp.php` documentados | `[X]` |
| ARQ-05 | Árvore RMA V2/TEMA V1/TEMA V2 | Matriz completa, ordem histórica confirmada pelo autor | `[X]` |
| ARQ-06a | Inventário funcional catalogado | 48 itens `LEG-RMA-NNN` + matriz de paridade | `[X]` |
| ARQ-06b | Resolver dúvidas de presença por tema | RN-13 a RN-18/RN-21 comparadas linha a linha (RN-12 residual) | `[X]` |
| ARQ-07a | Interface/identidade visual (1ª passada) | `inventario-visual-tema-{v1,v2}.md` escritos com evidência de runtime | `[X]` |
| ARQ-07b | Inventário de banco dedicado | `inventario-banco-rma-v2.md` escrito | `[X]` |
| ARQ-07c | Screenshots + telas internas | Imagem real dos dois temas, novo RMA e detalhes | `[X]` (Fase 8, `docs/produto/screenshots-fase8/`, 9 capturas) |
| ARQ-08 | Parecer arqueológico consolidado | 17 pontos respondidos | `[X]` |
| LR-01 | Design do LEGACY-RUNTIME | Escrito, compat. PHP verificada | `[X]` |
| LR-02 | `compose.yaml`/`Dockerfile` escritos e testados | TEMA V1 e TEMA V2 respondem em `localhost:8094` | `[X]` |
| LR-03 | Login validado nos dois temas | Smoke test real: login, home, listagem, RMA de fixture, transições, troca de tema | `[X]` |
| LR-04 | Ambiente separado em repositório próprio + porta oficial | `08.24.4-legacy-gerenciador-de-rma`, porta 8094, `scripts/legacy-reset.sh` | `[X]` |
| LR-05 | Mailpit validado de verdade | `mail()` capturado, nada saiu para a internet | `[X]` |
| LR-06 | Execução simultânea com V3 | 6 containers ativos ao mesmo tempo, sem conflito | `[X]` |
| V3-01 | Fundação técnica executável | Laravel 13/PHP 8.3, porta 8095, migrations + testes básicos passando | `[X]` |
| F1 | Fase 1 — Identidade implementada | `sail test` verde, commit `#F1` | `[X]` |
| F2 | Fase 2 — Parceiros implementada | `sail test` verde, commit `#F2` | `[X]` |
| F3 | Fase 3 — Rma núcleo implementada | `sail test` verde, commit `#F3` | `[X]` |
| F4 | Fase 4 — Ciclo de vida implementada | `sail test` verde, commit `#F4` | `[X]` |
| F5 | Fase 5 — Alertas e regras implementada | `sail test` verde, commit `#F5` | `[X]` |
| F6 | Fase 6 — Créditos e relatórios implementada | `sail test` verde, commit `#F6` | `[X]` |
| F7 | Fase 7 — Auditoria implementada | `sail test` verde, commit `#F7` | `[X]` |
| F8 | Fase 8 — Apresentação (Temas V1/V2) implementada | `sail test` verde, screenshots reais, commit `#F8` | `[X]` |
| F9 | Fase 9 — Migração V2→V3 implementada | `sail test` verde, migrador codificado/testado, commit `#F9` | `[X]` (código; execução real contra o Legacy segue pendente — ver Fase 10) |
| F10 | Fase 10 — QA de paridade | 3 eixos de paridade fechados (funcional/visual/dados), gate de conclusão da Trilha A | `[ ]` — não começou |
| MIG-01 | Mapa legado → V3 por tabela/campo | Documento completo (`INV-RMA-06`) | `[X]` |
| MIG-02 | Migrador oficial implementado | Repetível, testável, auditável, idempotente | `[X]` código; `[ ]` execução real contra o Legacy (bloqueio de rede documentado, dry-run real nunca rodou) |
| SAAS-01 | Investigação de evolução SaaS multiempresa | `INV-RMA-07` — fronteira de tenant, banco, isolamento, User×Company, papéis, superadmin, numeração, migração decididos | `[X]` |
| TEMA-V3-01 | Investigação de tema V3 mobile-first (Trilha B) | `INV-RMA-08` — decisões registradas, implementação não iniciada | `[X]` |
| ARQ-ADM-01 | Investigação de anexos de RMA + hub de config admin (Trilha B) | `INV-RMA-09` — decisões registradas, implementação não iniciada | `[X]` |
