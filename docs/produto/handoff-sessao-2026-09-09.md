# Handoff de sessão — CellSystem RMA V3

Data de encerramento: 2026-09-09. Substitui
`docs/produto/handoff-sessao-2026-08-26.md` como ponto de partida (os anteriores
ficam como histórico). Fonte de status sempre atualizada: `PLANO-ATAQUE.md`; leitura
de estado e lacunas: `docs/produto/diagnostico-estado-pos-gate-2026-09-09.md`.

## Estado geral

- Trilha A formalmente encerrada em 2026-09-04 (`F10-GATE-07`); `G-04..G-07` fechados
  e `G-08` (Trilha B liberada) fechado em 2026-09-09 no checklist, por decisão do
  usuário para execução controlada do EVO-SAAS-001.
- **EVO-SAAS-001 em execução**: S1–S8 + S9–S12 + S3.6 implementados/commitados
  (OpenSpec/mapa, Company/company_user, tenant CellSystem/backfill, TenantContext,
  isolamento por construção, papel por vínculo, numeração por empresa, migrador
  CellSystem, gate arquitetural, hardening NOT NULL). Restam S9.8, S10.4, S11.4,
  S13 completo e S14/gate formal.
- `main` local limpa e sincronizada com `origin/main` em `46c8204` nesta retomada
  (nada foi enviado/pushado por agente).
- Suíte PHPUnit corrente: **448 testes / 1070 assertions, 100% verde**.
- Runtime Docker local iniciado para validação (`rma-v3-mysql-1`,
  `rma-v3-laravel.test-1`, `rma-v3-mailpit-1`); imagens locais disponíveis.

O histórico dos primeiros ciclos da sessão (baseline FRONT-004/ARQ-004 etc., suíte
396/967) permanece nas seções abaixo como registro da sessão — não compete com o
estado corrente acima.

## Incidente operacional registrado

- Sandbox do harness falha para qualquer comando não aprovado com
  `bwrap: loopback: Failed RTM_NEWADDR` (mesmo `pwd`). Causa na camada de sandbox,
  não no repositório.
- Solução comprovada: prefixos já aprovados ou execução escalada de leitura pontual;
  edições de arquivos existentes via comando fora do sandbox com aprovação, porque o
  `apply_patch` também depende do sandbox para leitura.
- Registro e regra operacional: `docs/operacao/incidentes/2026-09-09-sandbox-bwrap-loopback.md`
  e nota no `AGENTS.md`.

## O que foi feito nesta sessão (commits)

1. `47c1414` — `#OPS-RMA`: registro do incidente de sandbox + regra operacional.
2. `77a8caa` — `#DOC-RMA`: diagnóstico de estado pós-gate (feito, doc obsoleta,
   lacunas reais).
3. `5d6baed` — `#ARQ-RMA`: FRONT-004/D-06 — `/` redireciona convidado para `login` e
   autenticado para `dashboard`; `welcome.blade.php` e os dois `ExampleTest`
   placeholders removidos; novo `RaizRedirecionamentoTest` (2 testes).
4. `9657236` — `#ARQ-RMA`: ARQ-004/PAR-RMA-001 — busca por NF consulta campos
   fiscais reais (`nfcompra`/`nfvenda`/`nf_remessa`/`nf_retorno_numero`/campos
   históricos); `os` vira critério próprio; mapeamento `NF` e `os` do painel
   Localizar V1 corrigido com fonte 14.6.1/15.8.1; 8 testes/23 assertions.
5. `c8fc639` — `#DOC-RMA`: reconciliação de gates G-04..G-07, H-011, H-012, H-021 e
   matriz de busca por NF.
6. `3b9e016` — `#ARQ-RMA`: PAR-RMA-002/PAR-RMA-003 parcial — busca pela chave
   histórica (`numero_legado`, critério `numero` + campo `CHAVE` do V1) e campos
   diretos do legado no texto; 3 testes novos.
7. `86bc9f4` — `#DOC-RMA`: reconciliação H-022 fechado e H-023 parcial.

## Regras verificadas nesta sessão

- Nunca alterar fontes históricas: leituras da arqueologia foram feitas apenas em
  `~/github/_rma-arqueologia/backup-15.9.7/extracted/`.
- Sem push, PR ou merge remoto.
- Commits locais pequenos por fase, com teste/evidência antes do commit.

## Próximos passos (ordem sugerida no PLANO-ATAQUE)

1. Reconciliar itens documentais residuais das seções F10-V1/F10-VIS com ponteiros
   para os diários CP/NAV (evidência já registrada).
2. Executar lotes pequenos da frente H com prova histórica, um por commit:
   `ARQ-008` (docblock), `ARQ-005` (destinatário/erros esperados), `FRONT-005`
   (disclosure `.pmo`), `PAR-PARCEIRO-001` (detalhe/RMAs do parceiro).
3. Investigar `C-02/C-03/C-04` (RN-12 V1, Lightbox2, skin AdminLTE) com evidência
   dirigida à fonte histórica.
4. Definir com o usuário a primeira iniciativa da Trilha B (sugestão:
   `EVO-CONF-001` — OpenSpec completo em `openspec/changes/configuracao-admin/`).

## Notas operacionais

- Para rodar testes: containers locais já sobem com `docker compose up -d mysql
  laravel.test`; suíte completa: `docker compose exec -T laravel.test php artisan
  test` (~60-80s).
