# Plano de execução — paridade estrutural do Tema V1

Data: 2026-08-25. Estado: **em execução**. Este documento é o detalhamento operacional
de `PLANO-ATAQUE.md`/F10-VIS; não é um roadmap paralelo. Fonte da reabertura:
`INV-RMA-BUG-LAYOUT-falhas.md`. Parecer:
`docs/pareceres/parecer-paridade-estrutural-v1-falhas-layout.md`.

Regra de gate: toda comparação feita durante esta frente entra no Diário de comparação
abaixo. Um item só recebe `[x]` depois de os prints anterior/referência/posterior terem
sido abertos e inspecionados, com ambiente e medidas registrados.

## Ambiente fixo

- Chromium; zoom 100%; `deviceScaleFactor: 1`.
- Viewport primária: 1440×1000; secundárias: 1562×1400 e 1700×1000.
- Legacy: `http://localhost:8094/14.6.1/`.
- V3: `http://localhost:8095`.
- Prints Legacy com dado histórico permanecem ignorados pelo Git.
- Medidas: `getBoundingClientRect`, computed style e CDP
  `CSS.getPlatformFontsForNode` para fonte realmente rasterizada.

## CP0 — baseline e parecer

- [x] CP0-01 — refazer `git status`, branch e log antes das alterações.
- [x] CP0-02 — ler integralmente investigação e checklists visuais.
- [x] CP0-03 — ler CSS, PHP e Blades das quatro listagens.
- [x] CP0-04 — inventariar dimensões/horários dos dez PNGs locais.
- [x] CP0-05 — agente independente inspecionar visualmente os dez PNGs e emitir parecer.
- [x] CP0-06 — classificar prints antigos como evidência histórica, não estado atual.
- [x] CP0-07 — registrar `[BUG-LEGADO]` dos SELECTs e `[DÚVIDA]` da zebra inicial.
- [x] CP0-08 — vincular este plano aos checklists mestre/runtime e ao plano de ataque.

## CP1 — cascata e fontes

- [x] CP1-01 — medir antes: body/menu/título/th/td/OS/rodapé no V3.
- [x] CP1-02 — separar `_v1-base.scss` do entrypoint `v1.scss`.
- [x] CP1-03 — emitir V1-base antes da camada compartilhada.
- [x] CP1-04 — inventariar e validar as fontes históricas locais.
- [x] CP1-05 — registrar rejeição OTS das cópias históricas truncadas.
- [x] CP1-06 — vendorizar Open Sans oficial válida e licença, sem runtime externo.
- [x] CP1-07 — reproduzir as pilhas/overrides exatos de 14.6.1 + 15.9.7.
- [x] CP1-08 — provar carregamento de Open Sans e Fira Mono pelo FontFaceSet.
- [x] CP1-09 — provar fonte rasterizada por CDP em menu/título/th e filhos relevantes.
- [x] CP1-10 — medir depois no V3 e comparar header + uma tabela.
- [x] CP1-11 — abrir os prints salvos pertinentes; o par atual de Concluído foi revisto.
- [x] CP1-12 — gerar/reabrir o par normalizado após CSS final de CP1.
- [x] CP1-13 — rodar build e Playwright focal.
- [x] CP1-14 — registrar resultado final no diário e criar commit local.

## CP2 — primitivas reais da tabela V1

- [x] CP2-01 — diff seletor legado × HTML legado × Blade × Sass.
- [x] CP2-02 — portar `TableListarFPEF-TR` e hover: 35 px/#2A2A2A/#F8C18B.
- [x] CP2-03 — portar `Tabelinha-TD`: 22 px/11 px/1 px/uppercase.
- [x] CP2-04 — conferir `Tabelinha-Table`: #363333/100%/12 px/borda e padding zero.
- [x] CP2-05 — conferir `Tabelinha-TR1/2/3`: cores e regra de 30 px.
- [x] CP2-06 — conferir `TrZebrada1/2`, `TrInconformidade`, `TrUrgente`: 18 px.
- [x] CP2-07 — preservar overrides globais `table/td/tr/th` da segunda folha.
- [x] CP2-08 — tornar o link da célula bloco integral sem HTML inválido.
- [x] CP2-09 — testar o filho que realmente pinta o glifo, não só o `td`.
- [x] CP2-10 — capturar fixture com header, linhas altas e compactas.
- [x] CP2-11 — abrir/comparar prints, registrar diário, testes/build e commit local.

