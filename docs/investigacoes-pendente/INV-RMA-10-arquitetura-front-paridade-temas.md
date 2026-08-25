# INV-RMA-10 — Arquitetura, front-end e paridade de temas

Data de abertura: 2026-08-25. Estado: **em execução**.

Esta investigação incorpora ao planejamento vigente a frente pedida para arquitetura,
front-end, paridade funcional, legado, UX, Tema 3 e evoluções. Não substitui `PLAN.md`,
`PLANO-ATAQUE.md` nem `docs/produto/checklist-master-v3.md`; os achados executáveis
estão indexados nesses documentos.

## Método e baseline

- Branch `main`, HEAD inicial `9e07f66`, working tree limpa e branch um commit à
  frente de `origin/main`.
- Planejamento principal e auxiliares lidos antes da auditoria.
- Código, testes e runtime prevaleceram sobre afirmações documentais.
- Três agentes fizeram auditorias somente leitura e separadas: arquitetura/front;
  layouts/legado; Tema 3/UX/evoluções.
- Baseline renovada: 310 testes, 608 assertions, sem falha. A suíte verde não cobre os
  cenários críticos abaixo.

## Parecer reconciliado

A arquitetura atual **ajuda parcialmente**: enums, regras, casos de uso e o repositório
do agregado RMA tornam o domínio explícito. Ela passa a adicionar complexidade quando
um objeto imutável com cerca de 30 campos é reconstruído manualmente em cada operação.
Esse custo não é estético: já produz perda de dados.

Decisão proporcional:

- manter o domínio explícito de RMA e a fronteira de escrita;
- preservar o estado do agregado por métodos nomeados/cópia segura centralizada;
- documentar Eloquent como read model pragmático onde já é usado;
- não criar repositories, DTOs ou controllers genéricos para todo CRUD;
- compartilhar contratos e comportamentos de apresentação, sem forçar HTML idêntico
  entre temas com composições historicamente diferentes.

## Achados críticos

### ARQ-001 — reconstruções do agregado apagam estado

Classificação: **CRÍTICO / CORREÇÃO / P0**.

`EditarRma` descarta a entidade encontrada e cria outra apenas com os campos do núcleo.
Receber, encaminhar, concluir, arquivar, reverter e registrar solução também omitem
campos introduzidos depois. `RmasEmBanco::paraArray()` persiste os defaults omitidos.
Consequências possíveis: status volta a Entrada; datas, solução e destinatário são
apagados; prioridade, marcação de estoque, notas fiscais, valor e crédito são zerados.

Evidências: `app/Rma/Dominio/Rma.php`, `app/Rma/Aplicacao/{EditarRma,ReceberRma,
EncaminharRma,ConcluirRma,ArquivarRma,ReverterRmaParaEntrada,RegistrarSolucao}.php` e
`app/Rma/Infraestrutura/RmasEmBanco.php`.

### ARQ-002 — dry-run não valida a migração nem reconcilia o destino

Classificação: **CRÍTICO / CORREÇÃO / P0**.

Os importadores fazem `continue` antes da tradução/persistência em `--dry-run`.
`ImportarRmas` não executa `traduzirLinha()`, portanto não detecta datas, status,
soluções ou destinatários anômalos. O relatório chama `$processados` de destino; em
dry-run e na segunda execução idempotente esse valor tende a zero mesmo com o destino
correto.

Decisão: separar `origem`, `planejado`, `criado`, `atualizado`, `ignorado` e total real
no destino; dry-run percorre toda tradução sem efeito externo. Bloqueia
`F10-DAD-04…09`.

### ARQ-003 — Supervisor pode escalar privilégio

Classificação: **CRÍTICO / SEGURANÇA / P0**.

A tela oculta SuperAdministradores do Supervisor, mas a autorização não considera o
alvo nem o papel pretendido. Por URL direta, Supervisor pode promover a si próprio a
SuperAdministrador, alterar outro SuperAdministrador ou resetar sua senha.

Evidências: `UsuarioController::update`, `ResetarSenhaDeUsuario` e `UserPolicy`.
Decisão segura: Supervisor não pode atribuir papel acima de Supervisor nem operar sobre
SuperAdministrador; a regra histórica fina continua rastreada quando não comprovada.

