# Refinamento EVO-UX-001 - Tema V3 / Console Operacional Adaptativa

Data: 2026-09-09. Natureza: addendum/refinamento da direcao do Tema V3, sem
reescrever os marcos historicos. Referencia e evolui:

- `docs/arquitetura/INV-RMA-08-tema-v3-mobile-first.md` (marco historico);
- `docs/investigacoes-pendente/INV-RMA-10-arquitetura-front-paridade-temas.md`
  (refinamento posterior: Console Operacional Adaptativa);
- `docs/produto/backlog-evolutivo.md` EVO-UX-001.

Status: [R] REVISADO. Investigacao concluida; implementacao nao liberada.

> ATUALIZACAO 2026-09-10 - DIRECAO VISUAL: a direcao CLARA registrada
> originalmente para o V3 foi **SUPERADA**. O norte visual aprovado pelo dono
> passou a ser **Console Operacional Dark** (referencia conceitual: linguagem
> visual do debug moderno do Laravel, sem copiar HTML/assets). Documento de
> decisao: `docs/arquitetura/2026-09-10-direcao-visual-v3-console-dark.md`.
> A arquitetura de informacao deste documento continua valida.

## 0. Objetivo de produto

O Tema V3 e um NOVO produto visual, nao uma reforma de V1 ou de V2:

- V1 preserva o CellSystem 14.6.1 (identidade, densidade e fluxos de pagina);
- V2 preserva o 15.8.1/15.9.7 (abas, contextualizacao e composicao de formulario);
- V3 estuda o que V1 e V2 ensinam sobre uso real e cria uma experiencia nova,
  preservando capacidade, regra, permissao, informacao, tenant e resultado.

V3 pode reorganizar navegacao, hierarquia, agrupamento, densidade, componentes,
ordem de informacao e apresentacao em desktop/mobile. Nao pode mudar o que cada
acao faz, quem pode executa-la, que dado existe ou o resultado funcional.

## 1. Direcao confirmada (sem reinventar INV-RMA-08/10)

O conceito reconciliado permanece:

- mesa de trabalho orientada a fila, excecao, status e proxima acao;
- produtividade e densidade no desktop;
- adaptacao integral para tablet e telefone;
- rail recolhivel no desktop e header/drawer no telefone;
- dashboard com busca rapida, novo RMA, contadores acionaveis, alertas e filas;
- listagem densa no desktop e cartoes equivalentes no mobile;
- formulario em secoes, erro no topo e junto ao campo, salvar/cancelar
  persistentes;
- detalhe com numero, status, prioridade, proxima transicao e camadas de dados;
- estados vazio, filtro vazio, sucesso, validacao, 403/404/500;
- acessibilidade e progressive enhancement.

Nesta rodada nao se implementa V3. O entregavel e especificacao navegaveis.

## 2. Nova direcao do dono: o melhor de V1 e V2

Para cada padrao de V1/V2, a auditoria registra: que problema o padrao resolvia,
qual a solucao atual, e a classificacao:

- MANTER NO V3;
- ADAPTAR;
- SUBSTITUIR;
- DESCARTAR;
- INVESTIGAR.

Hipoteses iniciais (a confirmar por evidencia de codigo/runtime):

- V1 contribui densidade, acesso rapido, atalhos e eficiencia do usuario
  experiente;
- V2 contribui contextualizacao, organizacao por abas, composicao de formularios e
  separacao mais clara de areas.

## 3. Auditoria de arquitetura de informacao

Antes da posicao do botao, a pergunta e: "o que o usuario quer fazer?". Para cada
tela:

- por que esta informacao esta aqui?
- esta acao pertence a esta tela?
- e primaria, secundaria ou administrativa?
- e usada com frequencia?
- qual a proxima acao provavel?
- quais informacoes precisam aparecer juntas?
- o que e detalhe que pode ficar abaixo?
- o usuario precisa sair da tela para continuar o fluxo?
- isto e navegacao global ou contexto atual?

## 4. Exemplo central: Controle V1

A tela `resources/views/temas/v1/rma/controle.blade.php` mistura hoje:

- cadastrar fornecedor/fabricante/assistencia;
- arquivar RMA;
- avisos sobre exclusao de RMA/usuario (decisao pendente);
- ajuda/procedimento;
- RMAs arquivados;
- mudar a propria senha.

No V3, a hipotese de decomposicao e:

- cadastros -> Parceiros;
- RMA arquivado -> RMAs, vista "Arquivados";
- usuarios -> Administracao/Usuarios;
- mudar senha -> Perfil;
- ajuda -> Ajuda;
- configuracoes/admin -> Administracao.