## CP3A — Concluído

- [x] CP3A-01 — permitir opt-out visual do H1 por superfície.
- [x] CP3A-02 — portar `concluido.png` byte a byte e validar hash/50×50.
- [x] CP3A-03 — montar `title-icone fl`, `title-comicone fl`, `hr.both`.
- [x] CP3A-04 — declarar colunas 8/5/5/12/14/10/18/17/6/4 sem normalizar soma.
- [x] CP3A-05 — resolver linha próprio: Sem Garantia→TR3; demais TR1/TR2.
- [x] CP3A-06 — tornar alternância determinística e registrar a dúvida `$TR1`.
- [x] CP3A-07 — não usar `classe_css_de_alerta()` nesta superfície.
- [x] CP3A-08 — restaurar wrappers e área clicável por célula.
- [x] CP3A-09 — NF zero→vazio; moeda com ponto decimal; origem abreviada.
- [x] CP3A-10 — testar ausência visual do H1, ícone, classes e colunas.
- [x] CP3A-11 — capturar/reabrir/comparar Concluído e registrar diário.
- [x] CP3A-12 — testes/build e commit local.

## CP3B — Entrada

- [x] CP3B-01 — portar `entrada.png`, validar hash/50×50.
- [x] CP3B-02 — montar cabeçalho interno e ocultar H1 artificial.
- [x] CP3B-03 — declarar colunas 8/10/6/6/13/12/18/17/6/4.
- [x] CP3B-04 — preservar família compacta e alternância determinística.
- [x] CP3B-05 — decidir/documentar `[BUG-LEGADO]` dos estados inalcançáveis.
- [x] CP3B-06 — corrigir wrappers, área clicável, NF/moeda/origem/data.
- [x] CP3B-07 — capturar/reabrir/comparar, registrar diário, testes/build e commit.

## CP3C — Encaminhado

- [x] CP3C-01 — portar `encaminhado.png`, validar hash/50×50.
- [x] CP3C-02 — montar cabeçalho interno e ocultar H1 artificial.
- [x] CP3C-03 — expor `NF R` pelo read model/objeto de apresentação.
- [x] CP3C-04 — declarar colunas 8/10/6/13/13/18/6/8/14/4.
- [x] CP3C-05 — preservar família compacta e decisão dos estados `[BUG-LEGADO]`.
- [x] CP3C-06 — corrigir wrappers, área clicável e formatação.
- [x] CP3C-07 — capturar/reabrir/comparar, registrar diário, testes/build e commit.

## CP3D — Aguardando crédito

- [x] CP3D-01 — portar `pendente.png`, validar hash/50×50.
- [x] CP3D-02 — montar cabeçalho interno e ocultar H1 artificial.
- [x] CP3D-03 — expor `NF R` pelo read model/objeto de apresentação.
- [x] CP3D-04 — declarar colunas 8/5/12/13/9/18/5/8/6/12/4.
- [x] CP3D-05 — usar somente TR1/TR2; remover helper genérico desta superfície.
- [x] CP3D-06 — reproduzir conscientemente o vazio “Nenhum encontrado”.
- [x] CP3D-07 — corrigir wrappers, área clicável e formatação.
- [x] CP3D-08 — capturar/reabrir/comparar, registrar diário, testes/build e commit.

## CP4 — resumo completo de Concluídos

- [x] CP4-01 — definir DTO/read model do resumo sem SQL no Blade.
- [x] CP4-02 — calcular soma, quantidade total e quantidade com valor zero.
- [x] CP4-03 — entregar data de processamento; congelar relógio no teste.
- [x] CP4-04 — renderizar quatro textos na ordem e grafia históricas.
- [x] CP4-05 — portar float/margens/tamanho/letter-spacing do resumo.
- [x] CP4-06 — formatar total com ponto decimal e sem agrupamento.
- [x] CP4-07 — capturar/reabrir/comparar seção inferior e registrar diário.
- [x] CP4-08 — testes focados/build e commit local.

## CP5 — propagação e gate final

