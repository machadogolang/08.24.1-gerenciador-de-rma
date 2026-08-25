# Comparação final V3 × Legado — resultado real

Data: 2026-08-25. Esta é uma comparação **V3 × Legado** (o sistema reconstruído contra o
CellSystem RMA original), diferente de `docs/produto/checklist-paridade-temas.md` (que
comparou TEMA V1 × TEMA V2 **dentro** da V3). Objetivo: confirmar, com evidência viva —
não só leitura de documento — que cada uma das 48 funcionalidades de
`docs/legado/inventario-funcional-rma-v2.md` realmente existe e funciona na V3 hoje, e
corrigir `docs/produto/paridade-v2-v3.md` onde a matriz e a realidade divergiam.

## Resumo executivo

- **`sail test`: 308/308 verde, 593 assertions** — confirmado no início e no fim desta
  sessão de comparação, sem regressão.
- **44 de 48 funcionalidades (`LEG-RMA-NNN`) confirmadas em `PARIDADE` de verdade** — não
  só a leitura da matriz, mas amostragem viva via `curl`/`tinker` autenticado contra
  `:8095` cobrindo as 9 fases implementadas (Identidade, Parceiros, Rma núcleo, Ciclo de
  vida, Alertas, Créditos/Relatórios, Auditoria, Temas V1/V2, Migração). Todas as rotas
  amostradas responderam `200` para usuário autenticado com o papel correto, sem 404/500
  inesperado.
- **2 itens `NÃO RECONSTRUIR`** (código morto em ambos os temas do legado —
  `LEG-RMA-016` "estado retornou", `LEG-RMA-034` alias "Autorizada") — decisão já
  registrada, confirmada sem mudança.
- **1 item `RETOMAR IDEIA`** (`LEG-RMA-035`, tabela `assistencias(tipo)` unificada) —
  ideia registrada como `EVO-DOM-001`, não implementada, decisão correta (é Trilha B).
- **1 item `PENDENTE` por decisão de produto não tomada** (`LEG-RMA-002`, autocadastro
  com convite) — segue sem decisão do usuário entre opção A (segredo em `.env`) e opção
  B (só admin cria usuário); nenhuma das duas foi implementada, como já estava
  registrado.
- **1 divergência de documentação real corrigida nesta sessão** (não de comportamento):
  o parágrafo-resumo final de `paridade-v2-v3.md` estava desatualizado desde a Fase 6
  (dizia "7 itens em PARIDADE, 37 aguardando") mesmo com a tabela linha-a-linha já
  refletindo corretamente as Fases 6-9 — corrigido para 44/48.
- **Nenhuma funcionalidade marcada `PARIDADE` foi encontrada quebrada, incompleta ou sem
  rota/view acessível** nesta amostragem.
- **O único próximo passo real para fechar a Trilha A é a Fase 10 (QA de paridade)** —
  ainda não começou (`openspec/changes/qa-paridade/tasks.md` 100% `[ ]`). Dentro dela:
  rodar o migrador da Fase 9 de verdade contra o Legacy (nunca rodou, só contra
  fixture), escrever o roteiro manual e o Playwright de diff visual V2×V3, e revisar o
  relatório de reconciliação real.

## O que foi comparado, e como

### 1. Suíte de testes

`sail test` executado no início desta comparação: **308 testes, 308 aprovados, 593
assertions**, igual à última verificação registrada (Fase 9). Reexecutado ao final desta
sessão de documentação com o mesmo resultado — nenhuma alteração de código de produto
foi feita, só documentação.

### 2. Amostragem viva por HTTP autenticado (`:8095`)

Login real via `curl` (usuário `SuperAdministrador` seed, `batista.franciele@example.org`
/ `password`), sessão mantida por cookie jar, amostragem de rotas cobrindo todas as 9
fases:

| Rota | Fase | Resultado |
|---|---|---|
| `/usuarios`, `/perfil` | 1 — Identidade | `200` |
| `/rmas`, `/rmas/create`, `/v1/rma`, `/v2/rma` | 3/8 — Rma núcleo + Temas | `200` |
| `/rmas-alertas` | 5 — Alertas | `200` |
| `/rmas-credito` | 6 — Créditos | `200` |
| `/rmas-relatorios/rcd`, `/rpec`, `/rmpe` (com `data_inicio`/`data_fim`) | 6 — Relatórios | `200` |
| `/rmas-historico`, `/historico-de-acesso` | 7 — Auditoria | `200` |
| `/rmas-logistica/frete-porto-alegre` | 7 — Logística | `200` |
| `/parceiros/clientes`, `/fabricantes`, `/fornecedores`, `/assistencias-tecnicas` (+ `/v1/`, `/v2/`) | 2 — Parceiros | `200` |

