# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-30 16:24 (America/Sao_Paulo). Fonte granular:
`docs/produto/checklist-master-v3.md`.
Handoff para nova sessão: `docs/produto/handoff-sessao-2026-08-26.md` (substitui o
handoff de 2026-08-25, mantido só como histórico).

## AGORA

**Frentes Visuais e Navegacionais Encerradas e Aprovadas:**
- Fase 1 (`docs/produto/plano-execucao-paridade-estrutural-v1.md`, CP1 a CP5) — **fechada**;
- Fase 2 (`docs/produto/plano-execucao-paridade-visual-v1-fase2.md`, CP6 a CP15) — **fechada**;
- Frente Paralela Tema V2 (`docs/produto/plano-execucao-paridade-v2.md`, CP16 a CP25) — **fechada**;
- Auditoria Navegacional e Visual do Tema V1 (`docs/produto/plano-execucao-auditoria-navegacional-visual-v1.md`, NAV-00 a NAV-05) — **fechada e consolidada** (`1a0aff2`).

---

### Esteira de Execução Sequencial Priorizada (Fase 10 / Fechamento da Trilha A)

Executar sequencialmente, um item por vez, com teste, evidência e commit atômico:

#### 1. Eixo Funcional (`F10-FUN`)
- [x] **ETA-01** — Executar e registrar formalmente os 6 smokes cruzados (M-01 a M-06) em `docs/qa/roteiro-paridade-funcional.md`, consolidando as evidências já obtidas em NAV-01..NAV-05 e Playwright (`tests/Browser/SmokesParidadeFuncional.spec.ts`).
- [x] **ETA-02** — Reconciliar a matriz completa de 48 IDs em `docs/produto/paridade-v2-v3.md` e fechar `F10-FUN-07` e `F10-FUN-08` no `checklist-master-v3.md`.

#### 2. Decisões e Refinamentos de Domínio / Produto
- [x] **ETA-03** — Formalizar decisão `LEG-RMA-002` (`DECISAO C-01`): modelo seguro de provisionamento (admin-only via `UsuarioController` / convite administrativo, sem chave estática exposta; parecer de 2026-09-04).
- [x] **ETA-04** — Formalizar decisão `VIS-V1-011` / `VIS-V1-012` (`F10-COB-03` e `F10-COB-04`): proteção de integridade referencial e auditoria imutável (arquivamento e soft-delete seguro contra hard-delete destrutivo; parecer de 2026-09-04).
- [x] **ETA-05** — Resolver achado CP14 (`Rma::classeDeAlerta()` com `ClasseDeAlerta::Urgente` para prioridade alta e prazo 30 dias estourado), com prova automatizada em teste unitário e de feature.

#### 3. Eixo de Dados (`F10-DAD`)
- [x] **ETA-06** — Viabilizar rede Docker V3→Legacy (`OPS F10-DAD-01` a `03`) conectando `rma-v3-laravel.test-1` à rede `rma-legacy_legacy-lab` do MariaDB na porta 3306/3309 com usuário `rma_legacy_readonly` estrito.
- [x] **ETA-07** — Executar `php artisan rma:migrar-legado --dry-run` contra a base de dados histórica, gerar relatório de reconciliação das 9 tabelas, auditar anomalias e provar idempotência no alvo descartável `rma_v3_descartavel` (`QA F10-DAD-04` a `09`).

#### 4. Fechamento da Fase 10 e Gate da Trilha A (`F10-GATE`)
- [ ] **ETA-08** — Rodar suíte completa de regressão técnica (PHPUnit 388+ testes e Playwright Browser no host e container).
- [ ] **ETA-09** — Produzir o relatório consolidado final em `docs/qa/relatorio-paridade-final.md` integrando os três eixos (funcional, visual e dados).
- [ ] **ETA-10** — Atualizar `checklist-master-v3.md` declarando o cumprimento integral da Fase 10 e encerramento da Trilha A.

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