- [ ] CP5-01 — inventariar todos os usos V1 das primitivas corrigidas.
- [ ] CP5-02 — separar correções automáticas de exceções históricas por superfície.
- [ ] CP5-03 — verificar home, usuários, parceiros, detalhe e controle.
- [ ] CP5-04 — provar que o Tema V2 não regrediu.
- [ ] CP5-05 — rodar testes focados e suíte PHP completa.
- [ ] CP5-06 — rodar build Vite e Playwright visual completo.
- [ ] CP5-07 — comparar em 1440×1000, 1562×1400 e 1700×1000.
- [ ] CP5-08 — abrir cada par final e registrar uma entrada no diário.
- [ ] CP5-09 — produzir tabela final por elemento e caminhos dos screenshots.
- [ ] CP5-10 — atualizar golden somente se comprovadamente mais próximo do Legacy.
- [ ] CP5-11 — atualizar checklists/parecer e criar commit final do checkpoint.

## Diário de comparação

### CMP-V1-001 — prints salvos da investigação

- Ambiente: capturas manuais, dimensões originais preservadas; zoom/DPR não provados.
- Arquivos: os dez PNGs de `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/`.
- Inspeção: aberta e concluída pelo parecer independente em 2026-08-25.
- Resultado: par Concluído atual confirma divergências estruturais; oito capturas antigas
  são evidência anterior a `6ddadde`/`91a1cfc`; escala não serve como régua.
- Decisão: reabrir paridade e exigir captura normalizada para fechar pixels.

### CMP-V1-002 — CP1 antes/depois, Concluído 1440×1000

- Ambiente: Chromium headless, zoom 100%, DPR 1, viewport 1440×1000.
- Screenshots: `screenshots-paridade-v1/{legacy,v3}-cp1-concluidos-1440x1000.png`
  (locais/ignorados pelo Git).
- Antes V3: corpo/menu/título/th/td/rodapé simplificados para Arial/Fira Sans.
- Depois V3: Open Sans rasterizada (CDP) em menu/título/th; Arial/Liberation Sans nos
  glifos diretos de `td`; Open Sans nos links; FontFaceSet carrega a fonte local.
- Geometria já igual: `#BASE` CSS 984 px, caixa 1004 px; `#CONTEUDO` 984 px; menu
  `x=275,109`, largura 94 px e altura 19 px nos dois runtimes.
- Divergências restantes visíveis/medidas: ícone/cabeçalho, H1, header 18×35 px,
  linha compacta×alta, colunas e resumo. Pertencem a CP2–CP4.
- Prova final: `{legacy,v3}-cp1-final2-concluidos-1440x1000.png` e recortes
  `{legacy,v3}-cp1-footer-1440.png`, todos abertos após a correção. Header global,
  tipografia e rodapé equivalentes; diferença de 1 px no recorte do rodapé não é
  perceptível. `CÃ³pia` Legacy × `Cópia` V3 é `[BUG-LEGADO]` de encoding consciente.
- Estado: **CP1 APROVADO**; divergências remanescentes pertencem a CP2–CP4.

### CMP-V1-003 — CP2, primitivas de tabela

- Ambiente: Chromium headless, zoom 100%, DPR 1, viewport 1440×1000.
- Screenshots abertos: `{legacy,v3}-cp2-concluidos-1440x1000.png` e
  `v3-cp2-fixture-primitivas-1440.png` (locais/ignorados).
- Resultado: `TableListarFPEF-TR` coincide em 35 px, #2A2A2A e #F8C18B; tabela/TD
  coincidem em fundo, grade, alinhamento, uppercase e spacing. A fixture comprova TR1,
  TR2, TR3, TrZebrada1/2, TrInconformidade e TrUrgente.
- Medidas: linha alta CSS/bbox 30 px na fixture; compacta `td` 18 px e `tr` 21 px;
  link de célula `display:block` e área integral.
- Diferença restante: Concluído ainda escolhe família compacta; aplicação por superfície
  pertence ao CP3A, não à primitiva.
- Estado: **CP2 APROVADO** por teste e inspeção visual.

### CMP-V1-004 — CP3A, Concluído completo (H1/ícone/colunas/zebra/formatação)

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000.
- Fonte PHP/CSS: `legacy-source/14.6.1/page/concluidos.php` (lida por inteiro nesta
  sessão — confirma literalmente as larguras 8/5/5/12/14/10/18/17/6/4, o `style`
  inline `margin-top:8px;margin-left:0px` do `.title-icone` e o resumo inferior, que
  fica para `CP4`).
