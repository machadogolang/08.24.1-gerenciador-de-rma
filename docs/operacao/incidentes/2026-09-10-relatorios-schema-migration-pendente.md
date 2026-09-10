# Incidente 2026-09-10 - relatorios com HTTP 500 por migration pendente

ID: `RMA-BUG-REL-SCHEMA-001`
Data: 2026-09-10 (America/Sao_Paulo)
Ambiente: local (Sail, `http://localhost:8095`, MySQL `rma_v3`)
Assunto: RPEC (e por tabela irmã RCD/RMPE) devolvendo HTTP 500 no runtime
persistente.

## Sintoma

O dono abriu `http://localhost:8095/rmas-relatorios/rpec` e recebeu:

```
Illuminate\Database\QueryException
SQLSTATE[42S02]: Base table or view not found:
Table 'rma_v3.relatorio_informacoes_adicionais' doesn't exist
```

Stack apontando para `RelatorioController::pacoteV1()`, no trecho
`RelatorioInformacaoAdicional::query()->where('codigo', $codigo)->value(...)`.

## Diagnostico (evidencia)

1. A migration existia NO CODIGO:
   `database/migrations/2026_09_10_000002_create_relatorio_informacoes_adicionais_table.php`.
2. `./vendor/bin/sail artisan migrate:status` mostrou:

   ```
   2026_09_10_000001_add_campos_historicos_de_log_to_tentativas_de_acesso_table  Pending
   2026_09_10_000002_create_relatorio_informacoes_adicionais_table               Pending
   ```

3. Logo, nao era schema drift: a tabela `migrations` nao conhecia as duas
   migrations novas. O codigo estava a frente do banco persistente.

## Causa real

O banco persistente do Sail ficou atras do codigo: as duas migrations de
2026-09-10 estavam versionadas no repositorio, mas nunca passaram por
`artisan migrate` naquele banco. Como a tabela de "informacao adicional" faz
parte do schema corrente, qualquer GET de RPEC/RCD/RMPE quebrava ao ler a
informacao adicional.

## Correcao aplicada

```bash
./vendor/bin/sail artisan migrate
```

Sem `migrate:fresh` e sem apagar dado. Resultado: as duas migrations rodaram
(`DONE`), `migrate:status` ficou sem pendencia e a tabela passou a existir.
Smoke HTTP real (login + GET) devolveu 200 em RCD, RPEC e RMPE.

## Prevencao

- Checklist pos-pull/deploy no runbook `docs/desenvolvimento/ambiente-v2-v3.md`:
  depois de atualizar o codigo, rodar `./vendor/bin/sail artisan migrate` antes
  de abrir a aplicacao, e conferir `migrate:status`.
- Check operacional simples: `scripts/qa-smoke-relatorios.sh` valida HTTP 200
  dos relatorios no banco REAL (o que a suite `RefreshDatabase` nao faz).
- Regra: em erro de "table not found", confirmar `artisan migrate:status` antes
  de alterar controller/model.

## O que NAO fazer

- Nao mascarar o bug com `Schema::hasTable(...)` retornando "n/a": isso
  transformaria um erro de deploy explicito em perda silenciosa da informacao
  adicional.
- Nao rodar `migrate:fresh` no banco do dono para "consertar" migration pendente.

## Relacionado

- `docs/operacao/incidentes/2026-09-09-sandbox-bwrap-loopback.md` - a assinatura
  `bwrap: loopback` e conhecida e NAO tem relacao com este incidente (nao foi
  reinvestigada).
