# Handoff de sessão — CellSystem RMA V3

Data de encerramento: 2026-08-26. Substitui `docs/produto/handoff-sessao-2026-08-25.md`
como ponto de partida de uma nova sessão (aquele documento fica só como histórico —
os achados `VIS-V1-*` que ele registrava foram todos absorvidos/reescritos pela reabertura
em fases estruturadas descrita abaixo). Fonte de status sempre atualizada:
`PLANO-ATAQUE.md` (raiz do repo) — este handoff só acrescenta o contexto narrativo que o
plano de ataque não carrega.

## Estado geral (as 3 frentes ativas nesta sessão)

1. **Paridade estrutural do Tema V1, fase 1** (`docs/produto/plano-execucao-paridade-estrutural-v1.md`,
   CP0–CP5) — **fechada**, sessões anteriores.
2. **Paridade visual do Tema V2** (`docs/produto/plano-execucao-paridade-v2.md`,
   CP16–CP25) — **fechada nesta sessão**, gate final aprovado (`CMP-V2-008`). Shell,
   header/nav, cascata, sidebar de 14 seções, Início/Pesquisar, Centro de Avisos, as 4
   tabelas por status e o rodapé todos com paridade pixel-a-pixel medida (delta 0) em
   4 viewports. Pendências explícitas, não bloqueantes: zebra fina de Entrada/Recebido/
   Encaminhado (`[INVESTIGAR]`), gap "Anotacoes" sem página própria (`[GAP]`), redesenho
   por-grupo do Centro de Avisos (fora de escopo desta frente).
3. **Paridade visual do Tema V1, fase 2** (`docs/produto/plano-execucao-paridade-visual-v1-fase2.md`,
   CP6–CP15) — **em execução**, CP6–CP14 fechados nesta sessão. **CP15 (gate final)
   é o próximo passo, mas há uma decisão do usuário pendente antes (ver seção
   "Decisão pendente" abaixo) — CP15 pode rodar sem ela (documentando o pendente),
   mas o resultado final muda dependendo da escolha.**

## O que foi feito nesta sessão, em ordem

- **CP25 (V2)** — corrigido o script de medição do gate final (precisava clicar a aba
  antes de medir `.Tabelinha-Table`, painéis Bootstrap só renderizam com `.active`).
  Confirmado pixel-perfeito nas 4 viewports. Achado de infraestrutura (não é bug de
  código): o container Legacy publica a porta 8094 só em `127.0.0.1`, inacessível de
  dentro de outro container via `host.docker.internal` — `ParidadeVisualTemaV1.spec.ts`
  precisa rodar do HOST (`npx playwright test` direto), não via
  `docker compose exec laravel.test`. **Isso vale pro resto desta sessão e pra próxima**:
  toda vez que for rodar esse spec específico, use:
  ```
  PLAYWRIGHT_BASE_URL=http://localhost:8095 LEGACY_BASE_URL=http://localhost:8094/14.6.1/ \
    npx playwright test tests/Browser/ParidadeVisualTemaV1.spec.ts --project=chromium --reporter=line
  ```
  Duas screenshots de Legacy com dado real de cliente foram descobertas na hora de
  fechar o CP25 e movidas pra um diretório gitignorado novo,
  `docs/produto/screenshots-paridade-v2/` (mesmo padrão de `screenshots-paridade-v1/`),
  com `.gitignore` atualizado — **atenção**: se aparecer print do Legacy com produto/
  cliente real de novo, ele vai para lá, nunca pro diretório versionado.
- **CP6 (V1 fase 2)** — removidos "RMAs" (H1 artificial) e "Novo RMA" (link duplicado)
  da Página Inicial; gap header→Localizar caiu de 72px pra 14px (igual ao Legacy).