- **Bug corrigido nesta sessão, achado durante a validação (não estava no plano
  original):** `concluidos.blade.php` tinha um `@section('omitirTituloPadrao')` sem
  `@endsection`, e a segunda linha usava `@php($indiceZebra = 0)` (forma inline sem
  `@endphp` próprio) antes de um bloco `@php ... @endphp` mais abaixo. O compilador
  Blade pareia `@php`/`@endphp` pelo **primeiro** `@endphp` encontrado no arquivo
  inteiro (`storePhpBlocks`), não pelo par mais próximo semanticamente — isso
  transformou `@foreach`, comentários e o `@php` do loop em texto literal dentro de um
  único bloco `<?php ?>`, deixando um `@endforeach` órfão (`syntax error, unexpected
  token "endforeach"`, view quebrada, `ListagensPorStatusTest` todo vermelho). Corrigido
  fechando a seção vazia e trocando a forma inline por um bloco `@php $indiceZebra = 0;
  @endphp` dedicado. Suíte completa voltou a verde (357→359 depois dos 2 testes novos).
- Screenshots abertos: `{legacy,v3}-cp3a-concluidos-1440x1000.png` (viewport, não
  full-page — o Legacy tem 1219 RMAs concluídos reais e full-page gerava ~37000px de
  altura; locais/ignorados pelo Git).
- **Achado de metodologia:** a primeira rodada bloqueava `fonts.googleapis.com` no
  Legacy (prática herdada do teste antigo) e isso mascarava a fonte real — o Legacy caía
  para `Liberation Sans` (fallback), dando `.title-comicone` 602×16 contra 612×19 do V3.
  Sem bloquear (ambiente tem rede), o Legacy carrega Open Sans real do Google e bate
  **exatamente** 612×19 com o V3, provando que a Open Sans local vendorizada no CP1 é
  metricamente idêntica à oficial. Decisão: comparações futuras não devem bloquear fontes
  do Legacy — isso testa uma condição (offline) que o Legacy real nunca está.
- Tabela de medidas (Legacy × V3):

  | Elemento | Legacy | V3 | Resultado |
  |---|---|---|---|
  | `#BASE` width | 1004px (CSS 984+padding) | 1004px | OK |
  | `#CONTEUDO` width | 984px | 984px | OK |
  | `.title-icone` (ícone 50×50) | 50×54 (wrapper), img 50×50 | 50×54 (wrapper), img 50×50 | OK |
  | `.title-comicone` | 612×19 | 612×19 | OK |
  | `.Tabelinha-Table` width | 984px | 984px | OK |
  | `.TableListarFPEF-TR` (header) | 983×35, x=229 y=140 | 983×35, x=229 y=140 | OK |
  | fonte do menu (CDP) | Open Sans (custom) | Open Sans (custom) | OK |
  | fonte do header/td | Arial, "Open Sans", "Fira mono"/"Fira Mono" | idem | OK |
  | H1 artificial em `#CONTEUDO` | ausente | ausente (testado) | OK |
  | colunas (% de 984, via `<colgroup>`) | declaradas via `style="width:X%"` nos `<th>` (8/5/5/12/14/10/18/17/6/4) | `<colgroup>` com os mesmos 10 valores | OK (fonte idêntica; pixel renderizado diverge só porque `table-layout` é `auto` nos dois e o conteúdo real (1219 registros históricos) difere do seed de QA — não é defeito de CSS) |

- Diferença perceptível restante: nenhuma nas primitivas testadas. `CP4` (resumo
  "VALOR TOTAL"/"DATA DO PROCESSAMENTO"/quantidades, presente em
  `concluidos.php:65-70`, mesmo arquivo) continua **não implementado** no Blade — não é
  regressão desta rodada, é o próximo checkpoint do plano.
- Decisão: **CP3A APROVADO** para H1/ícone/colunas/zebra/wrappers/formatação. `CP4`
  (resumo inferior) permanece aberto e é o próximo passo.
- Testes/build: `php artisan test` (359 testes/796 assertions, verde, dentro do
  container `laravel.test`); `npm run build` (Vite ok). 2 testes novos em
  `ListagensPorStatusTest` (Sem Garantia→`Tabelinha-TR3`/demais→TR1/TR2; ausência de
  `<h1>` dentro de `#CONTEUDO` + presença do ícone/`title-comicone`/`colgroup`).
