# CellSystem RMA V3 — estado macro

Última atualização: 2026-08-25 (reconciliação pós-Fases 1-3 + abertura de `INV-RMA-07`).
Nomenclatura oficial: **RMA V2 FINAL** = 15.9.7 · **TEMA V1** = 14.6.1 · **TEMA V2** =
15.8.1 · **RMA V3** = este projeto.

## Dois repositórios oficiais

- **`08.24.4-legacy-gerenciador-de-rma`** (`~/github/08.24.4-legacy-gerenciador-de-rma`)
  — preservação **executável** do RMA V2/15.9.7 (código histórico + Docker). Referência
  funcional/visual/de dados. Nunca modernizado. Ver seu `README.md`. Publicado no GitHub
  (`machadogolang/08.24.4-legacy-gerenciador-de-rma`).
- **`08.24.1-gerenciador-de-rma`** (este repo) — arqueologia consolidada, decisões,
  OpenSpecs, PLAN, e a reconstrução V3 real: **Fases 1 (Identidade), 2 (Parceiros) e 3
  (Rma núcleo) implementadas, testadas e commitadas** (`586513f`/`628475d`/`b2b3e74`);
  Fase 4 (Ciclo de vida) em implementação; Fases 5-8 especificadas (OpenSpec completo);
  Fases 9-10 especificadas, aguardando Fases 4/5 em código para Fase 9 poder ser
  codificada. Ver `docs/produto/checklist-master-v3.md` para o estado granular corrente
  — este documento (`PLAN.md`) é o resumo macro, o checklist é a fonte operacional.
- **`~/github/_rma-arqueologia/`** — fonte bruta (tar.gz original + extração), fora de
  qualquer Git, usada só como origem para os dois repositórios acima.

## Duas trilhas (a partir de `INV-RMA-07`, 2026-08-25)

- **Trilha A — reconstrução fiel** (em andamento, este documento trata dela até aqui):
  legado → arqueologia → RMA V3 → Fases 1-10 → migração V2→V3 → QA de paridade →
  baseline moderna estável. Rastreável por `LEG-RMA-*`/`RN-*`/OpenSpec/
  `paridade-v2-v3.md`.
- **Trilha B — evolução SaaS multiempresa** (investigação aberta e concluída,
  implementação NÃO iniciada): ver `docs/arquitetura/INV-RMA-07-evolucao-saas-
  multiempresa.md` e `docs/produto/backlog-evolutivo.md` (`EVO-SAAS-001`). Recomendação
  registrada: nenhuma linha de código de tenancy antes da baseline de paridade da
  Trilha A estar validada.

## Objetivo 1 — Arqueologia e especificação da V2

### ARQ — Arqueologia

- [X] ARQ-00 — Inventário do backup 15.9.7 (`inventario-tecnico-15.9.7.md`)
- [X] ARQ-01 — Arqueologia TEMA V2/15.8.1
- [X] ARQ-02 — Arqueologia TEMA V1/14.6.1
- [X] ARQ-03 — Arqueologia 14.10.2 (descartado como fonte principal — protótipo sem
      banco)
- [X] ARQ-04 — Camada compartilhada 15.9.7 (`metodo.php`/`conexao.php`/`trocarapp.php`)
- [X] ARQ-05 — Árvore RMA V2 → TEMA V1/TEMA V2, matriz de comparação
- [X] ARQ-06a — Inventário funcional catalogado (`inventario-funcional-rma-v2.md`, 48
      itens `LEG-RMA-NNN`) e matriz de paridade V2→V3 (`paridade-v2-v3.md`)
- [X] ARQ-06b — Dúvidas de presença por tema resolvidas (RN-13 a RN-18, RN-21
      comparadas linha a linha `14.6.1/post/*` vs `15.8.1/pp/*`) — achado maior:
      troca de senha funciona em TEMA V1 e está quebrada em TEMA V2 (regressão real).
      Único residual: RN-12/threshold R$75, não localizado em TEMA V1 por busca textual.
- [X] ARQ-07a — Interface e identidade visual, primeira passada com evidência real do
      LEGACY-RUNTIME (`inventario-visual-tema-v1.md`, `inventario-visual-tema-v2.md`) —
      falta screenshot em imagem e telas internas (novo RMA, detalhes)