- **CP7 (V1 fase 2, maior item da fase)** — painel Localizar reconstruído como painel
  inline global (`#JS-Localizar`, mesmo padrão do `#JS-Novo`/`NovoMaximize()`), com os
  2 selects históricos (CAMPO de 14 opções, SOLUÇÃO de 12) + geometria exata.
  `.JSformLocalizarSelect` nunca tinha sido portada (achado real). `CriterioDeBusca`
  ganhou um 3º parâmetro opcional (`?Solucao`) — filtro aditivo, testado funcionalmente
  (12 resultados pra `solucao=REPARO`, batendo com o contador da sidebar). Mapeamento
  campo→tipo de busca documentado com `[GAP]` explícito onde não há coluna própria
  (fabricante/cliente/destinatario/rastreio_ida/protocolo/NF/numero caem no fallback
  `texto`, mesmo critério já aceito antes desta fase pra `os`→`nota_fiscal`).
- **CP8 (V1 fase 2)** — toggle de checkbox "item é do estoque" (`data-text-true/false`
  + `<i>` deslizante, escopado a `#JS-Novo` pra não vazar pra outros checkboxes do
  sistema); datas viraram `type="text" placeholder="00/00/2015"` com conversão
  dd/mm/aaaa→ISO na camada HTTP (`RmaController::validarDados()`, não mexe no TEMA V2);
  `box-sizing:border-box` removido das 5 classes de campo do formulário Novo (o Legacy
  nunca teve essa propriedade — media 2-6px mais estreito no V3 antes da correção).
  **CP8-04 (fabricante como input/datalist) ficou deliberadamente em aberto** —
  implementá-lo exigiria usar `EncontrarOuCriarFabricante`, cujo próprio docblock diz
  que é só pro migrador e que a criação em runtime "continua exigindo fabricante de uma
  lista já cadastrada" (decisão de uma fase anterior). Reverter essa decisão sem
  revalidar com o usuário ficou fora do escopo desta correção — decisão registrada, não
  esquecida.
  **Achado colateral real:** o CP6 tinha reaproveitado a flag `$ocultarTituloVisual`
  (criada só pra esconder o H1) também pra controlar se `#JS-Novo` é renderizado —
  ao incluir `rmas.index` nessa flag, o painel "Novo" global sumiu da Página Inicial
  (clique em "Novo" navegava pra `/rmas/create` em vez de abrir inline). Corrigido com
  uma flag própria (`$omitirPainelNovoGlobal`) e coberto por um teste de regressão
  dedicado (`RenderizaTemaV1Test::test_painel_novo_global_continua_presente_na_pagina_inicial`).
- **CP9 (V1 fase 2)** — Quadro de Anotações: `rows="14"`→`rows="20"`, título trocado da
  classe genérica `.quadro-de-anotacoes-titulo` pelas classes reais `.panotacao`/
  `.imganotacao` (ícone deslocado, `letter-spacing:3px`, `font-weight:700`), textarea
  com fundo/borda reais (`rgba(0,0,0,.1)`/sem borda, não mais `#26251f`/borda sólida).
  Botão "Salvar anotação" removido (sem fonte no Legacy) — autosave novo via `fetch`
  PUT debounced (800ms) pro mesmo endpoint do formulário tradicional do perfil
  (`identidade.perfil.anotacao.update`, que ganhou uma resposta JSON condicional,
  aditiva). Indicador de erro discreto (borda vermelha sutil) se o `fetch` falhar.
  Testado o ciclo completo (digitou → capturou a requisição → recarregou noutra aba →
  texto persistiu).

- **CP10 (V1 fase 2)** — sidebar de contadores: `box-sizing:border-box` removido de
  `.formLabelStats`/`.formValorStats` (mesmo achado do CP8 — `outerWidth` real é
  210px/57px, não 198px/45px); container ganhou `margin-right:-8px;margin-top:-15px`
  (ausentes). **Achado maior:** nenhum dos 16 contadores era link antes — todos
  viraram `<a>` reais (`link_do_contador_v1()`, novo em
  `app/Support/view_do_tema.php`): os 4 primeiros pras 4 listagens dedicadas, os 11
  de solução pro Localizar com `?solucao=X` (usa o filtro aditivo do CP7). Testado
  funcionalmente (clique em "REPARO" → 12 linhas, batendo com o contador).
  Confirmado que o `<a>` colapsa pra `width:0;height:0` (filhos `float:left` sem
  clearfix) **nos dois lados** — não é bug, é fidelidade ao Legacy, não "corrigido".
  `[GAP]` documentado: "QUANTIDADE TOTAL DE ITENS" não tem modo "listar tudo sem
  filtro" no V3.