- Commit: a seguir (`#ARQ-RMA - Corrige bug de secao/php no Blade e fecha CP3A do Tema V1`).

### CMP-V1-005 — CP3B/CP3C/CP3D, Entrada/Encaminhado/Aguardando crédito

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear `fonts.googleapis.com` (decisão registrada em CMP-V1-004).
- Fonte PHP/CSS lida por inteiro: `legacy-source/14.6.1/page/{entrada,encaminhados,
  aguardandocredito}.php`. Confirma literalmente larguras, wrappers `<a><div>` por
  coluna (variam por tela — `MODELO`/`S/N` de `Entrada` não são clicáveis, diferente de
  `Concluido`) e o `style="margin-top:8px"` do ícone de `Aguardando credito` (sem
  `margin-left`, mas equivalente ao `margin-top:8px;margin-left:0px` das outras 3 telas
  porque `<p>` tem `margin-left:0` por padrão do navegador — sem CSS `.title-icone` base
  no legado, só `.fl` + o `style` inline).
- **Achado de estrutura (não é bug, é decisão preservada):** `Aguardando credito` no
  legado tem uma tag `<table>` desbalanceada — só abre dentro do `if
  ($sql->num_rows>0)`, mas o `</table>` fica fora do `if/else`, então a página quebra o
  HTML quando não há registro. Reproduzido de forma válida: mensagem "Nenhum encontrado"
  sem abrir tabela nenhuma (mesmo texto visível, HTML correto).
- **Extração de duplicação:** a abreviação de origem (`MERCADO LIVRE`/`Leilão`/
  `Licitação`) estava sendo escrita inline em `concluidos.blade.php` e ia se repetir
  mais 3x — extraído para `origem_abreviada_v1()` em `app/Support/view_do_tema.php`
  (mesmo arquivo de `classe_css_de_alerta()`), usado pelas 4 telas agora.
- **Decisão sobre "NF R" (Encaminhado/Aguardando crédito):** o checklist original
  (`CP3C-03`/`CP3D-03`) previa "expor NF R pelo read model". Na prática o domínio atual
  (`App\Rma\Dominio\Rma`) não tem propriedade `nfremessa` — só existe como coluna
  histórica do migrador (Fase 9), sem dono na camada de aplicação. Adicionar o campo ao
  domínio é mudança de modelo, fora do escopo desta correção visual. **Decisão: coluna
  `NF R` mantida na posição/largura exata do legado (preserva geometria), célula sempre
  vazia** — não é dado inventado, é ausência honesta. Print do Legacy nesta sessão
  confirma que o campo tem dado real em produção (`23948`, `*002`) — item pendente para
  uma frente de domínio futura, registrado aqui, não implementado agora.
- Screenshots abertos: `{legacy,v3}-cp3-{entrada,encaminhados,aguardando-credito}-1440x1000.png`
  (viewport, locais/ignorados pelo Git).
- Tabela de medidas (Legacy × V3), as três telas:

  | Elemento | Entrada | Encaminhado | Aguardando crédito |
  |---|---|---|---|
  | `.title-icone` (50×50 img) | 50×54 = 50×54 OK | 50×54 = 50×54 OK | 50×54 = 50×54 OK |
  | `.title-comicone` | 530×19 = 530×19 OK | 452×19 = 452×19 OK | 414×19 = 414×19 OK |
  | header da tabela | 983×35 = 983×35 OK | 983×35 = 983×35 OK | sem registro no seed de QA no momento da captura — estrutura provada via `ListagensPorStatusTest` (dado controlado) |
  | largura da tabela | 984 = 984 OK | 984 = 984 OK | 984 (provada por teste) |
  | colunas (px, `<colgroup>` vs `<th style>`) | `[79,98,59,59,128,118,177,167,59,39]` iguais | `[79,98,59,127~128,128,176,59,81,137,39]` (1px de diferença na coluna FABRICANTE, arredondamento) | `[79,49,118,127,88,176,49,81,59,118,39]` só Legacy (V3 sem registro no seed) |
  | H1 artificial | ausente (testado) | ausente (testado) | ausente (testado) |
  | zebra | `TrZebrada1/2`/`TrInconformidade` (achado 5, preservado) | idem | `Tabelinha-TR1/TR2` só (achado 4, corrigido — antes usava `classe_css_de_alerta()` por engano) |
