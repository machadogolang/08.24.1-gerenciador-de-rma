# Ambiente executável do RMA V2 (LEGACY-RUNTIME) — design

Data: 2026-08-24. Objetivo: rodar o CellSystem RMA V2/15.9.7 original lado a lado com a
V3, para ter uma **especificação visual e funcional viva**, não só leitura estática de
código. Design registrado aqui; bring-up efetivo é a próxima ação (ver
`PLANO-ATAQUE.md` — ainda **não testado/validado** nesta sessão).

## Localização (fora do Git da V3)

```
~/github/
├── 08.24.1-gerenciador-de-rma/         # V3 (este repo)
└── _rma-arqueologia/
    ├── github-14.10.2/                  # já existente
    ├── github-15.10.1/                  # já existente
    ├── backup-15.9.7/
    │   ├── original/                    # .tar.gz intocado (reorganizado nesta sessão)
    │   └── extracted/                   # código-fonte extraído, intocado
    └── legacy-runtime/                  # NOVO — ambiente Docker de laboratório
        ├── compose.yaml
        ├── php-legacy/Dockerfile
        ├── db/dump-schema-only.sql      # gerado a partir do dump de dez/2019, sem dado real
        ├── db/dump-com-dados-1maio2019.sql  # cópia do dump com dados reais — só para quem
        │                                     precisar de paridade de dado real, uso cauteloso
        └── scripts/
            ├── reset-legacy.sh
            └── migrate-v3.sh            # placeholder — implementação real é MIG-V3
```

`legacy-runtime/` **nunca** entra no Git da V3 (nem em `.gitignore`-tracked — vive fora
do diretório do repositório). O código-fonte legado usado pelo container é montado
read-only a partir de `backup-15.9.7/extracted/`.

## Requisitos de runtime confirmados (verificação estática, sem executar código)

- **PHP:** `mysqli` (prepared statements) em todo o código — **nenhum** uso de
  `mysql_connect`/`mysql_query` (removidos no PHP 7), `create_function()`, `mcrypt_*`,
  `ereg()`/`split()` (todos removidos no PHP 7) encontrado. Isso torna **PHP 7.4**
  (última 7.x, compatível com a era do backup — dez/2019) a escolha mais segura para o
  container: moderna o suficiente para não faltar extensão, antiga o suficiente para não
  quebrar em nada que dependa de comportamento pré-8.0 (comparação solta `==`,
  `each()` — não encontrado, mas PHP 7.4 ainda suporta legado que o 8.x removeu).
- **Extensões PHP necessárias:** `mysqli`, `session` (nativa), `mbstring` (charset
  ISO-8859-1 é setado via `ini_set`, mas processamento de string deve funcionar sem
  mbstring — confirmar no bring-up).
- **Servidor web:** Apache com `mod_rewrite` — confirmado pelo `.htaccess` de `15.8.1`
  (rotas amigáveis tipo `^rma/novo/?$`) e pelo `app/htaccess` do nível pai.
- **Banco:** MariaDB, versão próxima de `10.3.14` (a do cabeçalho do dump) — usar
  `mariadb:10.3` como imagem, mais fiel que MySQL genérico.
- **Charset:** `ini_set('default_charset','ISO 8859-1')` (nome com espaço, tecnicamente
  inválido — canônico é `ISO-8859-1`) — **não corrigir no código-fonte** (seria alterar a
  fonte histórica); se causar problema real de renderização no navegador, ajustar via
  header HTTP no nível do container/vhost, documentando como "adaptação de laboratório",
  nunca como edição do PHP legado.

## Separação `legacy-original` vs `legacy-runtime` (adaptação mínima documentada)

- **`backup-15.9.7/extracted/`** = fonte histórica imutável (mesmo papel de
  "legacy-original" pedido) — nunca editada.
- **Montagem no container** = read-only bind mount dessa mesma pasta. Se alguma
  adaptação de ambiente for estritamente necessária para rodar (ex.: criar um
  `conexao.php` de laboratório com credencial local, já que o original tem credencial de
  produção real que não deve ser usada), ela acontece **fora** da pasta extraída — via
  override de arquivo específico no container (ex.: volume adicional só para
  `conexao.php`), nunca editando o arquivo dentro de `extracted/`.

Toda adaptação será registrada nesta tabela conforme for necessária (vazia até o
bring-up real acontecer):

| Legado original | Adaptação de laboratório | Motivo |
|---|---|---|
| *(preencher durante o bring-up)* | | |

## Isolamento e segurança (requisitos obrigatórios)

- Bind só em `127.0.0.1`, nunca `0.0.0.0` nem exposto fora da máquina local.
- Rede Docker dedicada e isolada (`legacy-lab`), sem acesso à internet configurado por
  padrão para o container PHP legado (evita qualquer chamada externa inadvertida).