- **CP11 (V1 fase 2)** — `separador2.png` copiado do TEMA V2 (já hash-verificado
  nesta sessão, mesmo arquivo) pra `public/images/tema-v1/`, inserido entre o painel
  Anotações/Contadores e o Centro de Avisos com a geometria exata
  (`float:right;margin-top:50px;height:40px`).
- **CP12 (V1 fase 2)** — ordem/títulos do Centro de Avisos corrigidos reaproveitando
  o MESMO mapeamento já provado no TEMA V2 (CP22) — os 10 `subp/listar_*.php` são os
  arquivos-fonte idênticos pros dois temas. **CP12-05 (redesenho por-grupo das 10
  tabelas com colunas próprias) ficou deliberadamente em aberto** — classificação
  completa feita (todos os 10 headers de coluna extraídos e documentados no diário,
  `CMP-V1-2-007`), mas a implementação é grande (10 presenters + read-models) e
  mexe num componente COMPARTILHADO com o TEMA V2 recém-fechado — risco de
  regressão sem benefício proporcional pra esta sessão. Nota técnica: os 10
  arquivos são ISO-8859 — `grep` sem `-a` os trata como binário e retorna vazio
  silenciosamente, achado operacional que vale registrar.
- **CP13 (V1 fase 2)** — fixture de QA (`QaSeeder`) ajustada: `os` de
  `"OS-QA-00001"` (formato alfanumérico, 11 caracteres) pra `"5901"` (numérico puro,
  4 dígitos, igual ao Legacy real); `descricao` de `"Equipamento ficticio QA 001"`
  (27 caracteres) pra `"Ficticio QA 001"` (16 caracteres). Adicionado 1 registro com
  `solucao=PendenteCredito` (nenhum existia antes) — a tela Aguardando Crédito, que
  desde a fase 1 (CMP-V1-005) só tinha sido testada de forma automatizada por falta
  de dado, agora tem 1 linha real pra screenshot. Zero regressão confirmada nos dois
  temas (`QaSeeder` é compartilhado).
- **CP14 (V1 fase 2) — investigação, achado real, SEM implementação (decisão do
  usuário pendente).** Ver seção própria abaixo.

Todos os 9 checkpoints (CP6–CP14) têm entrada de diário completa em
`plano-execucao-paridade-visual-v1-fase2.md` (`CMP-V1-2-001` a `CMP-V1-2-009`), com
tabela de medidas Legacy×V3×Delta (onde aplicável), screenshots versionados e commit
próprio. Nenhuma correção teve regressão: suíte completa (364 testes/820-821
assertions) verde depois de cada uma, `ParidadeVisualTemaV1.spec.ts` 4/4 verde depois
de cada uma (rodado do host), `ComparacaoVisualTemaV2Test.spec.ts` inalterado (2
passados/1 skip) nas rodadas em que o TEMA V2 podia ter sido afetado (CP13, seed
compartilhado).

## Decisão pendente do usuário — achado do CP14

