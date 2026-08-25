# Ambientes locais V2/V3

Data da validação: 2026-08-25.

## Legacy / RMA V2 — máquina do tempo

- Repositório: `~/github/08.24.4-legacy-gerenciador-de-rma`
- URLs: `http://localhost:8094/`, `/14.6.1/` (TEMA V1) e `/15.8.1/` (TEMA V2)
- Banco: MariaDB 10.3 em `127.0.0.1:3309`, projeto Compose `rma-legacy`
- Login local: `lab@localhost` / `rma-lab-2026`

Sanitized: use `LEGACY_DB_MODE=sanitized` e `./scripts/legacy-reset.sh`. Historical:
configure no `.env` o caminho absoluto externo e o SHA-256, selecione
`LEGACY_DB_MODE=historical` e rode `./scripts/legacy-restore-historical.sh`. O dump
nunca entra no repositório; a conta lab é injetada somente na cópia local. O snapshot
selecionado contém 10 tabelas, 1.379 RMAs e 165 clientes.

## RMA V3 — produto em construção

- Repositório: `~/github/08.24.1-gerenciador-de-rma`
- URL: `http://localhost:8095/login`
- Banco: MySQL 8.4 em `127.0.0.1:3310`
- Mailpit: `127.0.0.1:8037`; o Legacy usa `8036`

Execute `./scripts/v3-reset-qa.sh`. O script exige `APP_ENV=local`, roda
`migrate:fresh --seed` e cria 5 usuários, 30 clientes, 10 fabricantes, 10
fornecedores, 5 assistências e 60 RMAs fictícios. Os logins `operador`, `supervisor`
e `superadministrador` usam `@rma.local` / `password`; `bloqueado@rma.local`
comprova a negação esperada.

## Isolamento confirmado

Os projetos têm nomes Compose, redes, volumes, portas de aplicação, bancos e Mailpit
distintos. `down -v` no Legacy limita-se a `rma-legacy`; o reset V3 usa somente
`rma_v3`. Ambos funcionam simultaneamente.
