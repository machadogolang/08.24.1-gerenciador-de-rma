# Checklist de paridade visual/interação — TEMA V1 runtime (reabertura 2026-08-25)

Documento auxiliar indexado pela seção F10 visual do `checklist-master-v3.md`, criado
por `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-no-layout.md`. Fonte de
verdade: runtime real `http://localhost:8094/14.6.1/` (Legacy) × `http://localhost:8095`
(V3), nunca suposição por nome de classe. Só marca `[x]` quando existir runtime Legacy +
runtime V3 + comparação depois da correção + teste.

## Estratégia de evidência visual (prints comparativos Legado×V3)

Todo achado de divergência visual/estrutural registrado a partir de 2026-08-25 deve
referenciar o(s) print(s) que provaram a falha, não só descrever em texto. Regra de
onde cada print pode viver, decidida com o usuário nesta sessão:

- **Print só do V3, ou do V3 + Legacy sem nenhum dado de negócio real** (forms vazios,
  texto estático, contagens agregadas, tela de erro/403, estrutura de menu/coluna) →
  **versionado** em `docs/produto/screenshots-vis-v1-001/` (nome numerado + descritivo).
  Fictício porque o seed de QA da V3 (`v3-reset-qa.sh`) não usa dado real; o lado Legacy
  só entra aqui quando a tela em si não renderiza linha de RMA/cliente/fornecedor (ex.:
  títulos de painel, texto de ajuda, tela 403).
- **Print do Legacy com dado real** (o ambiente local roda em `LEGACY_DB_MODE=historical`,
  1.379 RMAs/165 clientes reais — ver `docs/produto/ambientes-locais-v2-v3.md`) →
  **nunca commitado** (mesma proteção já usada por `screenshots-paridade-v1/`). Gerar
  localmente quando precisar revisitar e referenciar no achado só o comando de
  reprodução + o seletor/URL exato, não o arquivo.
- Resetar o Legacy para `LEGACY_DB_MODE=sanitized` (`./scripts/legacy-reset.sh`) é uma
  alternativa válida para tornar um print commitável, mas **derruba o volume atual do
  banco histórico** (`docker compose down -v`) e só volta com
  `legacy-restore-historical.sh` + o dump externo — não fazer sem perguntar antes,
  decisão explícita do usuário nesta sessão.

## VIS-V1-001 — menu superior incompleto

- ID: VIS-V1-001
- Tela: cabeçalho, todas as páginas autenticadas do TEMA V1
- Legacy: `Pag. Inicial / Novo / Localizar / Entrada / Encaminhado / Aguardando credito / Concluido! / MENU / SIGN OUT` (`legacy-source/14.6.1/index.php:162-168`)
- V3: `Pag. Inicial / Novo / Localizar / MENU / SIGN OUT`
- Categoria: [x] estrutura [x] controle ausente [x] funcionalidade
- Evidência: `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/(Legado) Tela Inicial do Tema 14.6.1.png`, `(V3) Tela Inicial do Tema 14.6.1.png`
- Problema: faltam 4 atalhos de navegação superior, cada um abrindo uma listagem
  filtrada por status com colunas e regras de cor próprias (não é só um link — ver
  achado funcional abaixo).
- Fonte Legacy: `legacy-source/14.6.1/page/{entrada,encaminhados,aguardandocredito,concluidos}.php`
  — cada um consulta `bd` filtrando por `status`, com 8-10 colunas próprias e regras de
  destaque de linha (`TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2`) baseadas em
  solução, prioridade, origem e prazo de 30 dias.
- Fonte V3: `app/Http/Controllers/Rma/RmaController.php` (só tem `index`/busca genérica,
  sem rota por status); `routes/web.php:59-102` (recurso `rmas`, sem rota equivalente a
  `?page=entrada` etc.)