`Rma::classeDeAlerta()` (domínio, compartilhado entre TEMA V1 e TEMA V2) nunca
devolve `ClasseDeAlerta::Urgente` — as condições que deveriam mapear pra essa classe
(`prioridade=Alta`, `origem=Cliente+marcarestoque=0+prazo de 30 dias estourado`) caem
todas em `ClasseDeAlerta::Inconformidade` por engano. No Legacy real
(`14.6.1/page/entrada.php:41-49`), essas são DUAS classes CSS com cores de fundo
diferentes (`TrInconformidade` `#303033` × `TrUrgente` `#382830`,
`pattern/15.9.7.css:60-63`). Confirmado ao vivo: nenhuma das 24 linhas de
`/rmas-entrada` (fixture com `prioridade=Alta` em 1 a cada 3 registros) rendeu
`TrUrgente`. **Esse mesmo achado já tinha sido apontado (mas não resolvido) no CP23
do TEMA V2** (`[INVESTIGAR]`: "Entrada não usa `TrUrgente` nem checa prazo de 30
dias") — é o MESMO bug de domínio, afetando os dois temas.

Achado secundário: a ordem de alternação da zebra (`TrZebrada1`/`TrZebrada2`) também
diverge quando há linhas de alerta intercaladas — o Legacy usa um contador (`$TR1`)
que PULA linhas `TrUrgente` mas AVANÇA em linhas `TrInconformidade`; o V3 usa o índice
bruto do array, que avança em toda linha igual. Efeito visual pequeno (duas linhas
neutras adjacentes por conteúdo podem sair com a mesma zebra em vez de alternada), mas
real e mensurável.

Evidência completa (trace de código linha a linha + verificação ao vivo) está em
`CMP-V1-2-009`. Duas opções de correção, sem decisão tomada:
- **(a) Mínima:** corrigir só o mapeamento `Urgente`×`Inconformidade` em
  `Rma::classeDeAlerta()` (aditivo, o case `Urgente` já existe no enum e em
  `classe_css_de_alerta()`, só nunca é devolvido) — fecha o achado principal, risco
  baixo, não toca a ordem de alternação.
- **(b) Completa:** corrige os dois achados juntos — exige passar um contador de
  zebra que pula linhas de alerta pra `classe_css_de_alerta()` em vez do índice bruto
  do `foreach`, tocando `RmaController`/`ListagensPorStatusController`/as views do
  TEMA V2 que reaproveitam a mesma função — maior, mais completo.

**Pergunte ao usuário qual escolher antes de implementar qualquer coisa aqui** — CP14
foi deliberadamente fechado como investigação, não como correção, por instrução
explícita do próprio checklist ("não é correção às cegas").

## Próximo passo imediato

### Atualização viva — 2026-08-26, retomada após o handoff

- O print do usuário reabriu CP12-05 como bloqueante. CP12-05A (protocolo),
  CP12-05B (prioridade alta) e CP12-05C (sem S/N) foram reconstruídos e aprovados
  após abrir os pares; ver `CMP-V1-2-011/012/013`. CP12-05D–J (outros 7 grupos)
  continuam pendentes.
- Gerador/evidência agora são permanentes em `scripts/qa/paridade-v1-fase2.mjs`,
  `screenshots-evidencias-v1-fase2/` e `evidencias-v1-fase2/cp15-medidas.json`.
- Nova matriz obrigatória menu a menu/link a link está em
  `plano-execucao-auditoria-navegacional-visual-v1.md`; o primeiro item registrado é
  `CMP-NAV-V1-001`.
- Disciplina reforçada pelo usuário: **commit local imediato por ciclo pequeno**
  (código + teste + evidência + diário), nunca acumular tudo para o final. Antes de
  cada commit, deixar no plano o próximo item exato e as instruções de retomada.
  Nunca push sem autorização.
- CP12-05C também corrigiu a ausência de `ORDER BY recebido DESC` no caso de uso,
  adicionou uma única linha QA recebida sem S/N e estendeu o gerador permanente.
  Validação: 28 testes/102 asserções, Browser dos três grupos 3/3 e Vite verde.
- Próximo item exato deste checkpoint: **CP12-05D**, tabela “SEM NF DE COMPRA E NF
  DE VENDA”; reler `listar_semnota.php` + `metodo.php::listar_semnota()`,
  implementar somente depois de classificar a estrutura, testar V1/V2+Browser,
  gerar e abrir o par, documentar `CMP-V1-2-014`/`CMP-NAV-V1-004`, então
  commitar isoladamente.
- Comando Browser no host precisa das bases explícitas:
  `PLAYWRIGHT_BASE_URL=http://localhost:8095 LEGACY_BASE_URL=http://localhost:8094/14.6.1/ npx playwright test ...`.

**Depois de CP12-05D–J, retomar CP15 — gate final da fase 2**
(`docs/produto/plano-execucao-paridade-visual-v1-fase2.md`). Precisa: rodar suíte PHP
completa + Vite build; rodar Playwright visual
completo (specs de paridade do host, demais no container) e confirmar TEMA V2 sem
regressão; comparar em 1440×1000, 1562×1400 e 1700×1000 (as duas viewports
secundárias, não executadas na fase 1, ficam obrigatórias aqui); abrir cada par final e
registrar no diário; produzir tabela final por elemento (mesmo formato de CMP-V1-007
da fase 1); atualizar `docs/produto/checklist-paridade-visual-v1-runtime.md` e
`PLANO-ATAQUE.md`. CP15 pode fechar com o achado do CP14 documentado como pendência
explícita (mesmo padrão já usado pro CP8-04/CP12-05) — não precisa esperar a decisão
do usuário pra rodar o gate, só não pode "esconder" o achado como se não existisse.

## Commits desta sessão (branch `main`)

Nota: em algum ponto desta sessão o branch remoto avançou (provavelmente um push feito
fora desta sessão) — `git log --oneline origin/main..HEAD` mostra só os commits mais
recentes como "à frente"; os commits de CP6-CP12 já foram sincronizados. Nenhum push
foi feito por esta sessão em nenhum momento. Do mais antigo pro mais recente, os
commits desta rodada de trabalho (CP25/V2 até CP14/V1):
- Fecha o gate final de paridade visual do tema V2 (`CMP-V2-008`, CP25).
- Remove o titulo e o link artificiais da pagina inicial do tema V1 (CP6).
- Reconstroi o painel Localizar inline do tema V1 com geometria e filtros reais (CP7).
- Corrige o checkbox as datas e o box model do painel Novo do tema V1 (CP8).
- Restaura o quadro de anotacoes com salvamento automatico no tema V1 (CP9).
- Documenta o handoff da sessao com o progresso da fase 2 do tema V1.
- Restaura os links reais da sidebar de contadores do tema V1 (CP10).
- Insere o separador antes do centro de avisos na pagina inicial do tema V1 (CP11).
- Corrige a ordem e os titulos do centro de avisos na pagina inicial do tema V1 (CP12).
- Ajusta a fixture de QA para comprimento de dado realista e cobre aguardando credito (CP13).
- Registra o achado da maquina de estados TrUrgente pendente de decisao (CP14, só documentação).

## Notas operacionais que valem para qualquer checkpoint futuro

- `ParidadeVisualTemaV1.spec.ts` roda do HOST, não do container (ver nota de
  infraestrutura acima) — `ComparacaoVisualTemaV2Test.spec.ts` continua rodando dentro
  do container (`docker compose exec laravel.test npx playwright test ...`), são specs
  diferentes com necessidades diferentes de rede.
- Scripts ad hoc de diagnóstico podem ser descartáveis em `scripts/_tmp-*.mjs`.
  Geradores que sustentam loops/evidência de checkpoint são obrigatoriamente
  versionados em `scripts/qa/`, junto da saída sanitizada e das instruções de uso.
- Screenshot com dado real de cliente/produto (Legacy) nunca vai pro diretório
  versionado (`screenshots-vis-v1-001/`) — só fictício/QA ou estrutural sem dado de
  negócio. Real vai pro par gitignorado (`screenshots-paridade-v1/` ou
  `screenshots-paridade-v2/`, conforme o tema).
- Depois de rodar Playwright via `docker compose exec`, o diretório `test-results/`
  fica com arquivos donos de `root` (o container roda como root), o que quebra a
  próxima execução do MESMO spec pelo host com `EACCES`. Se acontecer, limpar via
  `docker compose exec -T -u root laravel.test rm -rf /var/www/html/test-results`
  antes de tentar de novo.
- Toda decisão de "não implementar" (ex.: CP8-04) precisa ficar registrada inline no
  código E no diário — nunca just pular o item silenciosamente.