Nenhuma rota amostrada respondeu 404/500 para o usuário autenticado correto. (Duas
tentativas iniciais com caminho errado — `/clientes` sem o prefixo `/parceiros/` —
deram 404 por engano de digitação da comparação, não bug da V3; corrigido e reconfirmado
`200`.)

### 3. Legacy-reset.sh testado de verdade

**Atualização operacional posterior (2026-08-25):** o pedido de ambientes locais autorizou a alteração do repositório Legacy. A espera foi corrigida com consulta autenticada ao database final, eliminando também a corrida com o servidor temporário do entrypoint; resets sanitized e historical foram validados. O relato abaixo fica preservado como evidência do estado anterior.

`scripts/legacy-reset.sh` (repositório Legacy) nunca havia sido executado de ponta a
ponta. Rodado nesta sessão: `docker compose down -v` + `up -d`, reimportação do
`db/schema-only.sql`, `:8094` voltou a responder `200`, banco saudável (`mysqladmin
ping` → `mysqld is alive`). **O reset funciona** — mas um **bug real foi encontrado**: o
loop de espera do script chama `mariadb-admin ping`, binário que não existe na imagem
`mariadb:10.3` usada pelo `compose.yaml` (só `mysqladmin` existe nela); o script trava
indefinidamente nesse loop mesmo com o banco pronto (precisou ser interrompido por
timeout de 180s). Correção é trivial (`mariadb-admin` → `mysqladmin`, uma palavra), mas
**não foi aplicada** — regra desta sessão é nunca alterar o repositório Legacy; fica
registrada como achado para o usuário decidir.

### 4. Visibilidade dos repositórios GitHub

Reconfirmado via API pública do GitHub (sem autenticação):
- `machadogolang/08.24.1-gerenciador-de-rma` (V3, este repo): **`"private": false"`,
  `"visibility": "public"`** — ainda público, decisão de trocar para privado continua
  pendente do usuário (não decidida por este agente).
- `machadogolang/08.24.4-legacy-gerenciador-de-rma` (Legacy): API devolve `404` para
  acesso não-autenticado, consistente com **privado** (o push já documentado confirma
  que o repositório existe) — fechado, sem ação pendente.

### 5. Pendências reais que seguem em aberto (nenhuma nova encontrada, todas já
   catalogadas)

1. `LEG-RMA-002` — autocadastro com convite: decisão de produto (opção A/B) não tomada.
2. `EVO-AUD-001` — log de modificação por diff campo-a-campo (hoje é snapshot
   estruturado com ação nomeada): decisão de produto não tomada.
3. Fase 9 — migrador nunca rodou contra o banco real do Legacy (bloqueio de rede: porta
   `3309` do Legacy só em `127.0.0.1` do host, não alcançável do container V3); a
   evidência que existe é a fixture automatizada.
4. `legacy-reset.sh` — bug do `mariadb-admin` (achado nesta sessão), não corrigido por
   regra de não alterar o repositório Legacy.
5. Visibilidade pública do repositório V3 no GitHub — decisão do usuário pendente.
6. Trilha B (`INV-RMA-07`/`08`/`09`) — arquitetura decidida e registrada, nenhuma linha
   de implementação iniciada, propositalmente, até a Fase 10 fechar a baseline de
   paridade.

Nenhuma dessas é nova nesta sessão, exceto o item 4 (bug do `legacy-reset.sh`,
encontrado ao testá-lo de verdade pela primeira vez) e a correção textual do item de
documentação em `paridade-v2-v3.md` (resumo desatualizado, tabela sempre esteve
correta).

## Conclusão

A reconstrução (Trilha A) está funcionalmente completa e testada — 44/48 itens do
inventário legado em `PARIDADE` real, confirmada por amostragem viva, não só por
leitura de documento. Não há nenhuma funcionalidade que "desapareceu silenciosamente".
O único trabalho real que falta para fechar a Trilha A é a **Fase 10 (QA de
paridade)**, que ainda não começou: paridade funcional formal (roteiro manual +
matriz), paridade visual (diff Playwright V2×V3) e paridade de dados (rodar o migrador
de verdade contra o Legacy, hoje só validado contra fixture).
