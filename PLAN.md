# CellSystem RMA — estado macro

Última atualização: 2026-08-24. Nomenclatura oficial: **RMA V2 FINAL** = 15.9.7 ·
**TEMA V1** = 14.6.1 · **TEMA V2** = 15.8.1 · **RMA V3** = este projeto.

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
- [ ] ARQ-06b — Resolver dúvidas de presença por tema (RN-12 a RN-21 em TEMA V1; default
      de `usuario.app`; RN-11/RN-28/29 em TEMA V1) — ver `INV-RMA-00` §9
- [X] ARQ-07a — Interface e identidade visual, primeira passada com evidência real do
      LEGACY-RUNTIME (`inventario-visual-tema-v1.md`, `inventario-visual-tema-v2.md`) —
      falta screenshot em imagem e telas internas (novo RMA, detalhes)
- [X] ARQ-07b — Inventário de banco dedicado (`inventario-banco-rma-v2.md`)
- [ ] ARQ-08 — Parecer arqueológico consolidado (`docs/pareceres/`)

### LEGACY-RUNTIME — Ambiente executável da V2

- [X] Design registrado (`docs/legado/legacy-runtime-ambiente.md`), verificação estática
      de compatibilidade PHP/MariaDB feita
- [X] `compose.yaml` + `Dockerfile` do PHP legado (PHP 7.4 + Apache + mysqli/mbstring)
- [X] Banco `rma_legacy` de laboratório (schema completo via `schema-only.sql`)
- [X] Login validado em TEMA V1 (14.6.1) e TEMA V2 (15.8.1), ambos em `localhost:8091`
- [ ] Reset determinístico (`reset-legacy.sh`, ainda não escrito)
- [ ] Confirmar neutralização de e-mail de fato (disparar ação real, checar Mailpit)
- [ ] Screenshots/evidência visual dos dois temas autenticados

### MIG-V3 — Migração V2 → V3

- [ ] Mapa completo legado → V3 por tabela/campo (nasce da arqueologia já feita)
- [ ] Migrador oficial (`php artisan rma:migrate-legacy` ou equivalente) — requisito de
      produto, não script descartável
- [ ] Normalizações e resolução de ambiguidade (dedup de parceiro por nome, etc.)
- [ ] Relatório de reconciliação (contagens por entidade, ambíguos, não reconhecidos)
- [ ] Testes de migração determinísticos

## Objetivo 2 — Reconstrução V3

Não iniciar antes de ARQ-06b/07/08 maduros e `INV-RMA-05`/`INV-RMA-06` escritos.
Ordem obrigatória por funcionalidade: legado → evidência → inventário funcional → regra
documentada → decisão de reconstrução → OpenSpec → tasks → implementação. Nenhuma
funcionalidade entra em implementação sem OpenSpec correspondente madura.

- [ ] Arquitetura (compartilhar domínio/controllers/casos de uso; TEMA V1/TEMA V2 só na
      camada de apresentação — investigar antes de fixar)
- [ ] OpenSpecs por capacidade funcional coerente (não uma por botão, não uma gigante)
- [ ] Fundação técnica, autenticação, domínio RMA, cadastros, fluxo, filas, garantia,
      encaminhamento, estoque, conclusão, relatórios, TEMA V1, TEMA V2, seletor de tema
- [ ] QA funcional + QA visual (comparando com LEGACY-RUNTIME rodando) + QA de migração

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