## Achados importantes de arquitetura

- **ARQ-004:** busca por nota fiscal ainda consulta `os`, embora o schema já tenha
  campos fiscais; número legado e solução também não são pesquisados.
- **ARQ-005:** IDs de destinatário não têm validação dependente do tipo; `Solucao::from`
  pode transformar entrada inválida em erro 500; RMA ausente em edição vira
  `RuntimeException`.
- **ARQ-006:** mutação, auditoria e notificação não formam uma unidade consistente;
  criar/editar dependem de `Auth` implícito.
- **ARQ-007:** a home executa aproximadamente 27 consultas base e materializa alertas;
  os 16 contadores do Tema 1 são calculados também no Tema 2.
- **ARQ-008:** a implementação real é híbrida: repositório no agregado/escrita e
  Eloquent como read model. O docblock que diz que `App\Models\Rma` é exclusivo da
  infraestrutura está incorreto.
- **ARQ-009:** controllers/validações de parceiros e views antigas têm duplicação ou
  estão órfãos, mas uma abstração CRUD genérica não se justifica agora.

## Achados importantes de front-end e UX

- **FRONT-001:** `ClasseDeAlerta::Urgente` e `SemGarantia` têm CSS/mapeamento, mas
  `Rma::classeDeAlerta()` não os retorna; a semântica visual esperada fica inalcançável.
- **FRONT-002:** as abas por status do Tema 2 particionam somente o resultado da busca;
  sem termo, todas ficam vazias apesar de haver RMAs.
- **FRONT-003:** alertas, crédito, relatórios, históricos e logística são documentos
  isolados, sem shell/navegação temática; ações de ciclo também não têm contrato visual.
- **FRONT-004:** `/` ainda retorna o scaffold `welcome` do Laravel e os `ExampleTest`
  apenas congelam o placeholder.
- **FRONT-005:** disclosure `.pmo` tem JS duplicado em V1/V2, usa `span` sem teclado nem
  `aria-expanded` e controla `style.display` diretamente.
- **FRONT-006:** existem views genéricas órfãs depois de `view_do_tema()`; remover só
  após prova de ausência de consumidores.
- **UX-001:** ações renderizam para perfis que receberão 403; aplicar apresentação
  consciente de policy sem mover autorização para o browser.
- **UX-002:** remoção de parceiro é imediata e sem confirmação.
- **UX-003:** encaminhamento pede tipo + ID numérico cru; substituir por seleção segura
  e validada, sem mudar a regra de negócio.
- **UX-004:** flash, validação, vazio, 403/404/500 e prevenção de duplo envio não têm
  contrato transversal.

## Paridade com o legado

A auditoria anterior provava principalmente que V1 e V2 atuais alcançavam as mesmas
rotas e campos. Isso não prova equivalência integral com 14.6.1/15.8.1.

Achados confirmados:

- busca histórica era ampla; NF real, número, solução e outros campos foram reduzidos;
- Tema 1 perdeu filas/menu e filtros de solução;
- Tema 2 preserva a aparência das abas, mas não seus conjuntos completos;
- formulário/detalhe exibem apenas parte dos campos operacionais já preservados;
- conclusão histórica envolve solução e lançamento/estoque, hoje reduzida à solução;
- parceiros tinham detalhe/relacionados e confirmação de exclusão;
- crédito, relatórios, auditoria e logística existem em rotas, mas não são descobertos
  pelos shells atuais;
- HTTP 200 ou presença de uma `div` não fecha paridade funcional.

A matriz viva está em `docs/produto/matriz-paridade-temas-v1-v2-v3.md`.

## Critério para comportamento legado

- **MANTER:** semântica ampla de busca; filas por status; capacidades operacionais;
  composição V1 por páginas e V2 por abas; campos/detalhes úteis; alertas; acesso aos
  módulos secundários; confirmação de ações perigosas.
- **MELHORAR:** validação no servidor, seleção de destinatário, acessibilidade,
  feedback, policy-aware UI e paginação após medir volume/contrato.
- **SUBSTITUIR:** SQL concatenado, validação apenas em JS, IDs crus, confirms frágeis,
  rotas de crédito quebradas e dependências CDN.