- [X] ARQ-07b — Inventário de banco dedicado (`inventario-banco-rma-v2.md`)
- [X] ARQ-08 — Parecer arqueológico consolidado (`docs/pareceres/2026-08-24-parecer-arqueologia-rma.md`)

### LEGACY-RUNTIME — vive em `08.24.4-legacy-gerenciador-de-rma`

- [X] V2 em `:8094` — porta oficial fixa, documentada em `docs/desenvolvimento/
      ambiente-v2-v3.md`
- [X] TEMA V1 validado — smoke test real (login, home, listagem)
- [X] TEMA V2 validado — smoke test real (login, home, listagem, criar/localizar RMA de
      fixture, transições receber/concluir)
- [X] Troca de tema validada — `trocarapp.php` + persistência confirmada após
      logout/login (preferência `usuario.app` lida corretamente no redirect de login)
- [X] Banco de laboratório — `rma_legacy` (MariaDB), schema sanitizado, **banco
      compartilhado entre os dois temas confirmado por evidência direta** (RMA criado
      via TEMA V2 aparece na listagem do TEMA V1)
- [X] Mailpit validado de fato — `mail()` disparado dentro do container, capturado no
      Mailpit, nada saiu para a internet
- [X] Execução simultânea com V3 — confirmada, 6 containers (3+3) ativos ao mesmo
      tempo, sem conflito de porta/nome/rede/volume
- [ ] Remoto no GitHub — **`machadogolang/08.24.4-legacy-gerenciador-de-rma` ainda não
      existe** (HTTP 404 verificado via API), apesar de instrução anterior dizer que os
      dois repositórios já tinham sido publicados. Repositório continua só local.

### V3-BASE

- [X] Laravel 13/PHP 8.3 sobe (`sail up -d`)
- [X] Porta `:8095` — oficial, fixa
- [X] Infraestrutura isolada (`name: rma-v3` no compose, containers/rede/volume
      próprios, sem colisão com o Legacy)
- [X] Banco local (`rma_v3`, MySQL 8.4) — migrations padrão do Laravel aplicadas, ainda
      **sem nenhuma tabela de domínio RMA** (correto — aguarda OpenSpec)
- [X] Smoke test básico — HTTP 200 na home, suíte de testes (2/2) passando

### MIG-V3 — Migração V2 → V3

- [X] Mapa completo legado → V3 por tabela/campo (`INV-RMA-06`)
- [X] OpenSpec do migrador (`openspec/changes/migracao-v2-v3/`) — especificado, não
      codificado (bloqueado até Fases 4/5 existirem em código)
- [X] Normalizações e resolução de ambiguidade (dedup de parceiro por nome — já
      implementado para `Cliente` na Fase 2, `EncontrarOuCriarCliente`; generalização
      para os outros 3 tipos especificada na Fase 9)
- [X] Relatório de reconciliação — especificado (`RelatorioDeReconciliacao`,
      `INV-RMA-05` §14)
- [ ] Migrador codificado + testes de migração determinísticos — pendente (Fase 9)

## Objetivo 2 — Reconstrução V3

Ordem obrigatória por funcionalidade: legado → evidência → inventário funcional → regra
documentada → decisão de reconstrução → OpenSpec → tasks → implementação. Nenhuma
funcionalidade entra em implementação sem OpenSpec correspondente madura.

### Arquitetura — decidida em `INV-RMA-05`

- [X] `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` escrita — **monólito
      modular, referência CONAHOM real** (inspecionado o código de
      `~/github/online-conahom-laravel/app/`, não copiado de memória):
      `app/{Modulo}/{Dominio,Aplicacao,Infraestrutura}` por módulo de domínio +
      `app/Compartilhado/`, casos de uso nomeados por verbo em `Aplicacao/`, convenção
      `...EmBanco` para adapter Eloquent de uma interface de `Dominio/` **só onde isso
      se justifica** (não em todo módulo — CRUD simples usa Eloquent direto).
- [X] Decisão explícita de proporcionalidade: **não** replicar a abstração de
      Identidade própria do CONAHOM (lá existe para reconciliar identidade entre dois
      sistemas — problema que o RMA não tem); Fase 1 usa `App\Models\User` padrão do
      Laravel. Interface de repositório de domínio só se justifica no módulo `Rma`
      (por causa da migração do schema `rma_legacy`, bem diferente do novo).
