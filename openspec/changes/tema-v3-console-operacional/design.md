# Design - Tema V3 / Console Operacional Adaptativa

## Objetivo

Console Operacional Adaptativa: mesa orientada a fila, excecao, status e proxima
acao, produtiva no desktop e adaptada integralmente a tablet/telefone.

## Regra arquitetural

Tema muda identidade, disposicao, navegacao, densidade e experiencia. Tema nao
muda regra, permissao, informacao disponivel, acao possivel, tenant ou resultado.

## Arquitetura de informacao

Navegacao global por dominios:

- Dashboard;
- RMAs;
- Parceiros;
- Relatorios;
- Administracao;
- Perfil/Aparencia;
- Ajuda.

Dentro de RMAs, status viram filtros/vistas (Todos, Entrada, Recebidos,
Encaminhados, Aguardando credito, Concluidos, Arquivados).

## Informacao

Cada tela responde a "o que o usuario vai fazer com isto?". Detalhes secundarios
ficam abaixo ou em secoes do contexto. Capacidades nunca somem sem decisao
explicita no mapa de telas.

## Navegacao

- Desktop: rail recolhivel a esquerda, conteudo com largura por natureza;
- Telefone: header com drawer;
- Contexto: breadcrumb/cabecalho da area quando necessario.

## Componentes candidatos

AppShell, NavigationRail, MobileDrawer, PageHeader, ContextHeader, ActionBar,
MetricCard, QueueCard, AlertPanel, FilterBar, SearchBox, DataTable,
MobileRecordCard, FormSection, FieldGroup, StatusBadge, PriorityBadge,
EmptyState, ValidationSummary, Timeline, ConfirmDialog, Drawer, Modal e
Toast/Flash. So entram com reuso ou contrato transversal comprovado.

## Mobile

- Base escrita para telefone, com `min-width` apenas para ampliar;
- alvo minimo de 44px em acionaveis;
- listagem em cartoes equivalentes no mobile;
- formulario em 1 coluna (2 colunas so com relacao real no desktop);
- salvar/cancelar persistentes.

## Acessibilidade

- navegacao por teclado e foco visivel;
- ARIA para disclosure, dialog, drawer, tabs e mensagens;
- cor nunca como unico indicador;
- estados de erro no topo e junto ao campo;
- progressive enhancement.

## Selecao de tema

No futuro: `TemaPreferido` N-ario e selecao explicita V1/V2/V3 no Perfil. Nesta
rodada somente documentar; nao alterar enum/controller.

## Stack

Recomendacao documental do T3-SPIKE-01: Sass/CSS semantico moderno (BEM-like com
tokens), com Tailwind 4 mantido como candidato. Decisao final no gate de
implementacao.

## Largura por natureza

- Formulario: largura de leitura/edicao confortavel;
- Composicao: dashboard/detalhe;
- Dados: tabelas/relatorios.

## Estrategia incremental

V3 nasce oculto e e construido em tarefas pequenas:

1. shell base;
2. navegacao;
3. dashboard;
4. RMAs listagem;
5. RMA detalhe;
6. RMA formularios;
7. Parceiros;
8. Usuarios/admin;
9. Relatorios;
10. secundarias;
11. selecao explicita de tema;
12. mobile/browser;
13. acessibilidade;
14. performance.

## Matriz suportada

Toda capacidade atual precisa ter destino V3 documentado no mapa de telas antes
de qualquer exposicao publica. V3 so entra no seletor com matriz, E2E, mobile,
desktop, acessibilidade, Policy, tenant, erros/vazios, performance e build
verdes.