- `conexao.php` de laboratório usa credencial local própria, nunca a credencial real
  encontrada no backup (que não é reproduzida em lugar nenhum, inclusive aqui).
- **Neutralização de e-mail:** o legado chama `mail()` nativo em vários pontos
  (`naopermitido()`, `ezequiel()`, `enviar_senha()`, `enviar_saudacao()`) com
  destinatários hardcoded reais. O container PHP aponta `sendmail_path`/SMTP para um
  serviço **Mailpit** dentro da mesma rede isolada — nenhum e-mail real sai do
  ambiente. Isso é uma configuração do container (`php.ini` de laboratório), não uma
  edição do código-fonte legado.
- Banco `rma_legacy` **não** compartilha schema com o banco `rma_v3` da V3 — bancos
  totalmente separados, nascendo os dois do mesmo dump histórico (um importado
  diretamente, o outro passando pelo migrador V2→V3).

## Reset determinístico (proposto, scripts ainda não escritos/testados)

- `scripts/reset-legacy.sh`: derruba e recria o container/volume do `rma_legacy`,
  reimporta o dump (por padrão, o de dezembro/2019 — quase sem dado real, mais seguro
  como default; o dump de maio/2019 com dados reais completos fica disponível para quem
  precisar de paridade de dado real para QA, uso consciente).
- `scripts/migrate-v3.sh`: parte do `rma_legacy` conhecido, recria `rma_v3` do zero,
  executa o migrador oficial da V3 (ver `INV-RMA-00-arqueologia-cellsystem-15.9.7.md`
  §10/§14 para a regra de evolução do banco), gera relatório de reconciliação.

## Status — LEGACY-RUNTIME FUNCIONAL (2026-08-24)

**[CONFIRMADO]** Ambiente no ar e validado: `docker compose up -d` em
`_rma-arqueologia/backup-15.9.7/legacy-runtime/` sobe `php-legacy` (PHP 7.4 +
Apache/mod_rewrite), `mariadb` (10.3, banco `rma_legacy`) e `mailpit`, todos em
`127.0.0.1`. Login testado de ponta a ponta com usuário de laboratório
(`lab@localhost`, criado só no `schema-only.sql`, senha `rma-lab-2026` — não é
credencial histórica):

- **TEMA V2 (15.8.1):** `http://localhost:8094/` (login) → POST autenticado em
  `15.8.1/pp/senha.php` → redirect 302 → `http://localhost:8094/15.8.1/` renderiza
  dashboard autenticado, título dinâmico `RMA 15.8.1  Build: 2.5 | Data de hoje:
  <data corrente>`. Confirma o `$build="2.5"` já achado no código. Sem erro/warning PHP
  nos logs do Apache.
- **TEMA V1 (14.6.1):** `http://localhost:8094/14.6.1/` responde 200, título
  `Intranet : FIR 1.3 - <data corrente>`. **Achado novo:** o codinome interno do TEMA V1
  é **"FIR"** (não visto em nenhuma leitura de código anterior — aparece só na string de
  título montada em runtime). `$version="1.3"` confirmado batendo com o já encontrado em
  `14.6.1/config.php`.

**Adaptações de laboratório efetivamente usadas** (nenhuma edição da fonte em
`extracted/`, tudo via bind mount de arquivo único ou config de container):

| Legado original | Adaptação de laboratório | Motivo |
|---|---|---|
| `conexao.php` (credencial de produção real) | `overrides/conexao.php` com host `mariadb`, usuário/senha só de laboratório | nunca usar credencial real |
| `config.php` (raiz, `$local` apontando pra `cellsystem.com.br`) | `overrides/config-root.php` com `$local="http://localhost:8094/"` | permitir que assets/links funcionem localmente |
| `15.8.1/config.php` (`$caminho`/`$local` de produção) | `overrides/config-15.8.1.php` | idem |
| `14.6.1/config.php` (`$pedecabra` = segredo histórico de convite) | `overrides/config-14.6.1.php` com **hash novo**, gerado só para o laboratório (`sha1("rma-lab-convite-2026")`) — nunca o valor histórico | evita reproduzir credencial encontrada no backup, mesmo operacionalmente |
| `mail()` nativo (destinatários hardcoded reais) | `sendmail_path` do container relayado via `msmtp` para o Mailpit (`legacy-runtime/php-legacy/Dockerfile`) | nenhum e-mail real sai do ambiente — ainda **não testado** um envio de fato (próximo passo) |

**Pendente:** disparar uma ação que envie e-mail (ex.: concluir um RMA) e confirmar que
chega no Mailpit (`http://localhost:8036`), não em lugar nenhum real. Capturar evidência
visual (screenshots) dos dois temas autenticados para `inventario-visual-tema-v1.md` e
`inventario-visual-tema-v2.md`.
