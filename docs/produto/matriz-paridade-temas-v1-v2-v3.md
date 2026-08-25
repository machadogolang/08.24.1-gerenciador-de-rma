# Matriz viva — Legado × Tema 1 × Tema 2 × Tema 3

Data: 2026-08-25. Estado: **preliminar e executável**; não é declaração de conclusão.

Esta matriz complementa `paridade-v2-v3.md`: aquela rastreia 48 capacidades históricas;
esta mede também descobribilidade, composição de tela, ações e estados por tema. `sim`
significa capacidade comprovada; `parcial` exige tarefa; `planejado` não equivale a
implementado.

## Regra arquitetural

Tema muda identidade, disposição, navegação, densidade e experiência visual. Tema não
muda regra, permissão, informação disponível nem ação possível. Uma capacidade só fecha
quando todos os temas suportados cumprem o contrato previsto. Tema 3 fica oculto até
atingir paridade integral.

## Matriz funcional e de apresentação

| Capacidade | Legado | Tema 1 atual | Tema 2 atual | Tema 3 | Situação / tarefa |
|---|---|---|---|---|---|
| Login/logout | sim, superfícies assimétricas | gateway comum | gateway comum | gateway comum | MANTER unificação; login não é exclusivo de tema |
| Seleção de tema | V1/V2 | alternância binária | alternância binária | planejado | T3-05: seleção explícita N-ária |
| Usuários/papéis/reset | sim | parcial | parcial | planejado | ARQ-003; PAR-USR-001; investigar criação/exclusão |
| Perfil/senha/anotação | sim | sim | sim | planejado | contrato comum; anotação pode mudar de posição |
| Menu principal | filas/ações/módulos | parcial: 3 itens | parcial: abas sem módulos secundários | planejado | PAR-V1-001; PAR-NAV-001; T3-02 |
| Dashboard/avisos | filas, alertas, atalhos | alertas+contadores+anotação, sem filas acionáveis | alertas+abas, sem painel lateral | planejado | UX-01; PAR-V1-002; PAR-V2-003 |
| Busca por texto | ampla, cerca de 23 campos | parcial: 6 campos | parcial: 6 campos | planejado | PAR-RMA-003; contrato e testes |
| Busca por serial | sim | `sn` | `sn` | planejado | confirmar PN/SNID em INV-LEGADO-RMA-001 |
| Busca por NF | compra/venda/remessa | incorreta: consulta `os` | incorreta: consulta `os` | planejado | ARQ-004 / PAR-RMA-001 |
| Busca por número | número histórico | ausente | ausente | planejado | PAR-RMA-002/004 |
| Filtro por status | filas | ausente como ação | abas vazias sem busca | planejado | PAR-V1-001; FRONT-002/PAR-V2-001 |
| Filtro por solução | sim no V1 legado | ausente | ausente | planejado | PAR-V1-002; avaliar apresentação equivalente nos demais |
| Novo RMA | completo | 11 campos | 11 campos; aba só linka página | planejado | PAR-RMA-005/007; PAR-V2-002 |
| Editar RMA | completo | 11 campos; risco de apagar estado | idem | planejado | ARQ-001; INV-LEGADO-RMA-001 |
| Detalhe RMA | dados operacionais/históricos | parcial | parcial | planejado | PAR-RMA-004/005/006/009 |
| Receber | sim | sim, visual mínimo | sim, visual mínimo | planejado | FRONT-003; contrato de ação comum |
| Encaminhar | destino validado | tipo+ID cru | tipo+ID cru | planejado | ARQ-005; UX-003 |
| Concluir | solução+lancamento/estoque | só solução | só solução | planejado | PAR-RMA-008; confirmar regra por versão |
| Arquivar/reverter | sim, com diferenças históricas | sim | sim | planejado | preservar regra consolidada; ARQ-001 |
| Registrar solução | sim | sim | sim | planejado | ARQ-001; FRONT-003 |
| Alertas | regras e telas integradas | regras sim; tela isolada | regras sim; tela isolada | planejado | FRONT-001/003; PAR-VIS-SEC-001 |
| Crédito | sim | rota isolada, pouco descobrível | idem | planejado | PAR-NAV-001; FRONT-003 |
| RCD/RPEC/RMPE | sim | rotas isoladas | rotas isoladas | planejado | PAR-NAV-001; FRONT-003 |
| Histórico de RMA | sim | rota isolada | rota isolada | planejado | PAR-NAV-001; FRONT-003 |
| Histórico de acesso | sim | rota isolada | rota isolada | planejado | PAR-NAV-001; FRONT-003 |
| Frete Porto Alegre | ativo no V2 legado | rota isolada | rota isolada | planejado | PAR-NAV-001; FRONT-003 |
| Boletins relacionados | sim | não descobrível no detalhe | idem | planejado | PAR-RMA-010 |
| CRUD parceiros | V1 leitura/V2 CRUD | CRUD | CRUD | planejado | paridade atual; rever contrato útil do legado |
| Detalhe/RMAs do parceiro | sim | ausente | ausente | planejado | PAR-PARCEIRO-001 |
| Remover parceiro | confirmação histórica | imediato | imediato | planejado | UX-002 / UX-LEGADO-PARCEIRO-001 |
| Permissões na apresentação | guardas irregulares | ações aparecem e retornam 403 | idem | planejado | UX-001 / UX-LEGADO-PERM-001 |
| Flash/validação | por tela | parcial; sucesso pode ficar oculto | parcial | planejado | PAR-V1-MSG-001; UX-004 |
| Estado vazio/filtro vazio | mensagens simples | não distingue base de filtro | idem | planejado | UX-004 |
| Confirmações/modais | confirmações frágeis | ausentes | ausentes | planejado acessível | UX-002/005 |
| Breadcrumb/contexto | V2 contextual | ausente | classe visual sem trilha contextual | planejado | PAR-V2-004 |
| Paginação | não comprovada no núcleo | ausente em listas principais | ausente | planejado após contrato | UX-04; não classificar como legado sem evidência |
| Responsividade | V1 fixo; V2 por faixas | fixo fiel | 390 ignorado em teste | adaptativo | PAR-QA-003; T3-09/20 |
| Acessibilidade | não comprovada | gaps de teclado/semântica | gaps de teclado/semântica | requisito | FRONT-005; UX-07; T3-20 |

## Linhas históricas especiais

| Item | Classificação | Decisão |
|---|---|---|
| `LEG-RMA-016` estado retornou | DESCARTAR / dado real ainda investigável | não reconstruir código morto; se surgir no dado, registrar anomalia |
| `LEG-RMA-034` alias Autorizada | DESCARTAR | código morto comprovado |
| `LEG-RMA-035` parceiro unificado | RETOMAR IDEIA | Trilha B, não copiar implementação |
| `LEG-RMA-048` subrotas de crédito | SUBSTITUIR | manter fluxo único funcional, descartar rotas quebradas |
| Lightbox2/AdminLTE residual | INVESTIGAR | `C-03/C-04`; não remover por aparência |

## Gate por linha

Para fechar uma linha:

1. comportamento/contrato comprovado por versão histórica quando aplicável;
2. teste funcional ou roteiro executado por tema suportado;
3. informação, ação, policy, rota/método e estados equivalentes;
4. diferença apenas de composição/visual explicitamente consciente;
5. Tema 3 só muda de `planejado` para `sim` depois do gate integral e antes de aparecer
   no seletor.