- Arquivos candidatos: nova rota `GET /rmas-entrada|encaminhados|aguardando-credito|concluidos`
  (paralelo a `rmas-alertas`), controller próprio ou método adicional, view
  `resources/views/temas/v1/rma/{entrada,encaminhados,...}.blade.php`
- Critério de aceite: os 4 itens aparecem no header do TEMA V1, na mesma posição/ordem
  do legado, cada um abre uma listagem real com as colunas e regras de destaque
  equivalentes (não precisa copiar CSS pixel a pixel de cor, mas a lógica de
  destaque — garantia/urgência/prazo — precisa ser a mesma regra de negócio já provada
  por `RecebidosSemEncaminhar30DiasTest`/`NaoVaiDarGarantiaTest`/etc.)
- Screenshot antes: `(V3) Tela Inicial do Tema 14.6.1.png` (nesta pasta)
- Screenshot depois: `docs/produto/screenshots-vis-v1-001/01-home-header-com-4-atalhos.png`
  (header) e `02-entrada.png`/`03-encaminhados.png`/`04-aguardando-credito.png`/
  `05-concluidos.png` (as 4 listagens) — dados fictícios do seed de QA (`v3-reset-qa.sh`,
  não é banco histórico), por isso versionados (diferente de
  `screenshots-paridade-v1/`, que compara com dado real do Legacy e por isso não é
  versionado).
- Teste: `tests/Feature/Rma/ListagensPorStatusTest.php` (6 casos: filtro por
  status/solução de cada painel, destaque RN-11 reaproveitado, presença dos 4 links no
  header)
- Status: **CORRIGIDO NESTA SESSÃO** — 4 rotas novas (`rmas.entrada`,
  `rmas.encaminhados`, `rmas.aguardando-credito`, `rmas.concluidos`), controller
  `ListagensPorStatusController`, caso de uso `ListarRmasDoPainel`, filtro no
  repositório (`RmasEmBanco::listarPorPainel()`, novo enum de domínio
  `PainelDeStatus`), views `temas/v1/rma/{entrada,encaminhados,aguardando-credito,concluidos}.blade.php`.
  Reaproveita a mesma regra de destaque RN-11 (`Rma::classeDeAlerta()` +
  `classe_css_de_alerta()`) já provada pelas 10 regras de alerta da Fase 5 — nenhuma
  lógica de negócio nova. Colunas seguem `page/*.php` linha a linha, com uma omissão
  documentada: "NF R" (`nfremessa`) não tem campo equivalente no domínio de aplicação
  atual (só existe como coluna histórica do migrador, `Rma::$fillable` Fase 9, sem dono)
  — não simulado com dado falso. Exclusivo do TEMA V1 (o header do TEMA V2 não tem esses
  atalhos, achado original), por isso o controller renderiza `temas.v1.rma.*`
  diretamente em vez de `view_do_tema()`. Suíte completa: 337 testes / 716 assertions,
  verde.

## VIS-V1-002 — "Novo" perdeu a interação inline

- ID: VIS-V1-002
- Tela: header (`Novo`) + qualquer superfície onde é aberto
- Legacy: `NovoMaximize()` expande `#JS-Novo` sobre a superfície atual sem navegar
  (`legacy-source/14.6.1/index.php:163`, `menujs-top/*` monta o formulário)
