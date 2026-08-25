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

- [ ] CP3A-01 — permitir opt-out visual do H1 por superfície.
- [ ] CP3A-02 — portar `concluido.png` byte a byte e validar hash/50×50.
- [ ] CP3A-03 — montar `title-icone fl`, `title-comicone fl`, `hr.both`.
- [ ] CP3A-04 — declarar colunas 8/5/5/12/14/10/18/17/6/4 sem normalizar soma.
- [ ] CP3A-05 — resolver linha próprio: Sem Garantia→TR3; demais TR1/TR2.
- [ ] CP3A-06 — tornar alternância determinística e registrar a dúvida `$TR1`.
- [ ] CP3A-07 — não usar `classe_css_de_alerta()` nesta superfície.
- [ ] CP3A-08 — restaurar wrappers e área clicável por célula.
- [ ] CP3A-09 — NF zero→vazio; moeda com ponto decimal; origem abreviada.
- [ ] CP3A-10 — testar ausência visual do H1, ícone, classes e colunas.
- [ ] CP3A-11 — capturar/reabrir/comparar Concluído e registrar diário.
- [ ] CP3A-12 — testes/build e commit local.

## CP3B — Entrada

- [ ] CP3B-01 — portar `entrada.png`, validar hash/50×50.
- [ ] CP3B-02 — montar cabeçalho interno e ocultar H1 artificial.
- [ ] CP3B-03 — declarar colunas 8/10/6/6/13/12/18/17/6/4.
- [ ] CP3B-04 — preservar família compacta e alternância determinística.
- [ ] CP3B-05 — decidir/documentar `[BUG-LEGADO]` dos estados inalcançáveis.
- [ ] CP3B-06 — corrigir wrappers, área clicável, NF/moeda/origem/data.
- [ ] CP3B-07 — capturar/reabrir/comparar, registrar diário, testes/build e commit.

## CP3C — Encaminhado

- [ ] CP3C-01 — portar `encaminhado.png`, validar hash/50×50.
- [ ] CP3C-02 — montar cabeçalho interno e ocultar H1 artificial.
- [ ] CP3C-03 — expor `NF R` pelo read model/objeto de apresentação.
- [ ] CP3C-04 — declarar colunas 8/10/6/13/13/18/6/8/14/4.
- [ ] CP3C-05 — preservar família compacta e decisão dos estados `[BUG-LEGADO]`.
- [ ] CP3C-06 — corrigir wrappers, área clicável e formatação.
- [ ] CP3C-07 — capturar/reabrir/comparar, registrar diário, testes/build e commit.

## CP3D — Aguardando crédito

- [ ] CP3D-01 — portar `pendente.png`, validar hash/50×50.
- [ ] CP3D-02 — montar cabeçalho interno e ocultar H1 artificial.
- [ ] CP3D-03 — expor `NF R` pelo read model/objeto de apresentação.
- [ ] CP3D-04 — declarar colunas 8/5/12/13/9/18/5/8/6/12/4.
- [ ] CP3D-05 — usar somente TR1/TR2; remover helper genérico desta superfície.
- [ ] CP3D-06 — reproduzir conscientemente o vazio “Nenhum encontrado”.
- [ ] CP3D-07 — corrigir wrappers, área clicável e formatação.
- [ ] CP3D-08 — capturar/reabrir/comparar, registrar diário, testes/build e commit.

## CP4 — resumo completo de Concluídos

- [ ] CP4-01 — definir DTO/read model do resumo sem SQL no Blade.
- [ ] CP4-02 — calcular soma, quantidade total e quantidade com valor zero.
- [ ] CP4-03 — entregar data de processamento; congelar relógio no teste.
- [ ] CP4-04 — renderizar quatro textos na ordem e grafia históricas.
- [ ] CP4-05 — portar float/margens/tamanho/letter-spacing do resumo.
- [ ] CP4-06 — formatar total com ponto decimal e sem agrupamento.
- [ ] CP4-07 — capturar/reabrir/comparar seção inferior e registrar diário.
- [ ] CP4-08 — testes focados/build e commit local.

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

## Modelo para próximas entradas

Cada `CMP-V1-NNN` deve registrar: checkpoint/tela; fonte PHP/CSS; ambiente; caminhos dos
prints; elementos abertos; tabela de medidas Legacy/V3; fonte rasterizada; diferença
perceptível; seletor responsável; decisão; testes/build; commit. Sem esses campos, o
item permanece aberto.
