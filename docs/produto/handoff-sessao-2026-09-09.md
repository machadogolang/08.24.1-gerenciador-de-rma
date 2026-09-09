# Handoff de sessão — CellSystem RMA V3

Data de encerramento: 2026-09-09. Substitui
`docs/produto/handoff-sessao-2026-08-26.md` como ponto de partida (os anteriores
ficam como histórico). Fonte de status sempre atualizada: `PLANO-ATAQUE.md`; leitura
de estado e lacunas: `docs/produto/diagnostico-estado-pos-gate-2026-09-09.md`.

## Estado geral

- Trilha A formalmente encerrada em 2026-09-04 (`F10-GATE-07`); nesta sessão os gates
  `G-04..G-07` foram reconciliados como `[x]` no checklist com evidência.
- `G-08` (liberação formal da Trilha B) permanece aberto e depende da escolha da
  primeira iniciativa pelo usuário; nenhuma feature `EVO-*` foi implementada.
- `main` local limpa e adiante de `origin/main` (nada foi enviado/pushado).
- Suíte PHPUnit renovada nesta sessão: **396 testes / 967 assertions, 100% verde**
  (baseline era 388/941; 2 placeholders removidos e 10 testes reais adicionados).
- Runtime Docker local iniciado para validação (`rma-v3-mysql-1`,
  `rma-v3-laravel.test-1`, `rma-v3-mailpit-1`); imagens locais disponíveis.

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
