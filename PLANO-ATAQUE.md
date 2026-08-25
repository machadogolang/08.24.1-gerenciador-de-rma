# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-25 (reconciliação pós-Fase 4 + detalhe granular do que
falta nas Fases 5-10).

## AGORA

**Trilha A (reconstrução) em andamento real:** Fases 1 (Identidade), 2 (Parceiros), 3
(Rma núcleo) e 4 (Ciclo de vida) implementadas, testadas e commitadas
(`586513f`/`628475d`/`b2b3e74`/`a302c7b`, 131/131 testes verdes na última verificação
pessoal). Fases 5-10 especificadas (OpenSpec completo), não codificadas — Fase 9
formalmente bloqueada até Fases 4/5 existirem em código (4 já existe; falta a 5).
**Trilha B aberta:** `INV-RMA-07`
(evolução SaaS multiempresa) investigada e concluída em 2026-08-25 — decisões de
arquitetura registradas, implementação de tenancy propositalmente não iniciada (só
depois da baseline de paridade). Checklist granular por fase em
`docs/produto/checklist-master-v3.md` continua sendo o mapa operacional — este arquivo
é só o resumo de fase/dependência/critério de saída.

## DEPOIS — o que falta em cada fase (detalhe granular, espelha `checklist-master-v3.md`)

### Fase 5 — Alertas e regras (próxima a implementar)

OpenSpec: `openspec/changes/rma-alertas-e-prioridade/`. Decisão já tomada: filtro de
data inteiramente no SQL, nunca em PHP pós-`SELECT`. Falta:

- Migration com `prioridade`, `marcarestoque`, NF (compra/venda), `lancadoretorno`
- Enums `Origem`, `Prioridade`, `StatusDeLancamento`, `ClasseDeAlerta`
- 10 classes de regra + `UrgenciaPorThreshold` (`app/Rma/Aplicacao/Alertas/`)
- `Rma::classeDeAlerta()`, `Rma::prazoLegal()`
- Controller + view do painel + rotas
- 12 arquivos de teste unitário (10 regras + `ClasseDeAlerta` + threshold)
- `sail test` verde, `paridade-v2-v3.md` (`LEG-RMA-018` a `029`), commit `#F5`

### Fase 6 — Créditos e relatórios

OpenSpec: `openspec/changes/rma-creditos-e-relatorios/`. Cobre `LEG-RMA-036` a `039` e
`048` (fluxo único, não 3 sub-rotas como o legado quebrado de TEMA V2). Falta:

- Migration com `credito_disponivel`
- `MarcarCreditoDisponivel`, `AguardandoCredito`
- 3 relatórios (RCD/RPEC/RMPE) — RMPE corrige intervalo hardcoded para 2014
- Controller + views + rotas
- 4 arquivos de teste
- `sail test` verde, `paridade-v2-v3.md`, commit `#F6`

### Fase 7 — Auditoria

OpenSpec: `openspec/changes/rma-logistica-e-historico/` (cobre também `LEG-RMA-040`/
`041`). Decisão já tomada: `ConsolidarFretePorCidade` usa TEMA V2 como especificação;
log de modificação usa snapshot estruturado com ação nomeada (`EVO-AUD-001`, diff
campo-a-campo, fica pendência registrada). Falta:

- Migration `modificacoes_de_rma` (FK real para `rmas`/`users`)
- Enum `AcaoDeModificacao`
- `RegistrarModificacaoDeRma`, `EnviarNotificacaoDeConclusao`,
  `EnviarNotificacaoDeTentativaNaoPermitida` (listeners)
- `ConsolidarFretePorCidade`, `BoletinsRelacionados`
- Controllers de histórico (modificação + acesso) + views + rotas
- 7 arquivos de teste
- `sail test` verde, pendência `EVO-AUD-001` registrada (perguntar ao usuário),
  `paridade-v2-v3.md` (`LEG-RMA-040/041/044/045`), commit `#F7`

### Fase 8 — Apresentação (Temas V1/V2)

OpenSpec: `openspec/changes/temas-v1-v2/`. As 2 pendências originais (âncoras de TEMA
V2; RN-11 em TEMA V1) já foram **resolvidas** por inspeção direta do LEGACY-RUNTIME.
**2 decisões de produto continuam pendentes, bloqueando a view final** — precisam de
você antes de codificar:

1. Fonte Open Sans do TEMA V2 nunca carrega de fato (URL de produção morta) —
   reproduzir o fallback quebrado ou self-hostar corretamente?
2. Comportamento pós-login assimétrico (gateway respeita `tema_preferido`; login
   próprio de TEMA V1 sempre fica em V1) — reproduzir a assimetria ou corrigir?

Falta (depois das 2 decisões acima):

- Sass por tema (`v1.scss`/`v2.scss`/`_compartilhado.scss` — este último porta o
  CSS/JS compartilhado real `pattern/15.9.7.css`/`.js`, achado desta revisão)
- `ResolverTemaAtivo` (middleware) + rotas por tema + `identidade/login.blade.php` do
  gateway compartilhado
- Árvore de Blade por tema (`resources/views/temas/{v1,v2}/`)
- Testes de smoke por tema + Playwright (390/768/1440 — TEMA V1 layout fixo, TEMA V2
  breakpoints próprios via `css/media.php`)
- Screenshots reais (PNG) — fecha pendência residual da arqueologia (ARQ-07c)
- `sail test` verde, `checklist-master-v3.md`/`paridade-v2-v3.md` (paridade visual),
  commit `#F8`

### Fase 9 — Migração V2→V3

OpenSpec: `openspec/changes/migracao-v2-v3/`. Mapa campo-a-campo completo:
`INV-RMA-06`. **Bloqueada para codificar até Fase 5 existir em código** (Fase 4 já
existe). Falta:

- Migrador oficial (`php artisan rma:migrar-legado`) + 8 importadores + relatório de
  reconciliação + idempotência
- Teste de migração determinístico
- Resolver ou registrar decisão para as 4 pendências de `INV-RMA-06` (formato de data
  ambíguo; ocorrência real de `status='retornou'`; destino de
  `relatorio.informacaoadicional`; coordenação de `rmas.valor`)

### Fase 10 — QA de paridade (fecha por último)

OpenSpec: `openspec/changes/qa-paridade/`. Critério objetivo por eixo já fechado em
`INV-RMA-05` §15. Falta:

- Paridade funcional por `LEG-RMA-NNN` (atualizar `paridade-v2-v3.md` a cada fase, já
  em andamento)
- Paridade visual (screenshot V2×V3, Playwright, 390/768/1440)
- Paridade de dados (contagens pós-migração, depende da Fase 9)
- `docs/qa/roteiro-paridade-funcional.md` e `docs/qa/relatorio-paridade-final.md`

### Trilha B (SaaS)

Implementação só depois do gate de conclusão da Fase 10, conforme `INV-RMA-07` §13.
Nada a fazer agora além do que `INV-RMA-07`/`EVO-SAAS-001` já registram.

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
