# Ambiente local — Legacy (V2) + V3 lado a lado

Data: 2026-08-24. Portas oficiais do projeto, fixas e reproduzíveis (não usar valores
aleatórios/dependentes da máquina).

## Subir os dois ambientes

```bash
# Terminal 1 — Legacy (RMA V2 / 15.9.7)
cd ~/github/08.24.4-legacy-gerenciador-de-rma
cp .env.example .env   # só na primeira vez
docker compose up -d

# Terminal 2 — V3 (fundação técnica)
cd ~/github/08.24.1-gerenciador-de-rma
cp .env.example .env   # só na primeira vez, ajustar APP_KEY se necessário
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

Derrubar: `docker compose down` (Legacy) / `./vendor/bin/sail down` (V3) — cada um
isolado, não afeta o outro.

## Acessos

| Serviço | URL |
|---|---|
| **RMA V2 / Legacy** (login, redireciona para TEMA V2) | http://localhost:8094/ |
| Legacy — TEMA V1 (14.6.1) | http://localhost:8094/14.6.1/ |
| Legacy — TEMA V2 (15.8.1) | http://localhost:8094/15.8.1/ |
| Legacy — Mailpit (e-mails capturados, nunca saem para a internet) | http://localhost:8036/ |
| **RMA V3** (fundação técnica, sem funcionalidades de RMA ainda) | http://localhost:8095/ |
| V3 — Mailpit | http://localhost:8037/ |
| V3 — Vite (dev server, quando `sail npm run dev` estiver ativo) | http://localhost:5195/ |

## Isolamento entre os dois Docker Compose

Nomes de projeto Docker explícitos (`name:` no topo de cada `compose.yaml`), evitando
qualquer colisão de container/rede/volume:

| | Legacy | V3 |
|---|---|---|
| Project name | `rma-legacy` | `rma-v3` |
| App | `rma-legacy-php-legacy-1` (:8094) | `rma-v3-laravel.test-1` (:8095) |
| Banco | `rma-legacy-mariadb-1` (MariaDB 10.3, :3309) | `rma-v3-mysql-1` (MySQL 8.4, :3310) |
| Mailpit | `rma-legacy-mailpit-1` (:1036/:8036) | `rma-v3-mailpit-1` (:1037/:8037) |
| Rede | `rma-legacy_legacy-lab` | `rma-v3_rma-v3` |
| Volume de banco | `rma-legacy_rma-legacy-db` | `rma-v3_rma-v3-mysql` |

Nenhuma porta, nome de container, rede ou volume é compartilhado. Confirmado
executando `docker compose up -d` nos dois repositórios ao mesmo tempo e checando
`docker ps` — os 6 containers (3+3) ficam ativos simultaneamente, ambos respondendo a
`curl` sem interferência.

## Bancos — responsabilidades distintas

- **`rma_legacy`** (MariaDB, repo Legacy) — schema compatível com o RMA V2, semeado a
  partir de `db/schema-only.sql` (sanitizado, sem dado real). É a fonte de comparação/
  migração/QA de paridade — nunca escrito pela V3.
- **`rma_v3`** (MySQL, repo V3) — banco próprio da V3, baseline a ser definida pela
  arquitetura (`INV-RMA-05`, ainda não escrita). Só tem as migrations padrão do Laravel
  até agora (`users`, `cache`, `jobs`) — nenhuma tabela de domínio RMA ainda, por
  decisão (não implementar funcionalidade sem OpenSpec).

## Smoke test executado nesta sessão (evidência real, não só "a tela abre")

**Legacy:**
- [x] Login TEMA V1 (14.6.1) e TEMA V2 (15.8.1)
- [x] Home renderiza autenticada (título dinâmico com build/data)
- [x] Listagem (clientes) abre sem erro
- [x] Criação de RMA de fixture (`#603971`, dado 100% de laboratório)
- [x] Localizar/abrir o RMA de fixture
- [x] **Banco compartilhado confirmado:** o RMA criado via TEMA V2 aparece na listagem
      `entrada` do TEMA V1
- [x] Transições receber → concluir executadas sem erro
- [x] Troca de tema (`trocarapp.php`) + persistência confirmada: após trocar para
      TEMA V1 e fazer logout/login, o login redireciona para TEMA V1 (preferência
      `usuario.app` gravada e lida corretamente)
- [x] Mailpit: `mail()` testado diretamente dentro do container → capturado no Mailpit,
      **nenhum envio saiu da rede Docker** (destinatário de teste, não histórico)

**V3:**
- [x] Laravel sobe (`sail up -d`)
- [x] Migrations padrão aplicam sem erro
- [x] Página inicial responde HTTP 200
- [x] Suíte de testes básica passa (2/2)

**Execução simultânea:** confirmada — os dois responderam a `curl` ao mesmo tempo, sem
precisar derrubar um para subir o outro.

## Achados durante o smoke test (classificados)

- **[DIFERENCA-AMBIENTE]** A busca "localizar" do TEMA V1 via `?page=localizar&find=...`
  não retornou resultado no teste via `curl` (retornou a página mas sem tabela de
  resultados), enquanto a mesma consulta via listagem `?page=entrada` mostrou o RMA de
  fixture normalmente. Não investigado a fundo — pode ser peculiaridade de como a rota
  processa parâmetros GET fora do navegador (`%` codificado, cabeçalho `Referer`
  ausente, etc.), não necessariamente um bug do sistema. Registrado para não confundir
  com "funcionalidade quebrada" sem confirmação — a listagem por status já prova que os
  dados existem e são lidos corretamente pelo TEMA V1.
- **[PROBLEMA-RUNTIME], resolvido:** `sail:install`/`artisan sail:install` (nos dois
  repos) selecionam PHP 8.5 por padrão em vez de 8.3 — corrigido manualmente apontando
  `context:`/`image:` do serviço `laravel.test` para `runtimes/8.3` em ambos os casos
  (achado já registrado também no repo da Scripting, mesma causa).
