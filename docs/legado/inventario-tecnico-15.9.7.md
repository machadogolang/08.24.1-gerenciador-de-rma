# Inventário técnico — backup CellSystem RMA 15.9.7

Data: 2026-08-24.

## Localização e integridade

- **Original:** `/home/legionario/Downloads/16 de Dezembro de 2019 - Sistema de RMA CELLSYSTEM LTDA.tar.gz` (intocado).
- **Cópia de trabalho:** `~/github/_rma-arqueologia/backup-15.9.7/` (fora de qualquer
  repositório Git).
- **SHA-256 (confirmado idêntico nas duas cópias):**
  `d3811daa79087e04927613505069a7a81221691d93cca9ee37b7f0096ba354df`
- **Tamanho:** 32 MB, gzip, timestamp interno `16/12/2019 14:16:48`.
- Inventariado via `tar -tzf` antes de qualquer extração; extraído em subdiretório
  próprio (`extracted/`), nunca executado.

## Estrutura interna

```
16 de Dezembro de 2019 - Sistema de RMA CELLSYSTEM LTDA/
├── app/
│   ├── index.php, index.htm, htaccess       (raiz antiga, pré-container 15.9.7)
│   ├── 1maiode2019.sql, 2maiode2019.sql      (dumps intermediários, com dados reais)
│   ├── 15.9.7.tar.gz                         (cópia aninhada do código, redundante)
│   └── 15.9.7/                               (container da aplicação)
│       ├── index.php, config.php, conexao.php, metodo.php, trocarapp.php
│       ├── pattern/         (14.6.1.css/js, 15.8.1.css/js, 15.9.7.css/js)
│       ├── css/, images/, js/, inc/          (assets do shell de login)
│       ├── framework/, lib/                  (AdminLTE 2.2.0 duplicado em dois lugares,
│       │                                       Bootstrap, Foundation, jQuery Mobile)
│       ├── 14.6.1/                           (app completo, 2 camadas)
│       └── 15.8.1/                           (app completo, 4 camadas)
└── dump-cellsyst_rma-201912161213.sql        (dump final, mesmo timestamp do backup)
```

Total: 1.147 entradas no tar. `app/15.9.7/framework/` e `app/15.9.7/lib/` contêm cópias
idênticas de `LTE2.2.0` (AdminLTE) — duplicação de asset, não investigada a fundo.

## Tecnologias catalogadas

| Categoria | Item | Onde | Classificação preliminar (ver parecer) |
|---|---|---|---|
| Backend | PHP procedural, `mysqli` com prepared statements na maior parte (não em todos os pontos — ver `regras-negocio` para SQLi confirmada) | todo o código | reimplementar em Laravel |
| Banco | MySQL/MariaDB `10.3.14-MariaDB-cll-lve` | cabeçalho dos dumps | migrar schema, não copiar 1:1 |
| Admin UI framework | AdminLTE 2.2.0 | `framework/LTE2.2.0`, `lib/LTE2.2.0` | avaliar: comportamento/aparência a preservar, biblioteca em si pode ser substituída (ver INV-RMA-04, ainda não escrito) |
| CSS grid/reset | Bootstrap 3.3.5 (via CDN, `maxcdn.bootstrapcdn.com`), Foundation | `15.9.7/index.php`, `framework/foundation/` | Bootstrap 3.3.5 → substituir por Bootstrap 5.3 (comportamento equivalente, não bit a bit) |
| JS | jQuery 1.11.x/2.1.x (múltiplas versões coexistindo), jQuery Mobile, iCheck (checkboxes customizados) | `js/`, `framework/jQuery.mobile/`, `lib/LTE2.2.0/plugins/iCheck/` | avaliar caso a caso (ver seção seguinte) |
| Galeria/lightbox | Lightbox2 | `css/lightbox.css`, `js/lightbox.js` | avaliar necessidade real de uso no app (não confirmado onde é usado) |
| Fontes | Open Sans, Roboto, Fira Mono (parcial), Font Awesome | `framework/fonts/`, `css/font-awesome.min.css`, `css/font-opensans.css` | preservar via Google Fonts/self-host moderno |
| E-mail | `mail()` nativo do PHP, sem SMTP configurado | `metodo.php` (`naopermitido()`, `ezequiel()`), `banco.php` (`enviar_senha()`, `enviar_saudacao()`) | reimplementar com Mailable do Laravel |
| Sessão | `session_start()` nativo, chaves customizadas por app (`START1597` no 15.8.1) | múltiplos arquivos | usar sessão padrão do Laravel |

## Distinção A/B/C/D/E de bibliotecas (pedida explicitamente pelo usuário)

Ainda **preliminar** — falta ler CSS/JS de `js/`, `framework/`, `lib/` em detalhe para
fechar esta tabela. Primeira leitura:

