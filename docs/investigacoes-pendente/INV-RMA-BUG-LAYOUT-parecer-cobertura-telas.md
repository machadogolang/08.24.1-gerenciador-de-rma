# Parecer de cobertura de telas — Legado (14.6.1 + 15.8.1) × V3

Data: 2026-08-25. Documento complementar a
`INV-RMA-BUG-LAYOUT-problemas-no-layout.md` e ao checklist
`docs/produto/checklist-paridade-visual-v1-runtime.md` (achados `VIS-V1-001` a
`VIS-V1-008`). **Não edita esses documentos** — só estende a numeração.

## Objetivo e método

O documento-fonte da investigação de layout achou que 4 telas inteiras do legado
(Entrada/Encaminhado/Aguardando crédito/Concluído) não tinham NENHUM equivalente na
V3 — não é um problema de CSS, é ausência total de rota+controller+view. Este parecer
audita sistematicamente se esse MESMO tipo de gap existe em outra tela, cobrindo os
dois temas do legado (`legacy-source/14.6.1` = TEMA V1 fonte, `legacy-source/15.8.1` =
TEMA V2 fonte), cruzando cada arquivo `page/*.php` (e `subp/*.php`/`pp/*.php` do
15.8.1) contra `routes/web.php` e os Controllers reais da V3 — nunca por nome de
classe, sempre lendo o conteúdo (query SQL, campos, ação) de cada lado.

**Nota de nomenclatura:** "TEMA V1"/"TEMA V2" nos documentos `docs/produto/
checklist-paridade-temas.md` e `matriz-paridade-temas-v1-v2-v3.md` referem-se às DUAS
skins da própria V3 (mesmos Controllers, view diferente por `view_do_tema()`) — não ao
par de aplicações do legado. Este documento usa "V1 legado"/"14.6.1" e "V2
legado"/"15.8.1" para evitar a ambiguidade.

## Inventário — 14.6.1 (V1 legado), `legacy-source/14.6.1/page/*.php`

16 arquivos. `menu.php` (só dispara JS, sem conteúdo) e `index.php` (stub de
redirecionamento morto, `location.href="notfounder.php"`) não são telas reais — ficam
fora da contagem. **14 telas reais.**

| Arquivo | O que faz | Cobertura na V3 |
|---|---|---|
| `entrada.php` / `encaminhados.php` / `aguardandocredito.php` / `concluidos.php` | listagem por status, 8-10 colunas, regras de destaque | **Já achado — `VIS-V1-001`, correção em andamento em paralelo nesta sessão. Não duplicado aqui.** |
| `detalhes.php` | boletim de defeito (detalhe do RMA) | coberta, `rmas.show` |
| `localizar.php` | busca (campo+solução combinados) | coberta com ressalva — gap de "solução" já rastreado em `VIS-V1-006` |
| `relatorios.php` | RCD (créditos disponíveis) | coberta, `rmas.relatorios.rcd` |
| `controle.php` | painel de 7 ações administrativas (ver abaixo) | **parcial — ver `VIS-V1-010/011/012/013`** |
| `assistencia.php`, `assistencia_tecnica.php`, `cliente.php`, `fabricante.php`, `fornecedor.php` | detalhe de 1 parceiro + lista de RMAs associados | **ausente — `VIS-V1-009`** |
| `help.php` | texto estático de ajuda/procedimento | **ausente — `VIS-V1-014`** |

## Inventário — 15.8.1 (V2 legado), `legacy-source/15.8.1/page/*.php`

29 arquivos. `403.php`/`404.php` (páginas de erro), `menu.php` (placeholder vazio,
confirmado por leitura — só título "MENU" sem conteúdo) e `representantes.php`/
`retornou.php` (**0 bytes, confirmado por `wc -l` — código morto**, consistente com a
decisão já registrada na matriz para `LEG-RMA-016`) ficam fora da contagem. **24 telas
reais.**

