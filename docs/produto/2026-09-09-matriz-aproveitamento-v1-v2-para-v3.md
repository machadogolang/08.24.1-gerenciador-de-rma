# Matriz de aproveitamento V1 x V2 para o Tema V3

Data: 2026-09-09. Frente: EVO-UX-001 - Console Operacional Adaptativa.

Colunas: V1 (evidencia do tema 14.6.1), V2 (evidencia do tema 15.8.1), problema
atual, o que aproveitar, V3 proposto, motivo e classificacao.

Legenda: MANTER = manter no V3; ADAPTAR = transformar para o contexto novo;
SUBSTITUIR = substituir por solucao nova; DESCARTAR = nao reproduzir; INVESTIGAR =
precisa de prova antes de decidir.

## Matriz

| Capacidade | V1 | V2 | Problema atual | O que aproveitar | V3 proposto | Motivo | Classificacao |
|---|---|---|---|---|---|---|---|
| Shell | fixo 984px, paginas completas | 1190px com abas e sidebar | composicoes incomensiveis | densidade e contexto | shell com largura por natureza | leitura/dados/formulario tem medidas diferentes | ADAPTAR |
| Menu principal | acesso rapido por filas/modulos | abas por status e menu dropdown | navegacao por estados, nao por dominio | acesso direto + contexto de abas | navegacao por dominios com status como filtros | usuario pensa em dominio, depois fila | SUBSTITUIR |
| Dashboard | contadores, anotacao, avisos | inicio/pesquisar e centro de avisos | blocos nao respondem a proxima acao | contadores acionaveis e alertas | mesa com busca, filas, alertas e atividade | cada bloco responde a proxima acao | ADAPTAR |
| Busca | localizar amplo | pesquisar com breadcrumb | espalhada e parcial | busca ampla por campos | busca global/lancador | reduzir navegacao | MANTER/ADAPTAR |
| Filas | atalhos no topo e contadores | abas por status | fila nao e acionavel como mesa | listas por status | filas no dashboard e area RMAs | fila e instrumento de trabalho | ADAPTAR |
| Novo RMA | painel inline rapido | aba novo_rma + link | formulario historico longo | inicio rapido sem perder contexto | fluxo dedicado com secoes | proxima acao primaria | ADAPTAR |
| Editar RMA | tabela vertical | formulario Bootstrap | campos dispersos e risco de apagar estado | formulario em grid | formulario em secoes, 2 colunas quando ha relacao | significado antes de posicao | SUBSTITUIR |
| Detalhe RMA | dados operacionais completos | dados em aba rma | proxima acao no fim da pagina | numero/status/transicoes | cabecalho operacional + secoes | proxima acao visivel no topo | ADAPTAR |
| Acoes de ciclo | botoes por status | formularios por status | apresentacao minima e distribuida | acoes governadas por policy/status | action bar contextual por status | regra nao muda, apresentacao contextualiza | ADAPTAR |
| Parceiros | cadastro e detalhe | CRUD com ver/editar | formularios com larguras por acidente | contato/endereco/comercial | lista/busca/detalhe + form em secoes | padronizar geometria por contrato | ADAPTAR |
| Usuarios | tabela simples | tabela com acoes empilhadas | dois password por linha | dados de papel/ultimo acesso | listagem com acoes contextuais | remover acao permanente da tabela | SUBSTITUIR |
| Controle V1 | painel administrativo misto | logs/historico como controle | responsabilidades heterogeneas juntas | capacidades administrativas | decompor em areas e hub | arquivar/ajuda/senha nao sao mesma acao | SUBSTITUIR |
| Relatorios | lista de relatorios | menu com relatorios | tres rotas isoladas | filtros e impressao limpa | hub relatorios | mesmo conceito, um ponto de entrada | MANTER/ADAPTAR |
| Alertas | centro de avisos | centro de avisos | listas genericas em paginas isoladas | regras de alerta reais | dashboard + pagina dedicada | operador age no alerta | ADAPTAR |
| Historicos | historico RMA/controle | logs de modificacao/acesso | rotas isoladas de auditoria | auditoria por registro | historico no detalhe e admin | contexto reduz navegacao | ADAPTAR |
| Logistica | frete no menu V1 | frete/boletins V2 | rotas isoladas | boletins ligados ao RMA | operacao/logistica e detalhe | conteudo pertence ao contexto | ADAPTAR |
| Perfil | senha e anotacao | senha e anotacao | anotacao como quadro/pagina | campos de identidade | perfil com senha, aparencia e preferencias | tema sai da operacao | ADAPTAR |
| Troca de tema | V1 e V2 | V1 e V2 | toggle binario | selecao explicita N-aria | seletor V1/V2/V3 no perfil | 3 temas exigem escolha, nao toggle | SUBSTITUIR |
| Erros | mensagens por tela | mensagens por tela | sem contrato transversal | mensagens por formulario | validation summary + erro no campo | padrao de feedback | SUBSTITUIR |
| Vazios | mensagens simples | mensagens simples | nao diferencia vazio base de filtro | texto simples | EmptyState com proxima acao | orientar retomada do trabalho | ADAPTAR |
| Confirmacoes | confirmacoes frágeis/JS | removais imediatos | sem confirmacao acessivel | evitar dados perdidos | ConfirmDialog acessivel | acao perigosa exige confirmacao | SUBSTITUIR |
| Mobile | nao existe (fixo) | breakpoints por faixa | 390px ignorado em V2 | nenhuma | mobile-first integral | requisito de produto | SUBSTITUIR |

## Uso deste documento

Esta matriz alimenta `2026-09-09-mapa-telas-tema-v3.md` e o OpenSpec
`tema-v3-console-operacional`. Nenhuma linha vale como decisao final sem runtime ou
fonte que comprove o problema apontado.
