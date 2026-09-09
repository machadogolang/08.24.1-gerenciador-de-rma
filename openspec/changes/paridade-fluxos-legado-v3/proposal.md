# Proposal — Paridade total dirigida por fluxos (Legacy executável × V3)

Data: 2026-09-09. Esta frente **audita e fecha** paridade entre o Legacy executável
(`08.24.4-legacy-gerenciador-de-rma`, `f83542c`) e a V3, por fluxos ponta a ponta e
quatro quadrantes (Legacy V1/V2 × V3 V1/V2). Não cria arquitetura paralela e não
duplica tasks dos OpenSpecs existentes — referencia `temas-v1-v2`,
`parceiros`, `rma-cadastro-e-localizacao`, `rma-ciclo-de-vida`,
`rma-alertas-e-prioridade`, `rma-creditos-e-relatorios`, `rma-logistica-e-historico`,
`autenticacao-usuarios`, `front-003-shell-telas-secundarias` e `saas-multiempresa`.

## Problema

- O eixo funcional dos 48 LEG-RMA foi declarado fechado, mas novas comparações por
  fluxo encontram gaps reais (ex.: troca de tema no menu V1, detalhe de parceiro,
  busca por contrapartes, UX de destinatário, página de anotações V2).
- Várias pages/rotas Legacy ainda não têm correspondente provado na V3 e o checklist
  antigo não é prova.

## Decisões de produto

Sem decisão nova nesta proposal. Regras já decididas aplicadas:
hard deletes rejeitados; arquivar usa V2; troca de própria senha usa V1; tenant e
policy nunca relaxados; tema muda apresentação, não capacidade.

## Critério de saída da frente

P0–P10 concluídos, todos os fluxos classificados sem `[DECISAO-PENDENTE]` bloqueante,
documentação antiga reconciliada, PHPUnit completo verde, Playwright de fluxos nos
quatro quadrantes verde e handoff atualizado.
