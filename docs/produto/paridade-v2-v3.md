# Matriz de paridade V2 → V3

Data: 2026-08-24. Índice de rastreamento: nenhuma funcionalidade relevante do RMA V2
pode "desaparecer" silenciosamente na V3. Atualizado a cada avanço de OpenSpec/
implementação — ainda **totalmente pendente**, nenhuma OpenSpec nem implementação da V3
existe ainda (correto neste estágio: arqueologia antes de especificação, ver
`PLANO-ATAQUE.md`).

Fonte dos IDs: `docs/legado/inventario-funcional-rma-v2.md`.

| ID | Funcionalidade V2 | Tema V1 | Tema V2 | OpenSpec | V3 | QA | Status |
|---|---|---|---|---|---|---|---|
| LEG-RMA-001 | Login/logout | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-002 | Autocadastro com convite | confirmado | dúvida | — | — | — | PENDENTE |
| LEG-RMA-003 | Resetar senha (admin) | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-004 | Trocar própria senha | funcional (correto) | quebrado (regressão) | — | — | — | PENDENTE — V3 usa TEMA V1 como especificação |
| LEG-RMA-005 | Gerenciar usuários/permissões | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-006 | Selecionar tema V1/V2 | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-007 | Cadastrar novo RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-008 | Localizar/pesquisar RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-009 | Ver detalhes do RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-010 | Editar/salvar RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-011 | Receber RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-012 | Encaminhar RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-013 | Concluir RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-014 | Arquivar RMA | quebrado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-015 | Retornar p/ entrada (rollback) | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-016 | Estado "retornou" | código morto | código morto | — | — | — | NÃO RECONSTRUIR (morto em ambos) |
| LEG-RMA-017 | Registrar solução/resolução | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-018 | Alerta: recebido >30d não encaminhado | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-019 | Alerta: não vai dar garantia (MARKVISION) | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-020 | Alerta: NF pendente de lançamento | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-021 | Alerta: protocolo aberto não encaminhado | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-022 | Alerta: garantia fornecedor expirada | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-023 | Alerta: menos de 30d p/ expirar | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-024 | Alerta: prazo destinatário estourado | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-025 | Alerta: prioridade alta | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-026 | Alerta: sem nota fiscal | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-027 | Alerta: sem número de série | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-028 | Classificação visual de inconformidade | dúvida | confirmado | — | — | — | PENDENTE |
| LEG-RMA-029 | Urgência por threshold R$75 | dúvida | confirmado | — | — | — | PENDENTE |
| LEG-RMA-030 | Cadastro de clientes | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-031 | Cadastro de fabricantes | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-032 | Cadastro de fornecedores | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-033 | Cadastro de assistências técnicas | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-034 | "Autorizada" (alias morto) | n/a | código morto | — | — | — | NÃO RECONSTRUIR (morto) |
| LEG-RMA-035 | Tabela unificada `assistencias(tipo)` | legado/abandonado | n/a | — | — | — | RETOMAR IDEIA (não o código) — ver EVO-DOM-001 |
| LEG-RMA-036 | Fluxo de crédito | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-037 | Relatório RCD | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-038 | Relatório RPEC | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-039 | Relatório RMPE | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-040 | Consolidação de frete (Porto Alegre) | código morto/comentado | confirmado, ativo | — | — | — | PENDENTE |
| LEG-RMA-041 | Boletins relacionados (histórico por contraparte) | dúvida | confirmado | — | — | — | PENDENTE |
| LEG-RMA-042 | Bloco de notas pessoal | confirmado | dúvida | — | — | — | PENDENTE |
| LEG-RMA-043 | Auditoria de autenticação | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-044 | Auditoria de modificação de RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-045 | Notificação por e-mail | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-046 | Normalização automática (HGST→Hitachi, origem) | confirmado (duplicado) | confirmado | — | — | — | PENDENTE |
| LEG-RMA-047 | S/N de retorno auto-preenchido | ausente | confirmado | — | — | — | PENDENTE |
| LEG-RMA-048 | Módulo Créditos pendentes/usados/disponíveis | N/A (nunca existiu) | quebrado | — | — | — | PENDENTE (reconstruir só a intenção: fluxo único de crédito) |

**Legenda de Status:** `PENDENTE` (aguardando OpenSpec) · `EM ESPECIFICAÇÃO` ·
`EM IMPLEMENTAÇÃO` · `PARIDADE` (implementado + QA aprovado) · `NÃO RECONSTRUIR`
(código morto em ambos os temas, decisão registrada) · `RETOMAR IDEIA` (o conceito é bom,
o código legado não é a base — ver backlog/decisão de arquitetura).

**2 itens já decididos como não-reconstrução** (LEG-RMA-016, LEG-RMA-034 — código morto
em ambos os temas) e **1 item como "retomar ideia, não código"** (LEG-RMA-035). Os
demais 45 itens aguardam OpenSpec.