- Playwright visual: `ParidadeVisualTemaV1.spec.ts` roda do host, os demais specs no
  container (ver handoff 2026-08-26).
- Screenshots com dado real do Legacy continuam fora do diretório versionado.


## Atualização final — execução EVO-SAAS-001 (ondas S1 a S8)

**SHA inicial da segunda rodada:** `64f1a31`. **SHA final:** `012a479`. **origin/main
no fechamento:** `d466829` (HEAD local 10 commits à frente). Nada foi pushado por
este agente; a sincronização do origin aconteceu por fora da sessão.

### Commits desta segunda rodada (10 código/doc + tasks)
- `6f80445` — Reconcilia estado pós-gate e abre Trilha B para execução controlada.
- `d466829` — Especifica fundação SaaS multiempresa com OpenSpec executável e mapa tenant-scoped.
- `b9e0e1a` — Introduz Company e vínculo de usuários com papel por empresa.
- `d135534` — Marca S1.7 e S2 como concluídas no OpenSpec.
- `13124af` — Cria tenant CellSystem e faz backfill dos dados existentes.
- `c52f008` — Adiciona TenantContext e resolução de empresa após autenticação.
- `14a92fb` — Adiciona isolamento automático de tenant em parceiros, RMA e histórico.
- `d9cc5b2` — Isola parceiros por tenant com suíte A×B permanente.
- `7710546` — Isola repositório de RMA por tenant com suíte A×B.
- `a53f10d` — Escopa auditoria/histórico por tenant com prova A×B.
- `d763bd3` — Marca ondas S3-S8 no OpenSpec e registra pendência S3.6.

### Testes executados
- PHPUnit completo: **431 testes / 1023 assertions verdes**.
- Suítes dirigidas: CompanyUser, TenantSemente, ContextoDeTenant, IsolamentoBasico,
  ParceirosIsolamento (16), RmaIsolamento (4), AuditoriaIsolamento (1), mais regressão
  ampla de Identidade/Parceiros/Temas.

### Estado EVO-SAAS-001
- Ondas concluídas: S1-S8 (mapeamento, fundação, tenant inicial/backfill, contexto,
  isolamento por construção, parceiros, RMA, auditoria).
- Pendente: S3.6 (hardening NOT NULL, adiado por segurança), S9-S14.
- Gate de isolamento: não declarado (aguarda S12 e prova em dado real).

### Decisões pendentes para o usuário
1. Representação do administrador de plataforma — ortogonal a `Papel`; só exigida
   quando a plataforma tiver ação real.
2. UI de seletor de empresa (multi-vínculo): backend pronto; sem decisão de produto.

### Próximo item EXATO
`S9.1` — mapear todos os consumidores de `users.papel` e iniciar leitura do papel do
vínculo ativo no `ContextoDeTenant`/User, mantendo compatibilidade até S9.7.

### Comando de retomada
```
docker compose up -d mysql laravel.test
docker compose exec -T laravel.test php artisan test
```


## Atualização final — ondas S9 a S12 + S3.6

**SHA inicial desta rodada:** `46c8204`. **SHA final:** `201f3ff` (working tree limpa;
origin será sincronizado por fora). PUSH NÃO REALIZADO.

### Commits desta rodada
- `375d26b` — Reconcilia estado atual do EVO-SAAS-001 antes da onda S9.
- `bfbfd16` — Adiciona leitura do Papel pelo vínculo ativo no contexto de tenant.
- `4db743d` — Adapta Policies e casos de uso ao Papel da empresa ativa.
- `6f3a590` — Prova papéis distintos do mesmo usuário entre empresas e login por vínculo.
- `df9613a` — Passa gestão de papel a gravar no vínculo da empresa ativa.
- `aec1af4` — Adapta tela de usuários e seed de QA ao papel do vínculo.
- `ff2d6fa` — Registra matriz de consumidores e status S9 no OpenSpec.
- `65e2c01` — Adiciona numeração transacional de RMA por empresa com contador dedicado.
- `8810fcd` — Prova sequência 1 e 2 de RMA na mesma empresa com id técnico global.
- `b72df75` — Registra status S10 no OpenSpec e pendência do teste concorrente.
- `9066a47` — Integra tenant CellSystem ao migrador histórico com vínculo de usuários.
- `3b7ba16` — Registra status S11 no OpenSpec.
- `5932197` — Cria suite arquitetural de isolamento multiempresa com inventário canônico.
- `6f498b0` — Endurece tenant_id NOT NULL e garante contexto CellSystem nos importadores.
- `201f3ff` — Garante contexto CellSystem no teste do helper de cliente.

### Testes
- PHPUnit completo: **448 testes / 1070 assertions verdes** (última execução 2026-09-09).

### Estado EVO-SAAS-001
- Concluído: S1–S8, S9 (papel por vínculo), S10 (contador/numeração), S11
  (migrador CellSystem), S12 (gate arquitetural) e S3.6 (hardening NOT NULL).
- Pendente: S9.8 (remover users.papel), S10.4 (concorrência por processo externo),
  S11.4 (tenant no relatório do migrador), S13 (Playwright/segurança final), S14
  (gate formal).

### Próximo item EXATO
`S13.1→S13.2` já fechados; próximo: `S13` completo começando por Playwright relevante
de login/troca de tenant/autorização e, em paralelo, a prova externa `S10.4`.

### Comando de retomada
```
docker compose up -d mysql laravel.test
docker compose exec -T laravel.test php artisan test
```
