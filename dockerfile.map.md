# Mapa de portas Docker — todos os projetos

Ultima atualizacao: 2026-08-26 (America/Sao_Paulo)

Status: **PENDENTE — LEVANTAMENTO FEITO, RECONFIGURAÇÃO AINDA NÃO APLICADA
EM NENHUM PROJETO.** Este arquivo existe em vários repositórios ao mesmo
tempo (uma cópia idêntica em cada um) para que, ao abrir qualquer projeto,
já apareça o mapa completo de todos os outros — não só o dele.

Motivo: portas duplicadas entre projetos diferentes, descobertas ao
inspecionar os containers reais rodando (`docker ps -a` + `docker inspect`
em 2026-08-26) e os arquivos de compose de cada projeto. Regra combinada com
o usuário: a partir de `8090`, sequencial, um número por projeto/serviço
web principal — `8090` é fixo (`scripting-site`, já está correto hoje), o
resto sobe a partir de `8091`.

## Conflitos reais já confirmados hoje (evidência, não suposição)

- **Porta 8080** definida como padrão em **dois** projetos:
  `nao-sou-xiter` (rodando de verdade nela hoje) e `online-sistema-hrbio-app`
  (`.env`: `PORT=8080`, projeto parado no momento do levantamento).
- **Porta 3309** definida como padrão em **dois** projetos:
  `rma-legacy` (rodando de verdade nela hoje, `mariadb`) e
  `online-sistema-homeopatia-novo-app` (`.env`: `DB_PORT=3309`).
- **Porta 3000** definida como padrão em **dois** projetos:
  `thechinesedragon` (`truco_web`, rodando hoje) e `local-llama-chat`
  (`3000:8080`, parado no momento do levantamento).
- **`online-sistema-homeopatia-novo-app` já foi corrigido (2026-08-26)** —
  antes rodava com portas diferentes do que o próprio `.env` dizia (`.env`
  cravava `8080`/`3309`, alguém precisava sobrescrever na hora de subir por
  colidir com `nao-sou-xiter`/`rma-legacy`; o container real ficava em
  `8099`/`3319`, e o `.htaccess` só libera acesso HTTP direto na porta
  exata `8080`, hardcoded — daí o erro de SSL ao abrir `localhost:8099` no
  navegador). Corrigido: `.env` (`HTTP_PORT=8092`, `DB_PORT=3392`) e
  `.htaccess` (a condição de exceção do redirect, antes hardcoded em
  `8080`, agora em `8092`) — containers reiniciados, `http://localhost:8092/`
  confirmado funcionando. **Mudança feita no working tree, ainda não
  commitada** — revisar e commitar quando for conveniente.

## Mapa alvo — porta web principal, sequencial a partir de 8090

