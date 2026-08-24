# Proposal — Apresentação (Temas V1/V2 fiéis)

Fase 8 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §13).

## Por quê

Todas as funcionalidades (Fases 1-7) já existem com views mínimas, sem fidelidade
visual. Esta fase é a única responsável por fazer a V3 *parecer* o legado — os dois
temas visuais coexistindo, exatamente como o produto original sempre operou.

## O que entra

- Árvore de Blade por tema (`resources/views/temas/{v1,v2}/`), reaproveitando os
  **mesmos** Controllers/casos de uso das Fases 1-7 (nenhum duplicado).
- Sass por tema com a paleta capturada (`inventario-visual-tema-{v1,v2}.md`).
- `ResolverTemaAtivo` (middleware) — lê `tema_preferido` (Fase 1), decide a árvore de
  rotas/views.
- Rotas próprias por tema (`routes/tema-v1.php`, `routes/tema-v2.php`).
- QA visual lado a lado com o LEGACY-RUNTIME (`:8094`).

## O que não entra

- Qualquer regra de negócio nova — todas já existem desde as Fases 1-7; esta fase é
  puramente apresentação.
- Reescrita de design system além do necessário para reproduzir a aparência.

## Decisão registrada — granularidade de compartilhamento (resolve a pendência do checklist)

`checklist-master-v3.md` Parte 2 registrava como pendente decidir "se a diferença entre
temas é só view ou também Controller/rota". Evidência reunida nas Fases 1-7: as 21
regras de negócio já foram confirmadas como compartilhadas (camada `metodo.php`) ou
duplicadas **identicamente** entre temas (RN-13/RN-14) — nenhuma diverge por regra de
negócio, só por presença/ausência (RN-15/RN-21). **Decisão: Controllers/casos de uso
únicos** (já assim desde a Fase 1), **views e rotas por tema**. A navegação diverge de
verdade (TEMA V1 = páginas completas, TEMA V2 = âncoras de aba) — isso é roteamento e
front-end, não regra de negócio; o mesmo Controller pode responder às duas formas de
rota.

## Pendências reais, não resolvidas nesta OpenSpec (bloqueiam implementação, não planejamento)

1. Mecanismo exato das âncoras de TEMA V2 (`#entrada` etc.) — AJAX real ou âncora de
   scroll com tudo pré-renderizado. `[DÚVIDA]` em `inventario-visual-tema-v2.md`. Exige
   inspecionar o Network tab do LEGACY-RUNTIME (`:8094`) antes de escolher a
   implementação técnica — não muda nenhuma regra de negócio, só a escolha
   endpoint-JSON-com-fetch vs. página-longa-com-âncora.
2. RN-11 (classificação visual de inconformidade) `[DÚVIDA]` se tem equivalente exato
   em TEMA V1 (CSS 4× menor). Exige renderizar telas internas reais de TEMA V1 (novo
   RMA, detalhe — ainda não capturadas, ver `checklist-master-v3.md` Parte 1) antes de
   decidir se `ClasseDeAlerta` (Fase 5) tem 4 cores distintas em TEMA V1 ou versão
   simplificada.

## Rastreabilidade com o legado

Não introduz `LEG-RMA-NNN` novo — é a camada de apresentação de tudo que já foi
especificado (`LEG-RMA-006`, seleção de tema, já existe desde a Fase 1).
