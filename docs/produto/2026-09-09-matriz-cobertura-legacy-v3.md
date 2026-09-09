# Matriz de cobertura - superfícies/rotas Legacy × V3

Data: 2026-09-09. Legacy `f83542c` (read-only); V3 HEAD `a254d58`/origin `75c110d`.
Uma linha por superfície Legacy real. Objetivo: nenhuma página funcional do Legacy
desaparece silenciosamente na V3.

| Superfície | Fonte Legacy | V3 rota/controller | V3 view (V1/V2) | Ação HTTP | Policy | Equivalente? | Runtime V3 |
|---|---|---|---|---|---|---|---|
| Login | root + 14.6.1 + 15.8.1 | `login` `SessaoController` | `identidade.login` | GET/POST | guest | sim | sim |
| Home V1 | `14.6.1/page/index` | `rmas.index` | `temas.v1.rma.index` | GET | viewAny | sim | sim |
| Home V2 | `15.8.1/page/inicio` | `v2.rmas.index` | `temas.v2.rma.index` | GET | viewAny | sim | sim |
| Novo RMA | V1 menujs-top/novo; V2 novo_rma | `rmas.create/store` | V1 form inline + `/create`; V2 | GET/POST | create | sim | sim |
| Pesquisar | V1 localizar; V2 pesquisar | `rmas.index` com tipo/campo | V1 `_form_localizar`; V2 | GET | viewAny | sim | sim |
| Entrada | V1 page/entrada; V2 aba | `rmas.entrada`; V2 index | V1 listagem; V2 `_tabela_entrada` | GET | viewAny | sim | sim |
| Recebido | V2 page/recebido | V2 index | `_tabela_recebido` | GET | viewAny | sim | sim |
| Encaminhado | V1/V2 | `rmas.encaminhados`; V2 index | V1/V2 | GET | viewAny | sim | sim |
| Concluído | V1/V2 | `rmas.concluidos`; V2 index | V1/V2 | GET | viewAny | sim | sim |
| Detalhe RMA | V1 page/detalhes; V2 page/rma | `rmas.show` | V1/V2 show | GET | view | sim | sim |
| Editar RMA | V1/V2 | `rmas.edit/update` | V1/V2 | GET/PUT | update | sim | sim |
| Clientes | V2 page/clientes | `parceiros.clientes` | V1/V2 | GET | viewAny | sim | sim |
| Novo cliente | V2 novo_cliente | `create/store` | `_form` | GET/POST | create | sim | sim |
| Detalhe/editar cliente | V2 ver_cliente | `edit/update` | `_form` | GET/PUT | update | sim | sim |
| Fornecedores | V2 page/fornecedores | `parceiros.fornecedores` | V1/V2 | GET | viewAny | sim | sim |
| Novo/editar fornecedor | V2 | V3 | `_form` | GET/POST/PUT | create/update | sim | sim |
| Fabricantes | V2 page/fabricantes | `parceiros.fabricantes` | V1/V2 | GET | viewAny | sim | sim |
| Assistências | V2 page/assistencia_tecnicas | `parceiros.assistencias-tecnicas` | V1/V2 | GET | viewAny | sim | sim |
| Detalhe de parceiro (4) | V1 page/* + V2 ver_* | **sem rota show** | - | - | - | não | gap C (P5) |
| Créditos | V1 créditos; V2 creditos | `rmas.credito.index/marcar` | V1/V2 shell | GET/POST | viewAny/update | sim | sim |
| RCD | V1 relatorios RCRD; V2 | `rmas.relatorios.rcd` | shell UI-03 | GET | viewAny | sim | sim |
| RPEC | V1/V2 | `rpec` | shell UI-03 | GET | viewAny | sim | sim |
| RMPE | V1/V2 | `rmpe` | shell UI-03 | GET | viewAny | sim | sim |
| Alertas | V2 page? | `rmas.alertas` | shell UI-04 | GET | viewAny | sim | sim |
| Histórico RMA | V2 logs_de_modificacao | `rmas.historico.index` | shell UI-04 | GET | gerenciar | sim | sim |
| Histórico acesso | V2 logs_de_autenticacao | `historico-de-acesso` | shell UI-04 | GET | gerenciar | sim | sim |
| Frete Porto Alegre | V2 | `rmas.logistica.frete-porto-alegre` | shell UI-04 | GET | viewAny | sim | sim |
| Boletins | V2 | `rmas.logistica.boletins-relacionados` | shell UI-04 | GET | view | sim | sim |
| Controle V1 | V1 page/controle | `rmas.controle.index` | V1 | GET | gerenciar | sim | sim (UI-05) |
| Usuários | V1/V2 | `identidade.usuarios.*` | V1/V2 | GET/PUT/POST | gerenciar | sim | sim |
| Perfil | V1/V2 | `identidade.perfil.*` | V1/V2 | GET/PUT | auth | sim | sim |
| Anotações V2 | V2 page/anotacoes | perfil (sem página dedicada) | V1/V2 perfil | GET/PUT | auth | parcial | gap C |
| Avisar alguém | V2 avisar/{id} | sem rota V3 | - | GET/POST | ? | não | investigar (EXT-003) |
| Enviar e-mail | V2 enviar_email/{id} | sem rota V3 | - | GET/POST | ? | não | investigar (EXT-004) |
| Representantes | V2 page/representantes | campo `representante` apenas | - | - | ? | parcial | investigar (EXT-006) |
| Pomodoro | V2 rota morta | - | - | - | - | não (morto) | J |
| Trocar tema V1 | V1 menuright | `POST tema.alternar` | sem item V1 | POST | auth | parcial | gap D (P1) |

Resumo: 38 superfícies mapeadas; 35 com equivalente V3; 1 parcial (anotações);
2 fluxos de e-mail avisar/enviar e representantes requerem investigação; 1 rota morta
(pomodoro). Detalhe por fluxo no mapa `2026-09-09-mapa-fluxos-legado-v3.md`.