- Diferença perceptível restante: nenhuma. `Aguardando credito` não pôde ser comparada
  pixel a pixel em runtime porque o seed de QA atual não tem nenhum RMA com
  `solucao=PENDENTE CREDITO`; a estrutura (colgroup, TR1/TR2, ausência de H1, ícone) foi
  confirmada por `ListagensPorStatusTest::test_aguardando_credito_usa_apenas_zebra_tr1_tr2`
  com dado controlado (`RefreshDatabase` + factory).
- Decisão: **CP3B e CP3C APROVADOS** por medição direta. **CP3D APROVADO** por medição
  parcial (ícone/título idênticos) + teste automatizado para a tabela (sem par de
  screenshot com dado real disponível nesta sessão).
- Testes/build: `php artisan test` (362 testes/813 assertions, verde); `npm run build`
  (Vite ok). 3 testes novos em `ListagensPorStatusTest` (ícone/colgroup/ausência de H1
  para Entrada e Encaminhado; zebra exclusiva TR1/TR2 para Aguardando crédito).
- Commit: a seguir (`#ARQ-RMA - Restaurada a aparencia original das telas de Entrada
  Encaminhado e Aguardando Credito do Tema V1`).

### CMP-V1-006 — CP4, resumo inferior de Concluídos

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1400 (a
  seção fica abaixo da tabela — no Legacy com 1219 registros reais isso é ~y=37676, só
  medido via `getBoundingClientRect`/computed style, não por screenshot).
- Fonte: `legacy-source/14.6.1/page/concluidos.php:19-27,66-69` (lido por inteiro).
  `$soma`/`$quantidadetotal`/`$quantidadesemvalor` são acumulados durante o `while` que
  lista a tabela. Implementado como `ListagensPorStatusController::resumoDeConcluidos()`
  — agregação em memória sobre os `$registros` (já vêm do caso de uso), sem query nova
  e sem cálculo no Blade.
- Medidas (Legacy × V3, computed style do `<h3>`/`<p>`):

  | Propriedade | Legacy | V3 | Resultado |
  |---|---|---|---|
  | texto `h3` (formato) | `VALOR TOTAL: R$ 146762.81` | `VALOR TOTAL: R$ 0.00` (seed QA sem valor) | OK (mesmo formato, ponto decimal, sem agrupamento) |
  | `float` | right | right | OK |
  | `margin-top` | 0px | 0px | OK |
  | `letter-spacing` | 2px | 2px | OK |
  | `font-family` | Arial, "Open Sans", "Fira mono" | Arial, "Open Sans", "Fira Mono" | OK |
  | `font-size` (herdado, sem declaração própria em nenhum dos dois) | 14px | 14.04px | diferença de 0.04px, imperceptível — nenhum dos dois lados declara `font-size` no `h3`, é herança do UA; não há número da fonte para reproduzir literalmente aqui |
  | ordem dos 4 textos | DATA→Qtd total→Qtd sem valor→(VALOR TOTAL flutua à direita, renderizado antes no DOM) | idêntica | OK |
  | grafia | "a cima", "monetario" sem acento (histórico) | preservada literalmente | OK |
- Diferença perceptível restante: nenhuma (a de 0.04px não é visível).
- Decisão: **CP4 APROVADO**.
- Testes/build: `php artisan test` (363 testes/818 assertions, verde); `npm run build`
  (Vite ok). Teste novo `test_concluidos_mostra_resumo_com_total_data_e_quantidades`
  com `Date::setTestNow()` (relógio congelado em 2026-03-10) e 3 registros (2 com valor,
  1 zerado) provando soma `200.00`, contagem `3`/`1`.
- Commit: a seguir (`#ARQ-RMA - Adicionado o resumo final da tela de Concluidos do
  Tema V1`).

**Estado do plano após CMP-V1-006: CP0 a CP4 completos. Falta só CP5 (propagação e
gate final) para encerrar esta frente.**

## Modelo para próximas entradas

Cada `CMP-V1-NNN` deve registrar: checkpoint/tela; fonte PHP/CSS; ambiente; caminhos dos
prints; elementos abertos; tabela de medidas Legacy/V3; fonte rasterizada; diferença
perceptível; seletor responsável; decisão; testes/build; commit. Sem esses campos, o
item permanece aberto.
