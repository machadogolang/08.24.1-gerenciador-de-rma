# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-25 (reconciliação pós-Fases 1-3 + abertura de `INV-RMA-07`).

## AGORA

**Trilha A (reconstrução) em andamento real:** Fases 1 (Identidade), 2 (Parceiros) e 3
(Rma núcleo) implementadas, testadas e commitadas (`586513f`/`628475d`/`b2b3e74`, 85/85
testes verdes na última verificação pessoal). Fase 4 (Ciclo de vida) em implementação.
Fases 5-10 especificadas (OpenSpec completo), não codificadas — Fase 9 formalmente
bloqueada até Fases 4/5 existirem em código. **Trilha B aberta:** `INV-RMA-07`
(evolução SaaS multiempresa) investigada e concluída em 2026-08-25 — decisões de
arquitetura registradas, implementação de tenancy propositalmente não iniciada (só
depois da baseline de paridade). Checklist granular por fase em
`docs/produto/checklist-master-v3.md` continua sendo o mapa operacional — este arquivo
é só o resumo de fase/dependência/critério de saída.

## DEPOIS

1. Fase 4 (Ciclo de vida) — concluir implementação em andamento.
2. Fase 5 (Alertas e regras) — próxima a implementar após Fase 4, na mesma disciplina
   TDD das anteriores.
3. Fases 6-8 — na ordem de dependência já fixada (`INV-RMA-05` §5).
4. Fase 9 (Migração) — só depois das Fases 4/5 existirem em código (os enums que o
   migrador traduz).
5. Fase 10 (QA de paridade) — fecha por último, gate de conclusão da Trilha A.
6. Trilha B (SaaS) — implementação só depois do gate acima, conforme `INV-RMA-07` §13.
7. Item residual de baixo risco, não bloqueante: RN-12 (threshold R$75) — confirmar
   ausência/presença em TEMA V1 com leitura linha a linha completa, se necessário.
8. ARQ-07c — screenshots/telas internas (novo RMA, detalhes) dos dois temas.

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
| ARQ-07c | Screenshots + telas internas | Imagem real dos dois temas, novo RMA e detalhes | `[ ]` |
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
| F4 | Fase 4 — Ciclo de vida implementada | `sail test` verde, commit `#F4` | `[R]` em implementação |
| MIG-01 | Mapa legado → V3 por tabela/campo | Documento completo (`INV-RMA-06`) | `[X]` |
| MIG-02 | Migrador oficial implementado | Repetível, testável, auditável, idempotente — bloqueado até Fases 4/5 em código | `[ ]` |
| SAAS-01 | Investigação de evolução SaaS multiempresa | `INV-RMA-07` — fronteira de tenant, banco, isolamento, User×Company, papéis, superadmin, numeração, migração decididos | `[X]` |
