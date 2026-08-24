# CellSystem RMA V3 — estado macro

Última atualização: 2026-08-24. Nomenclatura oficial: **RMA V2 FINAL** = 15.9.7 ·
**TEMA V1** = 14.6.1 · **TEMA V2** = 15.8.1 · **RMA V3** = este projeto.

## Dois repositórios oficiais

- **`08.24.4-legacy-gerenciador-de-rma`** (`~/github/08.24.4-legacy-gerenciador-de-rma`)
  — preservação **executável** do RMA V2/15.9.7 (código histórico + Docker). Referência
  funcional/visual/de dados. Nunca modernizado. Ver seu `README.md`.
- **`08.24.1-gerenciador-de-rma`** (este repo) — arqueologia consolidada, decisões,
  OpenSpecs, PLAN, e a reconstrução V3 em si (ainda não iniciada).
- **`~/github/_rma-arqueologia/`** — fonte bruta (tar.gz original + extração), fora de
  qualquer Git, usada só como origem para os dois repositórios acima.

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

- [ ] Mapa completo legado → V3 por tabela/campo (nasce da arqueologia já feita)
- [ ] Migrador oficial (`php artisan rma:migrate-legacy` ou equivalente) — requisito de
      produto, não script descartável
- [ ] Normalizações e resolução de ambiguidade (dedup de parceiro por nome, etc.)
- [ ] Relatório de reconciliação (contagens por entidade, ambíguos, não reconhecidos)
- [ ] Testes de migração determinísticos

## Objetivo 2 — Reconstrução V3

Não iniciar antes de ARQ-08 e `INV-RMA-05`/`INV-RMA-06` escritos. Ordem obrigatória por
funcionalidade: legado → evidência → inventário funcional → regra documentada → decisão
de reconstrução → OpenSpec → tasks → implementação. Nenhuma funcionalidade entra em
implementação sem OpenSpec correspondente madura.

- [ ] Arquitetura (compartilhar domínio/controllers/casos de uso; TEMA V1/TEMA V2 só na
      camada de apresentação — investigar antes de fixar)
- [ ] OpenSpecs por capacidade funcional coerente (catálogo proposto em
      `PLANO-ATAQUE.md`, nenhuma escrita ainda)
- [ ] Autenticação, domínio RMA, cadastros, fluxo, filas, garantia, encaminhamento,
      estoque, conclusão, relatórios, TEMA V1, TEMA V2, seletor de tema (fundação
      técnica pura já concluída — ver V3-BASE acima)

### PARIDADE

- [ ] Comparação funcional (por `LEG-RMA-NNN`, ver `paridade-v2-v3.md`)
- [ ] Comparação visual (TEMA V1 e TEMA V2, lado a lado com o Legacy rodando em
      `:8094`)
- [ ] Comparação de dados (banco `rma_legacy` migrado → `rma_v3`, contagens batendo)

## Objetivo 3 — Evolução (Trilha B)

- [ ] Não iniciado — só depois de paridade funcional/visual/de dados comprovada. Ver
      `docs/produto/backlog-evolutivo.md`.

## Critério de sucesso da V3

Três paridades comprovadas (ver `INV-RMA-00` §11): **funcional** (regras/fluxos),
**visual** (TEMA V1 e TEMA V2 reconhecíveis, coexistindo), **de dados** (banco V2
migrável para V3 sem perda). Só com as três é possível afirmar que a V3 é continuação
real do produto, não um sistema diferente inspirado nele.

## Legenda

`[ ]` não iniciado · `[R]` em investigação/revisão · `[X]` concluído · `[B]` bloqueado.