| Arquivo | O que faz | Cobertura na V3 |
|---|---|---|
| `inicio.php` | dashboard | coberta, `rmas.index`/painel de alertas |
| `pesquisar.php` | busca | coberta com a mesma ressalva de `VIS-V1-006` |
| `logout.php` | logout | coberta, `POST /logout` |
| `entrada.php`, `encaminhado.php`, `concluido.php` | listagem por status | mesma cobertura em andamento de `VIS-V1-001` (Controllers são únicos entre temas — não duplica achado) |
| `recebido.php` | listagem status=Recebido, mesmas regras de destaque | **ausente — `VIS-V2-001`** |
| `credito.php`, `creditos.php` (com submenu disponíveis/pendentes/usados) | fluxo de crédito | coberta, `rmas.credito.index` — sub-rotas "pendentes"/"usados" **comprovadamente já quebradas no próprio legado** (`subp/pendentes.php`/`subp/usados.php` não existem no disco), confirma que a decisão já documentada no `CreditoController` (`LEG-RMA-048`, fluxo único) é a correta — não é lacuna |
| `clientes.php`, `fabricantes.php`, `fornecedores.php`, `assistencia_tecnicas.php` | listagem de parceiros | cobertas, `parceiros.*.index` |
| `novo_cliente.php`, `novo_fabricante.php`, `novo_fornecedor.php`, `nova_assistencia_tecnica.php` | criar parceiro | cobertas, `parceiros.*.create` |
| `novo_rma.php` | criar RMA | coberta, `rmas.create` |
| `rma.php` | detalhe do RMA | coberta, `rmas.show` |
| `relatorios.php` | RCD/RPEC/RMPE | cobertas |
| `controle.php` (submenu: logs de autenticação, logs de modificação, alterar senha, novo usuário, usuários) | painel administrativo | coberta — **corretamente** mapeada para `rmas.historico.index` (logs de modificação) + `identidade.historico-de-acesso.index` (logs de autenticação) + `identidade.usuarios.index`, EXCETO ação de excluir usuário (ver `VIS-V1-012`, cross-tema) |
| `anotacoes.php` | tela dedicada de anotação (textarea grande, item de menu próprio) | coberta funcionalmente via `/perfil` (embutida), diferença é só de composição/descoberta — mesma família do achado já registrado `VIS-V1-005`, não abro achado novo |
| `avisar_alguem.php`, `enviar_email.php` | telas que só mostram "Chave: X" | **inconclusivo, ver nota abaixo — não conta como gap confirmado** |

### Nota sobre `avisar_alguem.php` / `enviar_email.php`

Essas duas telas não têm nenhuma lógica de envio dentro delas (nem formulário de
submit, nem chamada a `mail()`) — são essencialmente stubs mesmo no legado. Existe
código de envio de e-mail real em `legacy-source/15.8.1/banco.php` (funções `ezequiel()`,
`naopermitido()`, `notificarerrologin()`, `enviar_saudacao()`), mas são notificações
automáticas de backend disparadas por outros eventos (RMA concluído, tentativa de
modificação por usuário sem permissão, falha de login, boas-vindas), com destinatários
**hardcoded** (e-mails pessoais de desenvolvedores) — não são acionadas por estas duas
telas. Não abro achado formal sem confirmar em runtime se `avisar_alguem`/`enviar_email`
têm algum handler de POST oculto que não encontrei por grep; risco baixo de ser uma
lacuna real, dado que a própria tela não tem ação.

## Achados novos

### VIS-V1-009 — Tela de detalhe do parceiro (com RMAs associados) ausente

- ID: VIS-V1-009
- Tela: detalhe de fornecedor/fabricante/cliente/assistência técnica/representante
- Legacy: `legacy-source/14.6.1/page/{assistencia,assistencia_tecnica,cliente,fabricante,fornecedor}.php` — cada um faz `SELECT * FROM <tabela> WHERE id=...` (dados completos: DDD, telefone, frete, e-mail, localidade, UF, CEP, CFOP, site, cidade, logradouro, bairro, complemento, observação) **mais** `SELECT * FROM bd WHERE destinatario='...'` (ou `cliente='...'`) — lista de RMAs associados a esse parceiro. Confirmado também no V2 legado: `legacy-source/15.8.1/subp/ver_{assistencia_tecnica,autorizada,cliente,fabricante,fornecedor}.php`
- V3: `routes/web.php` registra os 4 resources de parceiro com `->except(['show'])` — não existe rota de detalhe para NENHUM dos 4 tipos
- Categoria: [x] controle ausente [x] funcionalidade
- Evidência: leitura de código (`page/assistencia_tecnica.php` linhas 1-45, ver campos NOME/FONE1/FONE2/EMAIL1/EMAIL2/REPRESENTANTE/FRETE/CEP/CFOP/SITE/CIDADE/UF/LOGRADOURO/BAIRRO/COMPLEMENTO/OBSERVACAO — nenhum desses aparece no form atual de parceiros da V3, que só tem nome/representante/cpf_cnpj/email/telefone segundo `checklist-paridade-temas.md`)
- Problema: sem essa tela não há como ver o histórico de RMAs de um parceiro específico nem os campos operacionais completos (frete/CFOP importam para o fluxo fiscal de RMA)
- Fontes Legacy: arquivos acima
- Fontes V3: `routes/web.php` (resources `parceiros/*`), `app/Http/Controllers/Parceiros/*Controller.php`
- Já rastreado em outro documento: `docs/produto/matriz-paridade-temas-v1-v2-v3.md`, linha "Detalhe/RMAs do parceiro | sim | ausente | ausente | planejado | PAR-PARCEIRO-001" — este achado formaliza a mesma lacuna no formato do checklist visual, com os arquivos-fonte concretos.
- Critério de aceite: rota `GET` de detalhe para os 4 tipos de parceiro, view com todos os campos do domínio + lista de RMAs relacionados (reaproveitando `BuscarRmas` ou consulta equivalente, sem duplicar regra de negócio)
- Status: **NÃO CORRIGIDO** — pendente, já tinha tarefa aberta (`PAR-PARCEIRO-001`)