- **DESCARTAR:** somente itens já provados mortos (`LEG-RMA-016`, `LEG-RMA-034` e
  subrotas plurais quebradas de crédito). Resíduos Lightbox/AdminLTE dependem de
  `C-03/C-04`.
- **INVESTIGAR:** campos históricos editáveis × somente leitura, status importado nulo,
  criação/exclusão de usuários, mutações nas rotas prefixadas de QA e RN-12 no V1.

## Tema 3 — Console Operacional Adaptativa

Conceito reconciliado: mesa de trabalho orientada a fila, exceção, status e próxima
ação. Prioriza produtividade/densidade no desktop e se adapta integralmente a tablet e
telefone. CSS pode ser mobile-first sem presumir que telefone é o uso principal.

- navegação: rail recolhível no desktop, cabeçalho/drawer no telefone;
- dashboard: busca rápida, novo RMA, contadores acionáveis, alertas e filas;
- listagem: tabela densa no desktop e cartões equivalentes no telefone, sem remover
  informação/ação;
- formulário: seções, erros no topo e junto ao campo, salvar/cancelar persistentes;
- detalhe: número, status, prioridade, próxima transição, dados, solução, logística e
  histórico;
- estados: vazio-base, vazio-filtro, sucesso, validação, 403/404/500;
- interação: server-rendered e endereçável, progressive enhancement, teclado/foco,
  alvo mínimo e cor nunca como único indicador.

Regra superveniente a `INV-RMA-08` §6: desenvolvimento pode ser incremental e oculto,
mas o Tema 3 **só se torna selecionável** quando toda a matriz suportada estiver verde.
Tailwind 4 é candidato, não decisão; um spike compara utilitários com CSS semântico.

## Evoluções justificadas

### EVO-UX-002 — pesquisa global/lançador

Problema: busca atual exige entrar no módulo e não cobre parceiros.
Proposta: buscar por número, SN, NF, descrição e contraparte, agrupando resultados sob
as mesmas policies. Benefício: menos navegação. Complexidade: média-alta. Risco:
performance/vazamento. Prioridade: média-alta pós-fundação. Temas: todos. Dependências:
contrato de busca, índices, policies e tenancy quando aplicável.

### EVO-UX-003 — filtros e vistas pessoais persistentes

Problema: filas repetitivas perdem filtros. Proposta: salvar consultas nomeadas mantendo
a URL como fonte. Benefício: produtividade. Complexidade/risco: médios. Prioridade:
média. Temas: todos. Dependências: filtros/paginação e identidade/tenant.

### EVO-UX-004 — atividade operacional recente

Problema: históricos estão separados da fila. Proposta: read model paginado e
autorizado das últimas atividades. Benefício: consciência operacional. Complexidade e
risco: médios. Prioridade: média. Temas: todos. Dependências: policies, performance e
auditoria; não substitui `EVO-AUD-001`.

### INV-UX-005 — ações em lote

Problema ainda hipotético. Medir volume e repetição antes de especificar. Benefício
potencial alto; complexidade e risco altos. Prioridade baixa/investigação. Temas: todos.
Dependências: métricas, idempotência, autorização e auditoria por ação.

## Ordem de execução

1. Corrigir P0 (`ARQ-001`, `ARQ-002`, `ARQ-003`) e renovar regressões.
2. Retomar F10 funcional/visual/dados com as novas evidências.
3. Fechar busca, filas, campos, navegação e contratos compartilhados da Trilha A.
4. Declarar ou negar `G-07`; só depois liberar `G-08`.
5. Especificar/implementar Tema 3 oculto sobre a fundação compartilhada.
6. Expor Tema 3 somente com matriz, E2E, acessibilidade e performance aprovadas.

## Critério de saída desta investigação

- P0 corrigidos e testados;
- matriz por capacidade reconciliada com evidência;
- cada assimetria útil tem tarefa ou decisão registrada;
- contratos comuns definidos antes do Tema 3;
- `INV-RMA-08`/`EVO-UX-001` coerentes com paridade integral;
- Tema 3 possui OpenSpec e gate próprios antes de implementação pública.