O antigo "Controle" pode virar hub administrativo ou deixar de existir como tela
central, desde que todas as capacidades continuem descobriveis. Decisao so apos o
mapa de telas e a matriz de aproveitamento.

## 5. Navegacao global

Hipotese para avaliar:

    Dashboard
    RMAs
    Parceiros
    Relatorios
    Administracao

Dentro de RMAs: Todos, Entrada, Recebidos, Encaminhados, Aguardando credito,
Concluidos, Arquivados. Os status podem virar tabs, filtros salvos, chips,
segmentos ou sidebar secundaria, sem fixar antes da investigacao.

## 6. Dashboard

Blocos candidatos: busca rapida, novo RMA, filas, alertas reais, atividade
recente e atalhos contextuais. Contadores acionaveis apenas quando fizer sentido.
Nao criar painel decorativo com 25 cards.

## 7. RMA

- Listagem: contrato unico com tabela densa no desktop e cartao estruturado no
  mobile (numero, status, descricao, parceiro, data, prioridade, proxima acao).
- Detalhe: cabecalho operacional (numero, status, prioridade, empresa, proxima
  acao) + secoes para resumo, produto, origem, destinatario, fiscal, solucao,
  credito, logistica, boletins e historico.
- Novo/editar: agrupar por significado: identificacao, origem/parceiros, fiscal,
  operacao, observacoes. Desktop usa 2 colunas somente quando ha relacao real.

## 8. Parceiros

V3 usa lista, busca, filtros, novo, detalhe, editar e RMAs relacionados. Formulario
em secoes quando os campos existem: identificacao, contato, endereco, comercial e
observacoes/garantia. Nao preservar campo largo/estreito por acidente historico.

## 9. Usuarios e administracao

Listagem com nome, email, empresa, papel, status, ultimo acesso e acoes. Troca de
papel e reset de senha ficam como acao contextual (pagina pequena/modal/drawer),
nao como dois password permanentes por linha. Acoes impossiveis por Policy nao sao
oferecidas.

## 10. Relatorios

Hub "Relatorios" com lista de RCD/RPEC/RMPE, descricao curta, filtros e acesso ao
relatorio. Evita tres itens globais para o mesmo conceito. Preserva impressao limpa.

## 11. Alertas, historicos e logistica

Alertas podem morar no dashboard e em pagina dedicada; historico de RMA no
detalhe; historico de acesso em Administracao/Seguranca; frete em operacao;
boletins no detalhe. Capacidades nao somem, apenas mudam de lugar.

## 12. Perfil e selecao de tema

V3 adiciona a terceira opcao. O mecanismo binario V1<->V2 deixa de ser adequado.
Nesta rodada so se documenta o fluxo: selecao explicita de V1/V2/V3 em Perfil ou
seletor de aparencia. Nao alterar enum/controller.

## 13. Componentes candidatos

Inventario de reuso real: AppShell, NavigationRail, MobileDrawer, PageHeader,
ContextHeader, ActionBar, MetricCard, QueueCard, AlertPanel, FilterBar,
SearchBox, DataTable, MobileRecordCard, FormSection, FieldGroup, StatusBadge,
PriorityBadge, EmptyState, ValidationSummary, Timeline, ConfirmDialog, Drawer,
Modal e Toast/Flash. Componente so existe se houver reuso ou contrato transversal.

## 14. Largura por natureza

Inspirado em INV-RMA-08 e no CONAHOM, a largura depende da natureza da tela:

- formulario -> largura de leitura/edicao confortavel;
- composicao -> dashboard/detalhe;
- dados -> tabelas/relatorios.

Valores so depois de medir runtime.

## 15. Mobile-first e densidade desktop

Base de escrita: telefone, com `min-width` apenas para ampliar. Alvo minimo de
44px em elementos acionaveis. Mobile-first nao significa desktop desperdicado:
desktop continua denso e produtivo.

## 16. T3-SPIKE-01 - Tailwind 4 x CSS semantico

Comparacao documental obrigatoria antes de decidir stack:

1. bundle e impacto Vite;
2. isolamento V1/V2 (sem vazamento CSS);
3. legibilidade de Blade;
4. tokens e design system;
5. reuso e manutencao;
6. curva do projeto;
7. mobile-first;
8. componentizacao;
9. risco de vazamento CSS;
10. integracao Vite.

Resultado completo em `2026-09-09-spike-t3-tailwind-vs-css-semantico-v3.md`.

## 17. Gate

[GATE-PENDENTE] Tema V3 ainda nao implementado e nao selecionavel. A publicacao
depende de: frente atual de UI/paridade segura, matriz funcional sem lacuna,
OpenSpec aprovado, implementacao oculta completa, E2E, mobile, desktop,
acessibilidade, Policy, tenant, erros/vazios, performance e build verdes.