- [X] Princípio fixo registrado: **sem número mágico** — todo conceito de domínio
      fechado vira `enum` PHP com métodos nomeados de comparação (nunca
      `$papel->value >= 3` nem reprodução direta do `-1/1/2/3/4` do legado); o número
      original do legado só pode existir dentro do migrador (`MIG-V3`), isolado.
- [X] Módulos definidos: `Identidade`, `Parceiros`, `Rma` (créditos e relatórios ficam
      dentro dele, não módulos próprios — são sub-fluxo/consulta, não entidade
      independente), `Compartilhado`. "Temas" (V1/V2) não é módulo de domínio, é
      apresentação (`resources/views/temas/{v1,v2}/`).
- [X] Tecnologia consolidada (tabela completa no documento): Laravel 13/PHP 8.3, MySQL
      8.4 (Sail), `Auth`/Breeze/`Hash` nativos, Blade+Vite+Sass+Bootstrap 5.3, PHPUnit +
      Playwright.
- [X] 10 fases de implementação definidas por ordem de dependência (Identidade →
      Parceiros → Rma núcleo → ciclo de vida → alertas/regras → créditos/relatórios →
      auditoria → apresentação/temas → migração → QA de paridade).
- [X] Fases 1-8 detalhadas arquivo por arquivo em `INV-RMA-05` §6-§13; Fases 9-10 em
      §14-§15 (com `INV-RMA-06`, o mapa campo-a-campo, para a Fase 9).
- [X] `INV-RMA-06` (estratégia de migração formal) — escrita (`docs/arquitetura/
      INV-RMA-06-estrategia-reconstrucao.md`).
- [X] `INV-RMA-07` (investigação de evolução SaaS multiempresa, Trilha B) — escrita e
      concluída (`docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md`,
      2026-08-25). Implementação de tenancy **não iniciada, propositalmente** — só
      depois da baseline de paridade (Fases 1-10 + QA).

- [X] OpenSpecs escritas para todas as 10 fases (`openspec/changes/`) —
      `autenticacao-usuarios`, `parceiros`, `rma-cadastro-e-localizacao`,
      `rma-ciclo-de-vida`, `rma-alertas-e-prioridade`, `rma-creditos-e-relatorios`,
      `rma-logistica-e-historico`, `temas-v1-v2`, `migracao-v2-v3`, `qa-paridade`.
- [X] Fase 1 (Identidade) — implementada, testada, commitada (`586513f`).
- [X] Fase 2 (Parceiros) — implementada, testada, commitada (`628475d`).
- [X] Fase 3 (Rma núcleo) — implementada, testada, commitada (`b2b3e74`) — 85/85 testes
      verdes, 189 assertions (verificado pessoalmente).
- [R] Fase 4 (Ciclo de vida) — em implementação.
- [ ] Fases 5-10 — especificadas, não codificadas. Fase 9 formalmente bloqueada até as
      Fases 4/5 existirem em código.

### PARIDADE

- [ ] Comparação funcional (por `LEG-RMA-NNN`, ver `paridade-v2-v3.md`)
- [ ] Comparação visual (TEMA V1 e TEMA V2, lado a lado com o Legacy rodando em
      `:8094`)
- [ ] Comparação de dados (banco `rma_legacy` migrado → `rma_v3`, contagens batendo)

## Objetivo 3 — Evolução (Trilha B)

- [X] Investigação SaaS multiempresa aberta e concluída (`INV-RMA-07`, 2026-08-25) —
      fronteira de tenant, estratégia de banco, isolamento, `User`×`Company`, papéis,
      superadmin, numeração, migração do tenant CellSystem — tudo decidido no nível de
      arquitetura, nada implementado.
- [ ] Implementação — não iniciada, propositalmente. Só depois de paridade
      funcional/visual/de dados comprovada (Fases 1-10 + QA). Ver
      `docs/produto/backlog-evolutivo.md` (`EVO-SAAS-001`).

## Critério de sucesso da V3

Três paridades comprovadas (ver `INV-RMA-00` §11): **funcional** (regras/fluxos),
**visual** (TEMA V1 e TEMA V2 reconhecíveis, coexistindo), **de dados** (banco V2
migrável para V3 sem perda). Só com as três é possível afirmar que a V3 é continuação
real do produto, não um sistema diferente inspirado nele.

## Legenda

`[ ]` não iniciado · `[R]` em investigação/revisão · `[X]` concluído · `[B]` bloqueado.