### VIS-V1-010 — Refinamento de VIS-V1-008: "Controle" do V1 foi mapeado para a tela errada

- ID: VIS-V1-010
- Tela: item "Controle" do MENU, TEMA V1
- Achado: **correção de uma conclusão anterior**, não uma tela nova
- Legacy V1: `legacy-source/14.6.1/page/controle.php` (idêntico a
  `menujs-right/controle.php`, mesmo conteúdo) é um **painel de 7 ações
  administrativas**: adicionar representante genérico, arquivar RMA por número, deletar
  RMA por número, deletar usuário por e-mail, texto de ajuda do procedimento, listar
  RMAs arquivados, mudar senha
- V3 (conclusão de `VIS-V1-008`, sessão anterior): mapeou "Controle" → `rmas.historico.index` (`HistoricoDeModificacaoController`)
- Problema: o próprio `HistoricoDeModificacaoController.php` documenta no docblock que
  "reproduz `subp/logs_de_modificacao.php`" — que é uma tela do **V2 legado**
  (`15.8.1`), não do painel "Controle" do V1. Conferido: o "Controle" do V2
  (`15.8.1/page/controle.php` + `inc/menu_controle.php`) SIM inclui logs de
  autenticação/modificação — mapeamento correto ali. Mas o "Controle" do V1 é um painel
  totalmente diferente (ações administrativas pontuais, não logs). `VIS-V1-008` tratou
  os dois "Controle" (V1 e V2) como a mesma tela por coincidência de rótulo de menu —
  exatamente o tipo de suposição por nome que a investigação original pediu para evitar.
- Categoria: [x] funcionalidade [x] estrutura
- Fontes: `legacy-source/14.6.1/page/controle.php`, `legacy-source/14.6.1/menujs-right/controle.php`, `legacy-source/15.8.1/page/controle.php`, `legacy-source/15.8.1/inc/menu_controle.php`, `app/Http/Controllers/Rma/HistoricoDeModificacaoController.php` (docblock cita `subp/logs_de_modificacao.php`)
- Critério de aceite: reclassificar `VIS-V1-008` — para o TEMA V1, "Controle" deveria
  apontar para uma composição própria com as ações do painel (arquivar/mudar senha já
  têm rota; faltam as 3 descritas em `VIS-V1-011`/`012`/`013`); `rmas.historico.index`
  continua correto como mapeamento do "Logs de modificação" do V2, só não é o
  "Controle" do V1.
- Status: achado desta auditoria — recomendado reabrir a nota de `VIS-V1-008` no
  checklist original (não editado aqui, só sinalizado)

### VIS-V1-011 — Ação "Deletar RMA" (hard delete) ausente na V3

- ID: VIS-V1-011
- Tela/ação: painel Controle do V1 → "DELETAR UMA SOLICITACAO DE RMA"
- Legacy: `legacy-source/14.6.1/page/controle.php` (form com campo NUMERO + botão
  DELETAR), executado por `legacy-source/14.6.1/banco.oo.php:441`
  (`DELETE FROM bd WHERE numero = '$numero'`). Confirmado **só no V1** — `15.8.1/
  banco.php` não tem `DELETE FROM bd` equivalente.
- V3: `routes/web.php` — `Route::resource('rmas', RmaController::class)->except(['destroy'])`, sem nenhum comentário/decisão documentada explicando a exclusão (diferente do caso de crédito, que tem `LEG-RMA-048` documentado no próprio Controller)
- Categoria: [x] funcionalidade [x] controle ausente
- Problema: não há como excluir definitivamente um RMA na V3; só arquivar (reversível,
  `rmas.arquivar`) existe. Pode ser uma decisão de produto deliberada (preservar
  auditoria), mas hoje é uma omissão silenciosa, não uma decisão registrada.