- V3: `Novo` é `<a href="/rmas/create">`, navega e troca a tela inteira
- Categoria: [x] comportamento [x] estrutura
- Evidência: `(Legado) Funcionalidade de adicionar Novo RMA.png`, `(V3) Funcionalidade de adicionar Novo RMA.png`
- Problema: perde o contexto da tela onde o operador estava (ex.: lista de "Aguardando
  crédito" continua visível abaixo do formulário expandido no legado).
- Fonte Legacy: `legacy-source/14.6.1/menujs-top/*`, função JS `NovoMaximize()`
- Fonte V3: `resources/views/temas/v1/rma/index.blade.php` (ou view equivalente),
  rota `rmas.create`
- Arquivos candidatos: extrair o formulário de criação para um partial incluído (não
  navegado) em toda página do TEMA V1, com JS de expandir/recolher equivalente
- Critério de aceite: abrir "Novo" em qualquer tela do TEMA V1 mantém o conteúdo da
  tela visível abaixo do formulário expandido; submissão continua validando/persistindo
  pelo caso de uso Laravel real (`CriarRmaTest.php`), sem PHP procedural
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — reestruturação de layout compartilhado,
  requer decidir o mecanismo (Livewire/Alpine/fetch parcial) antes de implementar;
  registrado para a próxima sessão.

## VIS-V1-003 — formulário "Novo RMA" estruturalmente diferente

- ID: VIS-V1-003
- Tela: formulário de criação de RMA
- Legacy: composição horizontal compacta de 5 colunas com campos
  `DESCRICAO/SNID/S-N/EMPRESA/CLIENTE` × `ORIGEM/FABRICANTE/MODELO/DEFEITO/OBS` ×
  `NF C+DATA/NF V+DATA/P-N/OS`, mais checkbox de item em estoque
  (`legacy-source/14.6.1/menujs-top/*`)
- V3: lista vertical de 11 campos (Descrição/Fabricante/Fornecedor/Modelo/SN/OS/Origem/
  Empresa/Cliente/Defeito/Observação), sem `SNID`, `NF de compra/venda` + data, `P/N`,
  marcação de estoque
- Categoria: [x] estrutura [x] geometria [x] controle ausente [x] funcionalidade
- Evidência: `(Legado) Funcionalidade de adicionar Novo RMA.png`, `(V3) Funcionalidade de adicionar Novo RMA.png`
- Problema: além da geometria, há campos do domínio potencialmente ausentes do
  formulário atual — precisa verificar coluna a coluna se já existem em `rmas` (schema)
  antes de decidir se é lacuna de apresentação ou de funcionalidade.
- Achado rápido desta sessão (leitura do schema, `DB::table('rmas')->first()`):
  `snid`, `nfcompra`+`nfcompra_emissao`, `nfvenda`+`nfvenda_emissao`, `pn` e
  `marcarestoque` **já existem como colunas em `rmas`** — não é lacuna de domínio, é
  campo existente não exposto no formulário do TEMA V1. Precisa conferir
  `app/Http/Requests`/caso de uso de criação para saber se já aceitam esses campos ou
  se também faltam na camada de aplicação.
- Fonte V3: `resources/views/temas/v1/rma/_campos.blade.php` (formulário atual),
  request/caso de uso de `CriarRmaTest.php`
- Critério de aceite: formulário reproduz a composição de 5 colunas do legado, todos os
  campos citados presentes e funcionais (persistem, aparecem no detalhe), sem simular
  campo que não persiste de verdade
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — precisa primeiro confirmar até onde a camada
  de aplicação (request/caso de uso) já aceita `snid`/`nfcompra`/`nfvenda`/`pn`/
  `marcarestoque` antes de desenhar o formulário; registrado para a próxima sessão.

## VIS-V1-004 — CSS do "Novo RMA" não corresponde ao original

- ID: VIS-V1-004
- Tela: formulário de criação de RMA (mesma superfície de VIS-V1-003)
- Legacy: `.tablenovo` fixa em `700px`, `.novo_formInput` `22px` de altura com cor
  `#C3FF00`, mais `.novo_formInputDATE/.novo_formInputSmall/.formSelectempresa/
  .formButtonEnviarNovo/.novo_defeito/.formInputObservacao`
- V3: `.tablenovo` generalizada para `width:100%`, sem os seletores específicos
- Categoria: [x] geometria [x] tipografia [x] cor
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — depende de VIS-V1-003 (a estrutura de campos
  precisa existir antes do CSS fazer sentido); registrado para a próxima sessão.

## VIS-V1-005 — Quadro de Anotações com botão "Salvar anotação" que não existia

- ID: VIS-V1-005
- Tela: Home (`Pag. Inicial`) e listagem `/rmas`
- Legacy: textarea ~675px, salvamento pelo comportamento do campo (sem botão
  permanente); sidebar ~280px
- V3: textarea com botão `[ Salvar anotação ]` sempre visível
- Categoria: [x] comportamento [x] estrutura
- Status: **investigado nesta sessão, não corrigido** — ver nota no rodapé deste
  documento (achado de implementação atual).

## VIS-V1-006 — Home/Localizar com composição diferente

- ID: VIS-V1-006
- Tela: `/rmas` (Home/Localizar)
- Legacy: campo de pesquisa + `QUALQUER UMA SOLUCAO` + `TODOS OS CAMPOS` + `FILTRAR`,
  depois quadro de anotações + sidebar de contadores
- V3: `Buscar por [Texto] [campo] [Buscar]` → "Nenhum RMA encontrado." antes do quadro
  de anotações
- Categoria: [x] estrutura [x] controle ausente
- Achado confirmado nesta sessão (runtime real, ver `docs/qa/roteiro-paridade-funcional.md`
  M-03): a busca da V3 tem 3 tipos reais (`texto`/`serial`/`nota_fiscal`, todos
  funcionando), mas falta o segundo seletor "solução" (`QUALQUER UMA SOLUCAO` no
  legado) e o combo "TODOS OS CAMPOS" — no legado a busca é campo+solução combinados,
  na V3 é só um tipo por vez.
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — registrado para a próxima sessão.

## VIS-V1-007 — títulos `<h1>` que não existiam na composição original

- ID: VIS-V1-007
- Tela: várias (ex.: "Novo RMA" acima do formulário)
- Legacy: sem heading `<h1>` padronizado nas superfícies auditadas
- V3: `<h1 class="titulo-v1">` injetado
- Categoria: [x] estrutura [x] tipografia
- Status: **NÃO AUDITADO TELA A TELA NESTA SESSÃO** — precisa passar por cada view do
  TEMA V1 conferindo se o legado tinha heading equivalente; registrado para a próxima
  sessão.

## VIS-V1-008 — MENU administrativo incompleto

- ID: VIS-V1-008
- Tela: dropdown `MENU`, todas as páginas autenticadas do TEMA V1
- Legacy: `Fornecedores / Fabricantes / Assistencias / Clientes / Controle / Creditos /
  Relatorios / Usuarios / Trocar p/ 15.8.1`
- V3 (antes desta sessão): `Fornecedores / Fabricantes / Assistências / Clientes /
  Usuários`
- Categoria: [x] controle ausente
- Achado confirmado nesta sessão: as rotas de `Controle`(histórico), `Créditos` e
  `Relatórios` **já existem** no V3 (`rmas.historico.index`, `rmas.credito.index`,
  `rmas.relatorios.{rcd,rpec,rmpe}`, `routes/web.php:80-96`) — não é lacuna funcional,
  só faltavam os links no MENU do TEMA V1.
- Classificação:
  - `Controle` → **MANTER**, aponta para `rmas.historico.index`
  - `Créditos` → **MANTER**, aponta para `rmas.credito.index`
  - `Relatórios` → **MANTER**, aponta para `rmas.relatorios.rcd` (RCD como entrada;
    RPEC/RMPE ficam dentro da própria tela de relatórios, como já é a composição V3)
  - `Trocar p/ 15.8.1` → **NÃO RECONSTRUIR** — decisão explícita: a arquitetura V3 não
    tem um "outro tema" 15.8.1 irmão navegável por link cru como o legado; a troca de
    tema já existe por outro mecanismo (`/perfil`, "Alternar tema"), documentado em
    `docs/qa/roteiro-paridade-funcional.md` M-01.
- Status: **CORRIGIDO NESTA SESSÃO** — ver commit.
- **Refinamento aberto nesta sessão (auditoria de cobertura, ver `VIS-V1-010`
  abaixo): a classificação `Controle` → MANTER/`rmas.historico.index` está
  ERRADA.** Esse Controller reproduz o painel "Controle" do **V2** legado (logs de
  modificação/autenticação, `15.8.1/page/controle.php`), não o do **V1** (7 ações
  administrativas pontuais: adicionar representante, arquivar RMA, deletar RMA, deletar
  usuário, ajuda, listar arquivados, mudar senha — `14.6.1/menujs-right/controle.php`).
  O link do MENU do TEMA V1 continua presente (não regride F10-V1-03), mas aponta para
  a tela errada. Não corrigido ainda — ver `VIS-V1-010/011/012/013`.

## VIS-V1-009 — Tela de detalhe do parceiro (com RMAs associados) ausente

- ID: VIS-V1-009
- Tela: detalhe de fornecedor/fabricante/cliente/assistência técnica/representante
- Legacy: `legacy-source/14.6.1/page/{assistencia,assistencia_tecnica,cliente,fabricante,fornecedor}.php`
  — `SELECT * FROM <tabela> WHERE id=...` (DDD, telefone, frete, e-mail, localidade, UF,
  CEP, CFOP, site, cidade, logradouro, bairro, complemento, observação) **mais**
  `SELECT * FROM bd WHERE destinatario='...'`/`cliente='...'` (RMAs associados a esse
  parceiro). Confirmado também no V2 legado:
  `legacy-source/15.8.1/subp/ver_{assistencia_tecnica,autorizada,cliente,fabricante,fornecedor}.php`
- V3: `routes/web.php` registra os 4 resources de parceiro com `->except(['show'])` —
  nenhuma rota de detalhe para nenhum dos 4 tipos
- Categoria: [x] controle ausente [x] funcionalidade
- Evidência: `docs/produto/screenshots-vis-v1-001/09-v3-fornecedores-sem-detalhe.png`
  (commitado — só mostra Editar/Remover, sem "Ver"); lado Legacy tem dado real de
  parceiro/RMA, reproduzir localmente (login `lab@localhost`/`rma-lab-2026` em
  `http://localhost:8094/14.6.1/`, MENU → Fornecedores → clicar num nome)
- Problema: sem essa tela não há como ver o histórico de RMAs de um parceiro específico
  nem os campos operacionais completos (frete/CFOP importam para o fluxo fiscal de RMA)
- Fontes Legacy: arquivos acima
- Fontes V3: `routes/web.php` (resources `parceiros/*`),
  `app/Http/Controllers/Parceiros/*Controller.php`
- Já rastreado em `docs/produto/matriz-paridade-temas-v1-v2-v3.md` como
  `PAR-PARCEIRO-001` — este achado formaliza a mesma lacuna no formato do checklist
  visual, com os arquivos-fonte concretos.
- Critério de aceite: rota `GET` de detalhe para os 4 tipos de parceiro, view com todos
  os campos do domínio + lista de RMAs relacionados (reaproveitando `BuscarRmas` ou
  consulta equivalente, sem duplicar regra de negócio)
- Status: **[ ] pendente** — já tinha tarefa aberta (`PAR-PARCEIRO-001`)

## VIS-V1-010 — Refinamento de VIS-V1-008: "Controle" do V1 foi mapeado para a tela errada

- ID: VIS-V1-010
- Tela: item "Controle" do MENU, TEMA V1
- Achado: correção de uma conclusão anterior (`VIS-V1-008`), não uma tela nova
- Legacy V1: `legacy-source/14.6.1/page/controle.php` (idêntico a
  `menujs-right/controle.php`) é um painel de 7 ações administrativas: adicionar
  representante, arquivar RMA por número, deletar RMA por número, deletar usuário por
  e-mail, texto de ajuda do procedimento, listar RMAs arquivados, mudar senha
- V3: mapeado para `rmas.historico.index` (`HistoricoDeModificacaoController`), cujo
  próprio docblock cita `subp/logs_de_modificacao.php` — tela do **V2** legado, não do
  painel "Controle" do V1. O "Controle" do V2 (`15.8.1/page/controle.php` +
  `inc/menu_controle.php`) esse sim inclui logs — mapeamento correto só ali.
- Categoria: [x] funcionalidade [x] estrutura
- Evidência: `docs/produto/screenshots-vis-v1-001/06-legacy-menu-controle-v1-colapsado.png`
  e `07-legacy-menu-controle-v1-ajuda-expandida.png` (Legacy, painel real — commitados,
  sem dado de RMA/cliente, só títulos de ação e texto estático) ×
  `08-v3-controle-aponta-para-historico-nao-acoes.png` (V3, "Histórico de modificações
  de RMA" — tela diferente)
- Fontes: `legacy-source/14.6.1/page/controle.php`,
  `legacy-source/14.6.1/menujs-right/controle.php`,
  `legacy-source/15.8.1/page/controle.php`, `legacy-source/15.8.1/inc/menu_controle.php`,
  `app/Http/Controllers/Rma/HistoricoDeModificacaoController.php`
- Critério de aceite: reclassificar `VIS-V1-008` — para o TEMA V1, "Controle" deveria
  apontar para uma composição própria com as ações do painel (arquivar/mudar senha já
  têm rota; faltam `VIS-V1-011/012/013`); `rmas.historico.index` continua correto como
  "Logs de modificação" do V2, só não é o "Controle" do V1
- Status: **[ ] pendente** — reclassificar o link antes de fechar `F10-V1-03`

## VIS-V1-011 — Ação "Deletar RMA" (hard delete) ausente na V3

- ID: VIS-V1-011
- Tela/ação: painel Controle do V1 → "DELETAR UMA SOLICITACAO DE RMA"
- Legacy: `legacy-source/14.6.1/page/controle.php` (form NUMERO + botão DELETAR),
  executado por `legacy-source/14.6.1/banco.oo.php:441`
  (`DELETE FROM bd WHERE numero = '$numero'`). Só no V1 — `15.8.1/banco.php` não tem
  `DELETE FROM bd` equivalente.
- V3: `routes/web.php` — `Route::resource('rmas', RmaController::class)->except(['destroy'])`,
  sem decisão documentada explicando a exclusão (diferente do caso de crédito, que tem
  `LEG-RMA-048` documentado no próprio Controller)
- Categoria: [x] funcionalidade [x] controle ausente
- Evidência: `docs/produto/screenshots-vis-v1-001/06-legacy-menu-controle-v1-colapsado.png`
  (título "DELETAR UMA SOLICITACAO DE RMA" visível no painel)
- Problema: não há como excluir definitivamente um RMA na V3; só arquivar (reversível)
  existe. Pode ser decisão de produto deliberada (preservar auditoria), mas hoje é
  omissão silenciosa, não decisão registrada.
- Critério de aceite: OU implementar a ação com o mesmo contrato do legado, OU registrar
  formalmente a decisão de não reconstruir (mesmo padrão de `LEG-RMA-016`/`034`)
- Status: **[ ] pendente — requer decisão de produto antes de implementar ou descartar**

## VIS-V1-012 — Ação "Deletar usuário" (hard delete) ausente na V3

- ID: VIS-V1-012
- Tela/ação: painel Controle do V1 → "DELETAR UM USUARIO"; V2 → `subp/apagar_usuario.php`
- Legacy: confirmado nos dois temas —
  `legacy-source/14.6.1/page/controle.php` (form + `banco.oo.php:434
  DELETE FROM usuario WHERE email = '$email'`) e
  `legacy-source/15.8.1/subp/apagar_usuario.php`
- V3: `app/Http/Controllers/Identidade/UsuarioController.php` só tem
  `index`/`update`/`resetarSenha`; nenhuma rota `DELETE /usuarios/{id}`
- Categoria: [x] funcionalidade [x] controle ausente
- Evidência: `docs/produto/screenshots-vis-v1-001/06-legacy-menu-controle-v1-colapsado.png`
  (título "DELETAR UM USUARIO" visível no painel)
- Critério de aceite: ação de exclusão de usuário acessível pela tela de gerenciamento
  de usuários, com a mesma autorização (`Gate::authorize('gerenciar', ...)` + hierarquia
  de papel `ARQ-003`, já usada por `update`/`resetarSenha`), OU decisão de produto
  documentada para substituir por desativação/soft-delete
- Status: **[ ] pendente**

## VIS-V1-013 — Listagem "RMAs arquivados" sem equivalente na V3

- ID: VIS-V1-013
- Tela: painel Controle do V1 → item #8 "LISTAR SOLICITACOES DE RMA ARQUIVADAS"
- Legacy: `legacy-source/14.6.1/page/controle.php`
  (`SELECT * FROM bd WHERE status='ARQUIVADO'`), tabela CHAVE/FABRICANTE/DESCRICAO/
  MODELO/S-N/OS
- V3: `RmaController::index` só lista resultado de busca com `valor` não-vazio;
  `Status::Arquivado` existe no domínio e é usado por `rmas.arquivar`/`rmas.reverter`,
  mas não há listagem nem filtro de busca por esse status
- Categoria: [x] controle ausente [x] funcionalidade
- Evidência: `docs/produto/screenshots-vis-v1-001/06-legacy-menu-controle-v1-colapsado.png`
  (título "LISTAR SOLICITACOES DE RMA ARQUIVADAS" visível no painel)
- Critério de aceite: listagem (ou filtro de busca por status) para `Status::Arquivado`,
  acessível a partir de uma tela real
- Status: **[ ] pendente** — prioridade menor (dado é auditável via `rmas.historico`,
  falta visão consolidada)

## VIS-V1-014 — Tela de ajuda/procedimento estática ausente na V3

- ID: VIS-V1-014
- Tela: "help" (painel de ajuda do V1)
- Legacy: `legacy-source/14.6.1/page/help.php` — texto estático explicando o fluxo
  Entrada → Recebido → Encaminhado → Concluído
- V3: nenhuma view/rota com conteúdo equivalente
- Categoria: [x] controle ausente
- Evidência: `docs/produto/screenshots-vis-v1-001/07-legacy-menu-controle-v1-ajuda-expandida.png`
  (texto completo do painel de ajuda, commitado — conteúdo estático, sem dado real)
- Problema: conteúdo puramente informativo, baixa prioridade
- Critério de aceite: página de ajuda simples reproduzindo o texto, OU decisão de
  produto documentada de descartar
- Status: **[ ] pendente, prioridade baixa**

## VIS-V2-001 — Aba/listagem "Recebido" ausente na V3 (TEMA V2 legado)

- ID: VIS-V2-001
- Tela: aba de navegação "Recebido" do TEMA V2 legado
- Legacy: `legacy-source/15.8.1/inc/menu.php:29-33` (aba de primeiro nível, ao lado de
  Entrada/Encaminhado/Concluído), renderizada por `legacy-source/15.8.1/page/recebido.php`,
  mesmas colunas (DATA/ORIGEM/T/NF C/NF V/FABRICANTE/DESCRICAO/MODELO/S-N/OS/A) e regras
  de destaque (`TrInconformidade`/`TrUrgente`) já usadas nas outras 4 listagens de
  `VIS-V1-001`
- V3: `Status::Recebido` existe no domínio e a ação `rmas.receber` transiciona um RMA
  para esse status, mas nenhuma rota lista "RMAs com status Recebido" — nem mesmo a
  `ListagensPorStatusController` criada nesta sessão para `VIS-V1-001` (que replica só
  as 4 abas do V1; o V1 legado nunca teve aba "Recebido" própria, só o V2 tem)
- Categoria: [x] controle ausente [x] funcionalidade [x] estrutura
- Evidência: `legacy-source/15.8.1/page/recebido.php` linhas 1-30 (query via
  `listar_recebidos()`, mesmas classes CSS de destaque das outras listagens); print
  Legacy não gerado (mostraria RMAs reais) — reproduzir localmente em
  `http://localhost:8094/15.8.1/`
- Problema: MESMA classe de lacuna do `VIS-V1-001` (tela do legado totalmente ausente na
  V3, não malfeita), só que para um status que só tem aba dedicada no TEMA V2 do legado.
  **A correção de `VIS-V1-001` desta sessão cobre só as 4 abas do V1 e não fecha esta
  quinta listagem.**
- Fontes Legacy: `legacy-source/15.8.1/page/recebido.php`,
  `legacy-source/15.8.1/inc/menu.php:29-33`
- Fontes V3: `app/Rma/Dominio/Status.php`,
  `app/Http/Controllers/Rma/ListagensPorStatusController.php`, `routes/web.php`
- Critério de aceite: rota `GET /rmas-recebidos` (ou nome equivalente) com listagem e
  regras de destaque próprias, seguindo o mesmo padrão de `ListagensPorStatusController`;
  decidir em qual(is) tema(s) da V3 ela aparece
- Status: **[ ] pendente — sinalizado explicitamente para não fechar o gate visual sem
  esta quinta listagem**

---

## Nota sobre VIS-V1-005 (Quadro de Anotações)

Investigado nesta sessão: `app/Http/Controllers/QuadroDeAnotacoesController.php` (ou
equivalente) e a view do TEMA V1 mostram um botão de submit explícito. Restaurar o
salvamento "pelo comportamento do campo" do legado (sem botão, provavelmente
`onblur`/debounce) é uma mudança de mecanismo de persistência, não só de CSS — a própria
investigação pede autosave seguro (debounce, feedback discreto, proteção contra chamada
concorrente, tratamento de erro) em vez de reproduzir o JS antigo inseguro. Isso é
trabalho de funcionalidade nova (mesmo que pequeno), não de reclassificação de link, e
por isso fica registrado para a próxima sessão junto de VIS-V1-001/002/003/004/006/007.

## Resumo de status desta rodada

| ID | Status |
|---|---|
| VIS-V1-001 | **Corrigido e testado nesta sessão** |
| VIS-V1-002 | Registrado, não corrigido — reestruturação de layout compartilhado |
| VIS-V1-003 | Registrado, não corrigido — achado: campos já existem no schema |
| VIS-V1-004 | Registrado, não corrigido — depende de VIS-V1-003 |
| VIS-V1-005 | Registrado, não corrigido — mudança de mecanismo de persistência |
| VIS-V1-006 | Registrado, não corrigido — falta segundo seletor "solução" |
| VIS-V1-007 | Não auditado tela a tela |
| VIS-V1-008 | **Corrigido nesta sessão anterior — refinamento em `VIS-V1-010`** |
| VIS-V1-009 | Pendente — 5 telas de detalhe de parceiro ausentes (`PAR-PARCEIRO-001`) |
| VIS-V1-010 | Pendente — "Controle" do MENU V1 aponta para tela errada (refina `VIS-V1-008`) |
| VIS-V1-011 | Pendente — ação "Deletar RMA" ausente, requer decisão de produto |
| VIS-V1-012 | Pendente — ação "Deletar usuário" ausente (V1 e V2) |
| VIS-V1-013 | Pendente — listagem "RMAs arquivados" ausente, prioridade menor |
| VIS-V1-014 | Pendente — tela de ajuda estática ausente, prioridade baixa |
| VIS-V2-001 | Pendente — listagem "Recebido" (TEMA V2 legado) totalmente ausente |