| Porta alvo | Projeto | Caminho | Porta atual (real) | Onde mudar |
| --- | --- | --- | --- | --- |
| **8090** | scripting-site | `~/github/08.24.3-scripting-site` | 8090 (já correto) | `compose.yaml` já usa `${APP_PORT}` — nenhuma mudança necessária |
| **8091** | nao-sou-xiter | `~/github/nao-sou-xiter` | 8080 | `.env` (variável de porta do Sail, conferir nome exato na hora) |
| **8092** | online-sistema-homeopatia-novo-app | `~/bitbuckets/online-sistema-homeopatia-novo-app` | **8092 — JÁ APLICADO em 2026-08-26** | Feito: `.env` (`HTTP_PORT=8092`, `DB_PORT=3392`) e `.htaccess` (condição de exceção do redirect HTTPS atualizada de `8080` para `8092`) — containers reiniciados e confirmados (`http://localhost:8092/`, `/Adm` respondendo). **Ainda não commitado** — ver nota abaixo. |
| **8093** | online-sistema-hrbio-app | `~/bitbuckets/3/online-sistema-hrbio-app` | 8080 (`.env`, projeto parado) | `.env`: `PORT=8093` |
| **8094** | rma-legacy | `~/github/08.24.4-legacy-gerenciador-de-rma` | 8094 (já correto) | nenhuma mudança necessária |
| **8095** | rma-v3 | `~/github/08.24.1-gerenciador-de-rma` | 8095 (já correto) | nenhuma mudança necessária |
| **8096** | online-conahom-laravel | `~/github/online-conahom-laravel` | 8001 (e também expõe `80` direto — conferir na hora) | variável de porta do Sail no `.env` |
| **8097** | legal | `~/gitlab-ce/legal` | 5100 | `docker-compose.yml` (porta hoje hardcoded, conferir se dá para virar variável) |
| **8098** | manager (web) | `~/gitlab-ce/manager` | 4001 | `docker-compose.yml`/`.env` do manager |
| **8099** | manager (api) | `~/gitlab-ce/manager` | 4000 | `docker-compose.yml`/`.env` do manager |
| **8100** | thechinesedragon — ryudragon (app) | `~/github/thechinesedragon` | 8081 | `.env`: `APP_PORT=8100` (já é variável — mudança simples) |
| **8101** | thechinesedragon — truco (web) | `~/github/thechinesedragon` | 3000 | `.env`: `TRUCO_WEB_PORT=8101` (já é variável) |
| **8102** | local-llama-chat | `~/github/local-llama-chat` | 3000 (mapeia para `8080` interno) | `docker-compose.yml` (porta hoje hardcoded) |

Projetos com `docker-compose*.yml` encontrados mas **não avaliados a fundo
ainda** (podem não ser ambientes de desenvolvimento ativos — conferir antes
de incluir na sequência): `~/gitlab-ce/billing-azure`, `~/gitlab-ce/billing`,
`~/gitlab-ce/financial`, e as cópias em `~/gitlab-ce-old/*` (parecem
arquivadas/duplicadas do `gitlab-ce` atual — confirmar antes de mexer).

## Outras portas com colisão (fora da sequência 809x, resolver junto)

- `DB_PORT` de `homeopatia` (`3309`) colide com `rma-legacy` — sugestão:
  `homeopatia` passa a usar `3392` (ou qualquer porta livre de banco fora da
  faixa já ocupada por `rma-legacy` 3309, `rma-v3` 3310, `nao-sou-xiter`
  3307, `online-conahom-laravel` 3306) — decidir número exato na hora da
  reconfiguração, só reservando que não pode ser 3309.
- `PORT` de `local-llama-chat` (host `3000`) colide com `truco_web` — resolvida
  automaticamente se `truco_web` migrar para `8101` (ver tabela acima); depois
  disso `local-llama-chat` pode continuar em `3000` sem conflito, **ou**
  entrar também na sequência 809x (`8102`, já reservado acima) se o usuário
  preferir consistência total.

## Como aplicar (quando tivermos os tokens/tempo reservados para isso)

1. Entrar em cada projeto, um por vez, seguindo a ordem da tabela.
2. Conferir o nome real da variável de porta no `.env`/`.env.example` daquele
   projeto (nem todos usam o mesmo nome — `HTTP_PORT`, `PORT`, `APP_PORT`
   já apareceram três variações diferentes nesta varredura).
3. Trocar o valor para a porta alvo da tabela.
4. Subir o container daquele projeto sozinho e confirmar que respondeu na
   porta nova antes de passar para o próximo.
5. Atualizar a linha correspondente desta tabela (em **todas** as cópias
   deste arquivo, em todos os projetos) marcando "já aplicado".
6. Nenhum projeto deve ser reconfigurado sem primeiro conferir
   `docker ps -a` de novo — outros containers podem já ter sido religados
   entre o levantamento e a execução.

## Onde este arquivo vive

Cópia idêntica deste mapa, com o nome `dockerfile.map.md`, na raiz de cada
um dos projetos listados na tabela (pedido do usuário: manter o mapa
acessível diretamente no repositório de cada projeto, não só na memória do
Claude Code, para que abrir qualquer um deles já mostre o quadro completo).