- Critério de aceite: OU implementar a ação com o mesmo contrato do legado (exclusão
  física por número, mesma restrição de acesso do painel Controle), OU registrar
  formalmente a decisão de não reconstruir (mesmo padrão de `LEG-RMA-016`/`034` na
  matriz), documentando o porquê.
- Status: **aberto — requer decisão de produto antes de implementar ou descartar**

### VIS-V1-012 — Ação "Deletar usuário" (hard delete) ausente na V3

- ID: VIS-V1-012
- Tela/ação: painel Controle do V1 → "DELETAR UM USUARIO"; V2 → `subp/apagar_usuario.php`
- Legacy: confirmado nos **dois temas** do legado —
  `legacy-source/14.6.1/page/controle.php` (form + `banco.oo.php:434
  DELETE FROM usuario WHERE email = '$email'`) e
  `legacy-source/15.8.1/subp/apagar_usuario.php`
- V3: `app/Http/Controllers/Identidade/UsuarioController.php` só tem
  `index`/`update`/`resetarSenha`; nenhuma rota `DELETE /usuarios/{id}` em
  `routes/web.php`
- Categoria: [x] funcionalidade [x] controle ausente
- Fontes: arquivos acima
- Critério de aceite: ação de exclusão de usuário acessível pela tela de
  gerenciamento de usuários, com a mesma autorização (`Gate::authorize('gerenciar',
  ...)` + regra `ARQ-003` de hierarquia de papel, já usada por `update`/
  `resetarSenha`), OU decisão de produto documentada para substituir por
  desativação/soft-delete — mas como escolha registrada, não omissão.
- Status: **aberto**

### VIS-V1-013 — Listagem "RMAs arquivados" sem equivalente na V3

- ID: VIS-V1-013
- Tela: painel Controle do V1 → item #8 "LISTAR SOLICITACOES DE RMA ARQUIVADAS"
- Legacy: `legacy-source/14.6.1/page/controle.php`
  (`SELECT * FROM bd WHERE status='ARQUIVADO'`), tabela com CHAVE/FABRICANTE/
  DESCRICAO/MODELO/S-N/OS
- V3: `RmaController::index` só lista resultado de busca com `valor` não-vazio
  (tipo texto/serial/nota_fiscal); `Status::Arquivado` existe no domínio
  (`app/Rma/Dominio/Status.php`) e é usado por `rmas.arquivar`/`rmas.reverter`, mas
  não há listagem nem filtro de busca por esse status
- Categoria: [x] controle ausente [x] funcionalidade
- Critério de aceite: listagem (ou filtro de busca por status) para
  `Status::Arquivado`, acessível a partir de uma tela real
- Status: **aberto** — prioridade menor (dado é auditável via `rmas.historico`, mas
  falta visão consolidada dos arquivados)

### VIS-V1-014 — Tela de ajuda/procedimento estática ausente na V3

- ID: VIS-V1-014
- Tela: "help" (ícone de ajuda no V1)
- Legacy: `legacy-source/14.6.1/page/help.php` — texto estático explicando o fluxo
  Entrada → Recebido → Encaminhado → Concluído
- V3: nenhuma view/rota com conteúdo equivalente (`grep` por "ajuda"/"help" em
  `resources/views` não retornou nada)
- Categoria: [x] controle ausente
- Problema: conteúdo puramente informativo/estático (não é dado operacional), baixa
  prioridade
- Critério de aceite: página de ajuda simples reproduzindo o texto, OU decisão de
  produto documentada de descartar (ex.: substituir por documentação externa)
- Status: **aberto, prioridade baixa**

### VIS-V2-001 — Aba/listagem "Recebido" ausente na V3 (status real do domínio sem tela em nenhum tema)

- ID: VIS-V2-001
- Tela: aba de navegação "Recebido" do TEMA V2 legado
- Legacy: `legacy-source/15.8.1/inc/menu.php:29-33` tem uma aba de primeiro nível
  "Recebido" (ao lado de Entrada/Encaminhado/Concluído), renderizada por
  `legacy-source/15.8.1/page/recebido.php`, com as MESMAS colunas (DATA/ORIGEM/T/NF C/
  NF V/FABRICANTE/DESCRICAO/MODELO/S-N/OS/A) e regras de destaque
  (`TrInconformidade`/`TrUrgente`, mesma lógica de garantia/urgência/prazo de 30 dias)
  já identificadas em `VIS-V1-001` para as outras 4 listagens
