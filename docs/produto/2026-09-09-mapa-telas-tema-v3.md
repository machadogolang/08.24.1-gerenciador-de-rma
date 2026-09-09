# Mapa de telas do Tema V3

Data: 2026-09-09. Base: inventario atual de views V1/V2 e matriz de cobertura
`2026-09-09-matriz-cobertura-legacy-v3.md`. Status da especificacao usa
`[ ]`/`[R]`/`[x]`. Nenhuma capacidade some sem decisao explicita.

## Legenda

- Nav: grupo de navegacao V3 (Dashboard, RMAs, Parceiros, Relatorios,
  Administracao, Perfil, Ajuda, Operacao).
- Layout: DADOS (largo), COMPOSICAO (dashboard/detalhe), FORMULARIO (estreito
  confortavel).
- Spec: [x] especificado nesta rodada, [R] parcial/investigando, [ ] nao
  especificado.

## Mapa

| Capacidade | Origem V1 | Origem V2 | Destino V3 | Nav | Layout | Componente | Mobile | Acao primaria | Acoes secundarias | Estado vazio | Policy | Spec |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Login/logout | gateway | gateway | gateway comum | Autenticacao | FORMULARIO | Card de login | 1 coluna | Entrar | - | - | guest/auth | [x] |
| Trocar tema | toggle V1 | toggle V2 | seletor V1/V2/V3 | Perfil/Aparencia | FORMULARIO | Select de aparencia | drawer | Salvar | - | - | auth | [x] |
| Dashboard | home V1 | inicio V2 | Dashboard | Dashboard | COMPOSICAO | MetricCard/QueueCard/AlertPanel | cartoes | Abrir fila/proxima acao | Novo RMA, buscar, alerta | EmptyState dashboard | viewAny | [x] |
| Busca rapida/global | localizar | pesquisar | Dashboard/RMAs | RMAs | DADOS | SearchBox + DataTable | cartoes | Buscar | Filtros | EmptyState filtro | viewAny | [R] |
| Todos RMAs | busca vazia | busca | RMAs/Todos | RMAs | DADOS | DataTable + FilterBar | MobileRecordCard | Abrir RMA | Novo, filtros, exportar | EmptyState base | viewAny | [R] |
| Entrada | listagem | aba entrada | RMAs/Entrada | RMAs | DADOS | QueueCard/DataTable | cartoes | Receber | Ver, editar | EmptyState fila | viewAny | [R] |
| Recebidos | - | aba recebido | RMAs/Recebidos | RMAs | DADOS | DataTable | cartoes | Encaminhar | Ver, editar | EmptyState fila | viewAny | [R] |
| Encaminhados | listagem | aba encaminhado | RMAs/Encaminhados | RMAs | DADOS | DataTable | cartoes | Concluir | Ver, editar | EmptyState fila | viewAny | [R] |
| Aguardando credito | listagem | aba/credit | RMAs/Aguardando credito | RMAs | DADOS | DataTable | cartoes | Marcar credito | Ver | EmptyState fila | viewAny | [R] |
| Concluidos | listagem | aba concluido | RMAs/Concluidos | RMAs | DADOS | DataTable | cartoes | Abrir detalhe | Relatorios | EmptyState fila | viewAny | [R] |
| Arquivados | Controle V1 | - | RMAs/Arquivados | RMAs | DADOS | DataTable | cartoes | Reverter | Ver | EmptyState fila | gerenciar | [R] |
| Novo RMA | painel inline | aba novo_rma | RMAs/Novo | RMAs | FORMULARIO | FormSection | 1 coluna | Salvar | Cancelar | - | create | [x] |
| Editar RMA | tabela | form | RMAs/{id}/Editar | RMAs | FORMULARIO | FormSection | 1-2 colunas | Salvar | Cancelar | - | update | [x] |
| Detalhe RMA | pagina detalhes | aba rma | RMAs/{id} | RMAs | COMPOSICAO | PageHeader + Tabs/Timeline | secoes/accordion | Proxima transicao | Editar, historico, boletins | - | view | [x] |
| Acoes ciclo | botoes | formularios | ActionBar no detalhe | RMAs | COMPOSICAO | ActionBar | sticky bottom | Receber/Encaminhar/Concluir | Arquivar, solucao | - | policy/status | [x] |
| Parceiros lista | lista | lista | Parceiros | Parceiros | DADOS | DataTable/SearchBox | cartoes | Novo | Ver, editar, remover | EmptyState base | viewAny | [x] |
| Parceiro novo/editar | formulario | formulario | Parceiros/{tipo}/novo|editar | Parceiros | FORMULARIO | FormSection | 1-2 colunas | Salvar | Cancelar | - | create/update | [x] |
| Parceiro detalhe | page | ver_* | Parceiros/{tipo}/{id} | Parceiros | COMPOSICAO | ContextHeader | secoes | Editar | RMAs relacionados | - | view | [x] |
| Usuarios lista | lista | lista | Administracao/Usuarios | Administracao | DADOS | DataTable | cartoes | Novo usuario | Trocar papel, reset | EmptyState | gerenciar | [R] |
| Usuario novo/editar | - | subp | Administracao/Usuarios/{id} | Administracao | FORMULARIO | FormSection | 1 coluna | Salvar | Cancelar | - | gerenciarUsuario | [R] |
| Reset senha | controle | subp | Administracao (modal/pagina) | Administracao | FORMULARIO | ConfirmDialog/Drawer | drawer | Resetar | Cancelar | - | gerenciarUsuario | [R] |
| Perfil/senha | perfil | perfil | Perfil | Perfil | FORMULARIO | FormSection | 1 coluna | Salvar | - | - | auth | [x] |
| Anotacao pessoal | quadro V1 | pagina V2 | Perfil/Anotacao | Perfil | FORMULARIO | textarea | 1 coluna | Salvar | - | EmptyState | auth | [x] |
| Credito | listagem | painel | RMAs/Credito (vista) | RMAs | DADOS | DataTable | cartoes | Marcar credito | Ver | EmptyState | viewAny | [R] |
| Relatorios hub | lista | menu | Relatorios | Relatorios | DADOS | cards + filtros | cards | Abrir relatorio | - | EmptyState | viewAny | [x] |
| RCD/RPEC/RMPE | relatorios | relatorios | Relatorios/{rcd,rpec,rmpe} | Relatorios | DADOS | Report view/print | responsivo | Imprimir | Filtrar | EmptyState | viewAny | [x] |
| Alertas | centro avisos | centro avisos | Dashboard + Alertas | Dashboard | COMPOSICAO | AlertPanel | cartoes | Abrir/alerta | Ver RMA | EmptyState | viewAny | [R] |
| Historico RMA | historico | logs | Detalhe RMA | RMAs | COMPOSICAO | Timeline | secoes | - | - | EmptyState | view | [x] |
| Historico acesso | historico | logs | Administracao/Seguranca | Administracao | DADOS | DataTable | cartoes | - | - | EmptyState | gerenciar | [x] |
| Frete Porto Alegre | menu | operacao | Operacao/Frete | Operacao | DADOS | DataTable | cartoes | - | - | EmptyState | viewAny | [R] |
| Boletins | - | detalhe/rota | Detalhe RMA/Boletins | RMAs | COMPOSICAO | lista | secoes | Abrir boletim | - | EmptyState | view | [x] |
| Controle/hub admin | Controle V1 | - | Administracao (capacidades decompostas) | Administracao | COMPOSICAO | hub cards | drawer | Abrir area | - | - | gerenciar | [x] |
| Ajuda/procedimento | Controle V1 | - | Ajuda | Administracao/Ajuda | FORMULARIO | texto | secoes | - | - | - | auth | [x] |
| 403/404/500 | pagina erro | pagina erro | ErrorState global | Global | FORMULARIO | EmptyState/ErrorState | 1 coluna | Voltar | - | - | - | [x] |
| Flash/sucesso/validacao | por tela | por tela | Toast/ValidationSummary | Global | COMPOSICAO | Toast + form error | topo | - | - | - | - | [x] |

## Decisoes explicitas de destino

- Controle V1 deixa de ser tela central: cada capacidade tem destino proprio
  (Parceiros, RMAs, Administracao, Perfil, Ajuda).
- Relatorios viram hub; RCD/RPEC/RMPE continuam como relatorios individuais.
- Historico de RMA e boletins entram no detalhe do RMA.
- Alertas ficam no dashboard e em pagina dedicada.
- Troca de tema sai do fluxo operacional e vira preferencia do perfil.

## Referencias

- Matriz funcional: `docs/produto/matriz-paridade-temas-v1-v2-v3.md`.
- Fluxos: `docs/produto/2026-09-09-mapa-fluxos-legado-v3.md`.
- Cobertura: `docs/produto/2026-09-09-matriz-cobertura-legacy-v3.md`.
- Refinamento: `docs/arquitetura/2026-09-09-refinamento-evo-ux-001-tema-v3-console-operacional.md`.