| Biblioteca | Categoria | Nota |
|---|---|---|
| AdminLTE 2.2.0 | **B/C** — asset visual reutilizável como referência, mas versão 2.2.0 é antiga (2016) e sem manutenção; comportamento (dashboard admin com sidebar) pode ser recriado com Bootstrap 5.3 puro | Confirmar se skin realmente ativa (nenhuma classe `skin-*` foi encontrada referenciada no HTML lido até agora) |
| Bootstrap 3.3.5 (CDN) | **C** — versão antiga, sem motivo para manter; grid/componentes equivalentes existem em 5.3 | — |
| jQuery 1.11/2.1 | **C/D** — comportamento (manipulação DOM, AJAX) replicável com JS moderno; não há evidência até agora de uso de plugin jQuery insubstituível além de iCheck/Lightbox | — |
| iCheck | **C** — só estiliza checkbox/radio; CSS moderno resolve sem JS | — |
| Lightbox2 | **[DÚVIDA]** — não confirmado onde é usado de fato no fluxo de RMA (pode ser resquício do template AdminLTE, não uso real do app) | Precisa verificação antes de decidir |
| Fontes (Open Sans/Roboto) | **B** — preservar como parte da identidade visual (ver `matriz-comparacao-apps-rma.md`, paleta do app 15.8.1 usa essas fontes) | — |
| Font Awesome | **D** — ainda amplamente usado e mantido, ok continuar ou trocar por SVG inline moderno | — |

## Banco de dados / dumps

Três dumps, mesmo schema (9 tabelas idênticas): `app/1maiode2019.sql`,
`app/2maiode2019.sql` (ambos com dados reais — ~1.332 RMAs, ~165 clientes, ~2.777 logs,
não abertos além de schema+contagem), `dump-cellsyst_rma-201912161213.sql` (schema
idêntico, praticamente sem dados de exemplo — usado como fonte do schema documentado).

Tabelas: `assistencia_tecnica`, `assistencias` (só referenciada pelo app 14.6.1, ver
`modelo-dominio-rma-legado.md`), `bd`, `cliente`, `fabricante`, `fornecedor`, `log`,
`modificacao`, `relatorio`, `usuario`. Schema completo por tabela em
`modelo-dominio-rma-legado.md`.

**Anomalia técnica registrada, não é regra de negócio:** `bd` tem
`AUTO_INCREMENT=2147483648` (exatamente 2^31) já no dump de maio/2019 — contador de
auto-incremento corrompido/resetado para o limite de `int` assinado. Irrelevante para o
sistema novo porque `numero` é gerado em PHP, não pelo MySQL.

## Uploads, relatórios, documentos

Nenhum diretório de upload de anexo (foto de equipamento, PDF de NF) encontrado em
nenhum dos dois apps — os campos de NF armazenam só texto (número/chave), não arquivo.
Relatórios geram HTML para impressão via navegador (sem PDF server-side).

## Código duplicado / morto (catálogo)

| Item | Tipo | Situação |
|---|---|---|
| `framework/LTE2.2.0` vs `lib/LTE2.2.0` | Duplicação de asset completo | Não investigado a fundo — provavelmente resquício de reorganização de pastas |
| `subp/*_autorizada*` (4 arquivos) vs `subp/*_assistencia_tecnica*` | Código quase idêntico, sem rota | MORTO (ver `regras-negocio-rma-legado.md` RN-19) |
| `subp/pesquisar_{rma,nf,sn,descricao}.php` (4 arquivos) | Mesmo MD5, mesma função `pesquisar()` chamada | UI oferece como buscas distintas, mas são idênticas |
| `banco.php:novo()` vs `metodo.php:novo_bd()` | Duas funções para o mesmo INSERT | `novo()` tem SQL inválido (`INSERT numero SET`, sem `INTO`) e não é chamada — morta |
| 16 arquivos com 0 bytes no app 15.8.1 | Placeholders nunca implementados | Listados no relatório do agente de arqueologia; incluir na consolidação de `INV-RMA-00` |

## Arquivos potencialmente sensíveis (sem reprodução de valor)

1. `app/15.9.7/conexao.php` — credencial de banco em texto plano.
2. `app/15.9.7/14.6.1/config.php` — segredo fixo de convite de autocadastro (hash SHA1).
3. `app/1maiode2019.sql`, `app/2maiode2019.sql` — dados reais de clientes/fabricantes/
   fornecedores e ~2.777 registros de log de acesso (e-mail, IP, navegador).
4. `metodo.php`, `15.8.1/banco.php` — e-mails hardcoded de pessoas reais como
   destinatários fixos de notificação.
5. Tabela `usuario` (nos três dumps) — hashes SHA1 sem salt de senha (`Key1461`,
   `Key1581`).

## Cobertura desta arqueologia (transparência)

**Lido integralmente:** `metodo.php`, `15.8.1/banco.php` (1812 linhas), `15.8.1/page/
rma.php` (759 linhas), `15.8.1/pp/{salvar_rma,novo_rma}.php`, os 10 `subp/listar_*.php`,
`14.6.1/banco.oo.php`, boa parte de `14.6.1/page/*` e `post/*`, `14.10.2` inteiro (28
arquivos), `.htaccess` do 15.8.1, `config.php`/`index.php`/`trocarapp.php` do container
15.9.7, os 6 arquivos `pattern/*.css/js`.

**Não lido ainda:** a maior parte de `js/`, `framework/`, `lib/` (bibliotecas de
terceiros, baixa prioridade); vários `subp/apagar_*.php`/`pp/editar_*.php` do 15.8.1 (não
verificados quanto a guarda de nível de permissão); `14.6.1/inc/*` restantes; qualquer
comparação linha a linha entre `14.6.1/post/novo.php` e `15.8.1/pp/novo_rma.php` para
resolver as dúvidas marcadas em `regras-negocio-rma-legado.md` (RN-12 a RN-17, RN-18,
RN-21).