- V3: `Status::Recebido` existe no domínio (`app/Rma/Dominio/Status.php`) e a ação
  `rmas.receber` transiciona um RMA para esse status, mas **nenhuma rota lista** "RMAs
  com status Recebido" — nem mesmo a `ListagensPorStatusController` criada nesta
  mesma sessão, em paralelo, para corrigir `VIS-V1-001` (que replica só as 4 abas do
  V1: entrada/encaminhados/aguardandoCredito/concluidos — o V1 legado nunca teve aba
  "Recebido" própria, só o V2 tem)
- Categoria: [x] controle ausente [x] funcionalidade [x] estrutura
- Evidência: `legacy-source/15.8.1/page/recebido.php` linhas 1-30 (query via
  `listar_recebidos()`, mesmas classes CSS de destaque das outras listagens)
- Problema: é a MESMA classe de lacuna do `VIS-V1-001` (tela do legado totalmente
  ausente na V3, não malfeita) — só que para um status que só tem aba dedicada no
  TEMA V2 do legado. **Risco concreto:** a correção de `VIS-V1-001` em andamento
  nesta sessão em paralelo cobre só as 4 abas do V1 e pode ser fechada como completa
  sem essa quinta listagem, já que "Recebido" nunca apareceu no header do V1 (fonte
  original de `VIS-V1-001`).
- Fontes Legacy: `legacy-source/15.8.1/page/recebido.php`,
  `legacy-source/15.8.1/inc/menu.php:29-33`
- Fontes V3: `app/Rma/Dominio/Status.php`,
  `app/Http/Controllers/Rma/ListagensPorStatusController.php`,
  `routes/web.php` (rotas `rmas-entrada/encaminhados/aguardando-credito/concluidos`)
- Critério de aceite: rota `GET /rmas-recebidos` (ou nome equivalente) com listagem e
  regras de destaque próprias, seguindo o mesmo padrão de
  `ListagensPorStatusController`; decidir em qual(is) tema(s) da V3 ela aparece
  seguindo a regra já registrada na matriz ("Tema muda disposição, não regra")
- Status: **aberto — sinalizar para quem está corrigindo `VIS-V1-001` nesta sessão,
  para não fechar o gate sem esta quinta listagem**

## Resumo executivo

- **Telas reais auditadas nesta sessão** (excluindo páginas de erro, arquivos vazios/
  mortos e triggers puramente JS sem conteúdo): **38** — 14 do V1 legado (`14.6.1`) +
  24 do V2 legado (`15.8.1`).
- **Com equivalente confirmado na V3** (cobertas, incluindo as que compartilham
  Controller entre os 2 temas da V3): **28** — 7 no V1 (3 cobertas + 4 já em correção
  via `VIS-V1-001`, fora do escopo desta auditoria) + 21 no V2 (20 cobertas + `controle`
  quase totalmente coberto).
- **Parcialmente cobertas, com ação/sub-tela confirmadamente ausente**: **2** —
  `controle.php` do V1 (3 sub-lacunas: `VIS-V1-011/012/013`) e `controle.php` do V2
  (1 sub-lacuna, deletar usuário, mesma raiz de `VIS-V1-012`).
- **Totalmente ausentes na V3** (nenhuma rota/view real, mesmo malfeita): **7 arquivos
  de legado**, agrupados em **3 achados novos**: `VIS-V1-009` (5 telas de detalhe de
  parceiro), `VIS-V1-014` (1 tela de ajuda), `VIS-V2-001` (1 aba/listagem "Recebido").
- **1 refinamento de achado existente**: `VIS-V1-010` corrige o mapeamento de
  "Controle" feito por `VIS-V1-008` (o Controller citado como equivalente reproduz uma
  tela do V2, não a do V1 — mesma tela, temas diferentes, conteúdo diferente).
- **2 ações administrativas ausentes, cross-tema** (não são "telas" sozinhas, mas
  ações confirmadas em ambos os legados e ausentes na V3): deletar RMA (`VIS-V1-011`,
  só V1) e deletar usuário (`VIS-V1-012`, V1 e V2).
- **2 telas inconclusivas, não contadas como gap confirmado**: `avisar_alguem.php`/
  `enviar_email.php` do V2 — stubs sem lógica de envio mesmo no próprio legado.

Nenhum destes achados foi corrigido nesta sessão — este é só o documento de
investigação, conforme escopo pedido.
