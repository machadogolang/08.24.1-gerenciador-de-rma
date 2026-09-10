# Matriz forense de paridade Legacy -> Novo (V1 / V2)

Data: 2026-09-10 (America/Sao_Paulo). Frente: `[R] PAR-FORENSE-LEGACY-01`.
Esta e a fonte canonica desta auditoria. Sentido obrigatorio de leitura:
LEGACY -> capacidade -> NOVO -> equivalente? -> completo? -> visualmente
equivalente? -> funcionalmente equivalente?

Regra de texto: nunca hifen longo, sempre hifen simples
(`docs/operacao/regra-hifen.md`). Marcadores canonicos `[ ]` / `[R]` / `[x]`
(`docs/operacao/padrao-status-plano.md`).

Legacy e SOMENTE LEITURA:
`/home/legionario/github/08.24.4-legacy-gerenciador-de-rma/legacy-source/`.

- Tema V1 novo <-> Legacy `14.6.1` (PAR14-*)
- Tema V2 novo <-> Legacy `15.8.1` (PAR15-*)

Uma rota com nome parecido NAO fecha paridade. `[x]` exige evidencia real
(codigo, teste, runtime ou documento com trecho citado).

## 0. Baseline e metodo

Baseline confirmada nesta sessao:

- `git status --short --branch` -> `## main...origin/main` (arvore limpa).
- HEAD local: `e9f2629` (`#DOC-RMA - Handoff da segunda continuacao 2026-09-10`).
- Ultimo main observado pelo dono: `e9f2629`.

Metodo (capability-first, comeca no Legacy):

1. Ler `.htaccess`, `index.php`, `page/`, `subp/`, `pp/`, `post/`, `inc/`,
   `menu*`, `menujs-right/`, `menujs-top/`, JS, forms, links, onclick, selects,
   checkboxes, tabelas, relatorios e CSS relevante.
2. Montar a lista de capacidades Legacy ANTES de olhar as rotas Laravel.
3. Procurar cada capacidade no novo (rota, controller, view, caso de uso).
4. Classificar e registrar com ID. Nada desaparece sem ID.

Se uma capacidade sumiu do novo, ela so aparece na matriz porque a auditoria
comeca no Legacy, nunca nas rotas Laravel.

### Classificacao (secao 21 da instrucao)

| Codigo | Significado |
|---|---|
| A | GAP FUNCIONAL |
| B | GAP DE DADO/INFORMACAO |
| C | GAP VISUAL |
| D | GAP DE NAVEGACAO |
| E | IMPLEMENTACAO MODERNA EQUIVALENTE |
| F | BUG LEGACY QUE NAO DEVE SER REPRODUZIDO |
| G | SUPERFICIE NOVA SEM EQUIVALENTE LEGACY |
| H | DECISAO PENDENTE |

Nao reproduzir: SQL inseguro, GET destrutivo, SHA1, ausencia de CSRF, falhas de
autorizacao e bugs comprovados.

## 1. Inventario de capacidades Legacy

### 1.1 V1 / 14.6.1 (30 capacidades inventariadas)

Fonte: `14.6.1/index.php`, `14.6.1/inc/`, `14.6.1/page/`, `14.6.1/post/`,
`14.6.1/menujs-right/`, `14.6.1/menujs-top/`. `.htaccess` do 14.6.1 e vazio
(roteamento por `index.php?page=`).

| # | Capacidade V1 | Superficie Legacy |
|---|---|---|
| 1 | Login | `inc/signin.php` (POST `signin`) |
| 2 | Sign up | `inc/signup.php` (POST `signup`) |
| 3 | Logout | `index.php` (POST `signout`) |
| 4 | Trocar para 15.8.1 | `../trocarapp.php` |
| 5 | Pagina Inicial | `inc/startpage.php` (Localizar + Novo + anotacoes) |
| 6 | Novo RMA | `menujs-top/novo.php` + `post/novo.php` |
| 7 | Localizar | `menujs-top/localizar.php` |
| 8 | Entrada | `page/entrada.php` |
| 9 | Encaminhados | `page/encaminhados.php` |
| 10 | Aguardando credito | `page/aguardandocredito.php` |
| 11 | Concluidos | `page/concluidos.php` |
| 12 | Detalhe RMA (BOLETIM DE DEFEITO) | `page/detalhes.php` + `post/processa_detalhes.php` |
| 13 | Arquivar solicitacao | `post/arquivar.php` |
| 14 | Deletar solicitacao | `post/deleta_solicitacao.php` |
| 15 | Deletar usuario | `post/deleta_usuario.php` |
| 16 | Mudar senha propria | `post/mudar_senha.php` |
| 17 | Salvar anotacoes | `post/salvarnotas.php` |
| 18 | Adicionar representante | `post/adicionar_assistencia.php` |
| 19 | Clientes | `page/cliente.php` + `menujs-right/clientes.php` |
| 20 | Fornecedores | `page/fornecedor.php` + `menujs-right/fornecedores.php` |
| 21 | Fabricantes | `page/fabricante.php` + `menujs-right/fabricantes.php` |
| 22 | Assistencias tecnicas | `page/assistencia.php`/`assistencia_tecnica.php` |
| 23 | Destinatarios | `menujs-right/destinatarios.php` |
| 24 | Creditos | `menujs-right/creditos.php` |
| 25 | Controle (painel admin) | `page/controle.php` + `menujs-right/controle.php` (9 paineis) |
| 26 | Usuarios | `menujs-right/usuarios.php` |
| 27 | Relatorios | `page/relatorios.php` (RCRD/RCD/RPEC/RMPE) |
| 28 | Ajuda / procedimento | `page/help.php` |
| 29 | Alertas | `inc/alerts.php` |
| 30 | Menu de sessao + rodape | `inc/menuright.php` + rodape do `index.php` |

### 1.2 V2 / 15.8.1 (36 capacidades inventariadas)

Fonte: `15.8.1/.htaccess` (rewrites), `15.8.1/index.php`, `15.8.1/page/`,
`15.8.1/subp/`, `15.8.1/pp/`, `15.8.1/inc/`.

| # | Capacidade V2 | Superficie Legacy |
|---|---|---|
| 1 | Login | `login.php` |
| 2 | Logout | `page/logout.php` |
| 3 | Trocar para 14.6.1 | `../trocarapp.php` |
| 4 | Inicio | `page/inicio.php` (pesquisar + Centro de Avisos) |
| 5 | Pesquisar | `page/pesquisar.php` + `subp/pesquisar_*` |
| 6 | Novo RMA | `page/novo_rma.php` + `pp/novo_rma.php` |
| 7 | Entrada | `page/entrada.php` |
| 8 | Recebido | `page/recebido.php` |
| 9 | Encaminhado | `page/encaminhado.php` |
| 10 | Concluido | `page/concluido.php` |
| 11 | Retornou | `page/retornou.php` (0 bytes - rota existe no `.htaccess`) |
| 12 | Detalhe RMA | `page/rma.php` + `pp/salvar_rma.php` |
| 13 | Info do log | `info/{numero}` |
| 14 | Creditos | `page/credito.php` / `creditos.php` |
| 15 | Clientes | `page/clientes.php` + `subp/listar_clientes.php` / `ver_cliente.php` / `apagar_cliente.php` |
| 16 | Fornecedores | `page/fornecedores.php` + `subp/...` |
| 17 | Fabricantes | `page/fabricantes.php` + `subp/...` |
| 18 | Assistencias tecnicas | `page/assistencia_tecnicas.php` + `subp/listar_assistencia_tecnicas.php` |
| 19 | Autorizadas (alias) | `subp/listar_autorizadas.php` / `ver_autorizada.php` |
| 20 | Novo usuario | `subp/novo_usuario.php` + `pp/novo_usuario.php` |
| 21 | Usuarios | `subp/usuarios.php` |
| 22 | Resetar senha | `subp/resetar_senha.php` + `pp/resetar_senha.php` |
| 23 | Mudar permissao | `subp/mudar_permissao.php` + `pp/mudar_permissao.php` |
| 24 | Apagar usuario | `subp/apagar_usuario.php` + `pp/apagar_usuario.php` |
| 25 | Alterar senha propria | `subp/senha.php` + `pp/alterar_senha.php` |
| 26 | Logs de autenticacao | `subp/logs_de_autenticacao.php` |
| 27 | Logs de modificacao | `subp/logs_de_modificacao.php` |
| 28 | Controle (hub) | `page/controle.php` + `inc/menu_controle.php` |
| 29 | Anotacoes | `page/anotacoes.php` + `pp/salvar_anotacao.php` |
| 30 | Relatorios (painel estatistico) | `page/relatorios.php` |
| 31 | Enviar e-mail | `page/enviar_email.php` |
| 32 | Avisar alguem | `page/avisar_alguem.php` |
| 33 | Marcar como | `subp/marcarcomo.php` |
| 34 | Sidebar (painel lateral) | `inc/rightmenu.php` |
| 35 | Menu principal | `inc/menu.php` |
| 36 | 403 / 404 | `page/403.php` / `page/404.php` |

## 2. Matriz PAR14-* (Legacy 14.6.1 -> Tema V1 novo)

Legenda de Tipo/Status igual as secoes 0. Rotas novas entre crase.

### 2.1 Shell, navegacao e controle

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR14-SHELL-001 | V1 | Topo/menu principal | `index.php` `#TOPO` | `temas.v1.layout` | Pag. Inicial, Novo, Localizar, Entrada, Encaminhado, Aguardando credito, Concluido!, SIGN OUT, MENU | Mesmos itens, mesma ordem | Equivalente (reconciliado VIS-V1) | E | Baixo | [x] | RenderizaTemaV1 + evidencia VIS-V1 | Manter |
| PAR14-NAV-001 | V1 | Painel MENU (sessao) | `inc/menuright.php` | `temas.v1.layout` `#JS-Sessao` | Fornecedores, Fabricantes, Assistencias, Clientes, Controle, Creditos, Relatorios, Usuarios, Trocar p/ 15.8.1 | Mesmos + Relatorio RPEC/RMPE explicitos | Equivalente com superficies dedicadas | E | Medio | [x] | RenderizaTemaV1 | Manter; RPEC/RMPE sao capacidade V1 real (ver PAR14-REL) |
| PAR14-NAV-002 | V1 | Pagina Inicial | `inc/startpage.php` | `temas.v1.rma.index` | Localizar + Novo + Quadro de Anotacoes | Localizar + Novo (anotacao no perfil) | Anotacao mudou de posicao | E | Baixo | [R] | Browser | Documentar posicao |
| PAR14-CTRL-001 | V1 | Painel Controle | `page/controle.php` + `menujs-right/controle.php` | `temas.v1.rma.controle` (`/rmas-controle`) | 9 paineis: adicionar representante, arquivar, deletar solicitacao, deletar usuario, informacao do procedimento, listar arquivadas, mudar senha | 9 paineis equivalentes; exclusoes = decisao pendente | Exclusoes deliberadamente nao reproduzidas (GET destrutivo) | E/F | Medio | [x] | VIS-V1-010/011/012/013 | Manter; decidir exclusao |
| PAR14-USR-001 | V1 | Usuarios | `menujs-right/usuarios.php` | `temas.v1.identidade.usuarios` | Colunas NOME, ENDERECO DE E-MAIL, PERMISSAO, N LOGIN; SEM acoes por linha | Colunas Nome, Endereco de e-mail, Permissao, Acoes; select de papel + SALVAR e inputs de senha + RESETAR em cada linha | Organizacao e densidade diferentes; falta N LOGIN; acoes inline modernas que o V1 nao tinha | A/C/B | Medio | [R] | Feature + Browser | Reavaliar V1 apos V2 (prioridade 1 e V2) |
| PAR14-USR-002 | V1 | Resetar senha | `post/deleta_usuario.php` / `post/mudar_senha.php` | rota `identidade.usuarios.resetar-senha` | Senha do proprio usuario via POST | Reset por operador via POST seguro | Equivalente moderno mais seguro | E | Baixo | [x] | GerenciarUsuariosTest | Manter |

### 2.2 Detalhe RMA V1

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR14-RMA-DET-001 | V1 | Detalhe RMA | `page/detalhes.php` | `temas.v1.rma.show` | Formulario BOLETIM DE DEFEITO com todos os campos editaveis inline | Formulario inline equivalente | Equivalente (reconciliado A5/2beecb3) | E | Alto | [x] | PAR-DET-V1-EDIT-01 + Browser | Manter |
| PAR14-RMA-STOCK-001 | V1 | Estoque | `page/detalhes.php` (checkbox `marcarestoque`) | `temas.v1.rma.show` rodape | Checkbox real; label `O ITEM E DO ESTOQUE` / `ITEM NAO E DO ESTOQUE` | Checkbox real + mesmos textos (`data-texto-true/falso`); persistencia POST/PUT | Equivalente; falta medir geometria/label no runtime | E/C | Medio | [R] | Browser Legacy x novo | Comparar geometria (secao 8 da instrucao) |
| PAR14-RMA-CREDIT-001 | V1 | Credito | `page/detalhes.php` (checkbox `creditodisponivel`) | `temas.v1.rma.show` rodape | Checkbox real; label `CREDITO DISPONIVEL` / `MARQUE P/ VALIDAR CREDITO` | Checkbox real + mesmos textos | Equivalente; falta medir geometria e relacao com `GERADO CREDITO` | E/C | Medio | [R] | Browser Legacy x novo | Comparar geometria |
| PAR14-RMA-DET-002 | V1 | Acoes de ciclo | `page/detalhes.php` select `acao` + OK no rodape | `temas.v1.rma.show` | Select de acao unico no rodape (SALVAR/RETORNAR P/ ENTRADA/RECEBER/ENCAMINHAR/CONCLUIR) | Select de acao no rodape + bloco `Mais acoes de ciclo de vida` | Equivalente com bloco extra recolhido | E | Baixo | [x] | PAR-DET-V1-EDIT-01 | Manter |
| PAR14-RMA-DET-003 | V1 | Campos fiscais | `page/detalhes.php` | `temas.v1.rma.show` | nfentrada_cli, nfretorno_cli, nfdevolucaodevenda, nfcompra/venda/remessa/retorno + emissao + chave | Todos presentes | Equivalente | E | Medio | [x] | PAR-DET-V1-EDIT-01 | Manter |

### 2.3 Relatorios V1 (RCD / RPEC / RMPE)

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR14-REL-001 | V1 | Hub de relatorios | `page/relatorios.php` sem `id` | nao existe hub | Texto `Nenhum relatorio p/ exibir!` quando sem `id` | Sem superficie de hub (links diretos por relatorio) | Falta o hub contextual | D | Baixo | [R] | Browser | Avaliar hub (ou nao) |
| PAR14-REL-RPEC-001 | V1 | RPEC colunas | `page/relatorios.php` bloco `id==RPEC` | `rma.relatorios._conteudo_rpec` | 10 colunas: ENTRADA, FABRICANTE, DESCRICAO, EMPRESA, MODELO, NF C, NF V, ORIGEM, DESTINATARIO, OS | 3 colunas: #, Descricao, Status | Simplificacao forte de contrato de tela | A/C | Alto | [R] | Browser + Feature | Reabrir contrato RPEC |
| PAR14-REL-RPEC-002 | V1 | RPEC selecao | idem | idem | WHERE status IN (ENTRADA,RECEBIDO,ENCAMINHADO) AND nfremessa < 1 AND marcarestoque = 1 ORDER BY modelo | Service `RelatorioProdutosEmEstoqueParaContagem` | Conferir regra (filtro Status extra no novo) | A | Alto | [R] | Unit/Feature | Comparar criterio |
| PAR14-REL-RPEC-003 | V1 | RPEC totais | idem | idem | `Valor Total`, `DATA DO RELATORIO`, `Quantidade Total`, `Quantidade sem valor` | Nenhum totalizador | Faltam totalizadores | B/C | Medio | [R] | Feature | Reabrir |
| PAR14-REL-RPEC-004 | V1 | RPEC info adicional | idem (`rpec_informacaoadicional`) | nao existe | Textarea persistida em `relatorio.informacaoadicional` + botao SALVAR | Nenhum campo persistido | Falta persistencia de informacao adicional no relatorio | A/B | Medio | [R] | Feature | Reabrir |
| PAR14-REL-RPEC-005 | V1 | RPEC origem truncada | idem | nao existe | MERCADO LIVRE -> M LIVRE; Leilao -> LEILAO; Licitacao -> LICITACAO | Status cru | Falta transformacao de rotulo | C | Baixo | [R] | Feature | Reabrir |
| PAR14-REL-RCD-001 | V1 | RCD colunas | `page/relatorios.php` bloco `id==RCRD` | `rma.relatorios._conteudo_rcd` | 11 colunas: CONCLUIDO, FABRICANTE, DESCRICAO, EMPRESA, MODELO, NF C, NF R, VALOR, PROTOCOLO, DESTINATARIO, OS | 3 colunas: #, Descricao, Solucao | Simplificacao forte | A/C | Alto | [R] | Browser + Feature | Reabrir |
| PAR14-REL-RCD-002 | V1 | RCD regra | idem | idem | WHERE status='CONCLUIDO' AND creditodisponivel = 1 ORDER BY protocolo, destinatario | Service `RelatorioCreditosDisponiveis` | Conferir criterio/ordem | A | Alto | [R] | Unit/Feature | Comparar |
| PAR14-REL-RCD-003 | V1 | RCD totais + info adicional | idem | idem | `Valor Total`, data, quantidade total, quantidade sem valor + `rcrd_informacaoadicional` | Nenhum | Faltam totais e info adicional | B/C | Medio | [R] | Feature | Reabrir |
| PAR14-REL-RMPE-001 | V1 | RMPE colunas | `page/relatorios.php` bloco `id==RMPE` | `rma.relatorios._conteudo_rmpe` | 11 colunas: ENCAMINHADO, FABRICANTE, DESCRICAO, EMPRESA, MODELO, NF C, NF V, NF R, VALOR, DESTINATARIO, OS | 3 colunas: #, Descricao, Encaminhado em | Simplificacao forte | A/C | Alto | [R] | Browser + Feature | Reabrir |
| PAR14-REL-RMPE-002 | V1 | RMPE regra/totais/info | idem | idem | WHERE (status=ENCAMINHADO OR RECEBIDO) AND nfremessa > 0 AND marcarestoque=1 ORDER BY encaminhado DESC + totais + `rmpe_informacaoadicional` | Service + filtro por data | Conferir criterio, totais e info adicional | A/B | Alto | [R] | Unit/Feature | Comparar |
| PAR14-REL-VIS-001 | V1 | Impressao | nota `CTRL + P ... DOPDF` | `.relatorio-print` | Impressao limpa | Impressao limpa | Equivalente | E | Baixo | [x] | PAR-RES-F-01 | Manter |

## 3. Matriz PAR15-* (Legacy 15.8.1 -> Tema V2 novo)

### 3.1 Shell, navegacao e sidebar

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR15-SHELL-001 | V2 | Menu principal | `inc/menu.php` | `temas.v2.layout` `header-v2` | Abas Inicio..Concluido + dropdown Menu + Logout | Mesmas abas + dropdown + Logout | Equivalente (PAR-RES-A) | E | Baixo | [x] | RenderizaTemaV2 + Browser | Manter |
| PAR15-NAV-001 | V2 | Dropdown Menu | `inc/menu.php` | `temas.v2.layout` dropdown | Creditos, Assistencias, Fabricantes, Fornecedores, Clientes, Relatorios, Anotacoes, Controle, Trocar p/ 14.6.1 | Creditos, Assistencias, Fabricantes, Fornecedores, Clientes, Relatorio RCD/RPEC/RMPE, Anotacoes, Controle, Usuarios, Trocar | Relatorios virou 3 links; Usuarios adicionado | D/G | Medio | [R] | Browser | Ver PAR15-REL-001/008 |
| PAR15-NAV-002 | V2 | Sidebar | `inc/rightmenu.php` | `temas.v2.rma._painel_lateral` | DEU ENTRADA HOJE, RECEBIDOS, ENCAMINHADOS, LAST 10 CONCLUIDOS, DESTINATARIOS, TRANSPORTE P/ PORTO A, URGENTE, PENDENTE CREDITO, CREDITO DISPONIVEL, + | Mesmos blocos + contadores | Equivalente (EVO/CP19) | E | Baixo | [x] | Browser | Manter |
| PAR15-NAV-003 | V2 | Rodape | `inc/footer.php` | `temas.v2.layout` | Designed by + licenca | Mesmos textos | Equivalente | E | Baixo | [x] | Browser | Manter |

### 3.2 Usuarios V2 (prioridade 1)

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR15-USR-001 | V2 | Tela Usuarios | `subp/usuarios.php` | `temas.v2.identidade.usuarios` | Tabela Nome, E-mail, QT Login, Ultimo login, Permissao, Acao; linhas ~30px | Tabela Nome, E-mail, Papel, Acoes; select+Salvar papel e 2 inputs de senha + Resetar dentro da linha | Densidade, colunas, informacao e organizacao diferem fortemente; QT Login/Ultimo login sumiram | A/C/B | Alto | [x] | Feature (UsuariosV2ParidadeTest) + Browser (ParidadeUsuariosV2.spec.ts) | CORRIGIDO: colunas historicas, 3 acoes compactas, superficies V2 dedicadas |
| PAR15-USR-002 | V2 | QT Login / Ultimo login | `subp/usuarios.php` (bind `quantidade_login`,`ultimo_login`) | ausente | Contagem de logins + data do ultimo login | Ausente da tela | Informacao operacional perdida visualmente | B | Medio | [R] | Feature | Projetar de `tentativas_de_acesso` (ver 3.6) |
| PAR15-USR-003 | V2 | Acoes compactas | `subp/usuarios.php` (3 icones) | ausente | `resetar_senha`, `mudar_permissao`, `apagar_usuario` por icone | Formularios inline | Falta acao compacta; fluxo diferente | A/C | Alto | [R] | Browser | Superficies V2 dedicadas |
| PAR15-USR-004 | V2 | Apagar usuario | `subp/apagar_usuario.php` + `pp/apagar_usuario.php` | ausente | Hard delete com confirmacao (`pms>1` e `pms>alvo`) | Nenhuma | Sem equivalente; hard delete cascatearia auditoria (`modificacoes_de_rma.user_id` cascadeOnDelete) | H/F | Alto | [R] | Feature | Decisao de produto; nao reproduzir cascata cega |
| PAR15-USR-005 | V2 | Resetar senha | `pp/resetar_senha.php` | `identidade.usuarios.resetar-senha` | Gera senha aleatoria `@dddd` e envia por e-mail | Operador digita nova senha (min 8 + confirmacao) | Sem envio de e-mail no V3 | E | Medio | [R] | Feature | Superficie V2 dedicada |
| PAR15-USR-006 | V2 | Mudar permissao | `pp/mudar_permissao.php` | `identidade.usuarios.update` | Select -1/1/2 (Bloqueado/Leitura/Leitura e modificacao) | Select com 5 papeis (`Papel`) | Nomenclatura/estrutura de papel mudou | E | Medio | [R] | Feature | Rotulo legado + papeis modernos |
| PAR15-USR-007 | V2 | Novo usuario | `subp/novo_usuario.php` + `pp/novo_usuario.php` | nao existe rota de criacao | Form nome/email/senha/permissao | Ausente | Falta criacao de usuario | A | Medio | [R] | Feature | Registrar; decidir implementacao |
| PAR15-USR-008 | V2 | Permissao como rotulo | `subp/usuarios.php` | `Papel` cru | `Leitura` / `Leitura e modificacao` / `Bloqueado` | Nome tecnico do papel | Rotulo diferente do historico | C | Baixo | [R] | Feature | Mapear rotulo legado |

### 3.3 Detalhe RMA V2 (credito, estoque, acoes)

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR15-RMA-DET-001 | V2 | Acao superior | `page/rma.php` select `selectacaoup` + botao OK no TOPO (linhas 146-166) | `temas.v2.rma._form_detalhe` | Select SALVAR/RETORNAR P/ ENTRADA/RECEBER/ENCAMINHAR/CONCLUIR + OK no topo E select `selectacaodown` + OK no rodape | Topo tem apenas `<button name="acao" value="salvar">SALVAR</button>`; select + OK so no rodape | Faltou o select operacional + OK do topo | A/C | Alto | [x] | Feature (DetalheRmaV2AcaoSuperiorTest) + Browser pendente (PF-14) | CORRIGIDO: select+OK no topo e rodape; controller resolve por bloco |
| PAR15-RMA-DET-002 | V2 | Acao inferior | `page/rma.php` select `selectacaodown` + OK | rodape `detalhe-rma-v2__acoes-finais` | Select + OK no rodape | Select + OK no rodape | Equivalente | E | Baixo | [x] | Feature | Manter |
| PAR15-RMA-STOCK-001 | V2 | Estoque | `page/rma.php` select `marcarestoque` (Nao/Sim), 3a coluna | `_form_detalhe` select `marcarestoque` | Select Nao/Sim, label `E um produto do estoque ?` | Select Nao/Sim, mesmo label | Equivalente; medir grid/largura/cursor | E/C | Medio | [R] | Browser | Medir geometria |
| PAR15-RMA-CREDIT-001 | V2 | Credito | `page/rma.php` select `creditodisponivel` (Nao/Sim) | `_form_detalhe` select `credito_disponivel` | Select Nao/Sim, label `E credito disponivel ?` | Select Nao/Sim, mesmo label | Nome do campo diverge (`creditodisponivel` x `credito_disponivel`) | E | Baixo | [R] | Feature | Conferir binding/persistencia |
| PAR15-RMA-DET-003 | V2 | Campos fiscais | `page/rma.php` | `_form_detalhe` | nfvenda/compra/remessa/retorno + emissao + chave, nfdevolucaodevenda, nfentrada_cli, nfretorno_cli, valor, cliente_email | Todos presentes | Equivalente | E | Medio | [x] | PAR-V2-DETAIL-02 | Manter |
| PAR15-RMA-DET-004 | V2 | snretorno | `page/rma.php` input hidden `snretorno` | ausente | Campo oculto transportado | Ausente | Campo oculto sem efeito visual | E | Baixo | [ ] | Feature | Conferir se ha uso real |
| PAR15-RMA-DET-005 | V2 | Rastreio | `page/rma.php` rastreio_ida/retorno | `_form_detalhe` | Codigo de rastreio ida e retorno | Ambos | Equivalente | E | Baixo | [x] | PAR-V2-DETAIL-02 | Manter |
| PAR15-RMA-DET-006 | V2 | Politica de garantia | `page/rma.php` | `_form_detalhe` | Textarea politica (somente leitura) | Bloco politica (somente leitura) | Equivalente | E | Baixo | [x] | PAR-V2-DETAIL-02 | Manter |

### 3.4 Auditoria / Controle / Logs V2

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR15-AUD-001 | V2 | Organizacao Controle | `page/controle.php` + `inc/menu_controle.php` (5 subpaginas: logs_de_autenticacao, logs_de_modificacao, senha, novo_usuario, usuarios) | `/rmas-historico`, `/historico-de-acesso`, `/perfil/senha`, `/usuarios` | Controle como hub com breadcrumb de 5 itens | Superficies separadas, sem hub Controle V2 | Organizacao/breadcrumb divergem | D/C | Medio | [R] | Browser | Avaliar hub V2 |
| PAR15-AUD-002 | V2 | Logs de modificacao | `subp/logs_de_modificacao.php` | `rma.historico._conteudo` | DATA, BD NUMERO, FABRICANTE, DESCRICAO, MODELO, NAVEGADOR, acao Ver | Data, RMA, Usuario, Acao, IP | Contrato de tela/dados diferente | A/B/C | Alto | [R] | Browser + Feature | View tematica V2 preservando colunas do Legacy |
| PAR15-AUD-003 | V2 | Acao Ver do log | `subp/logs_de_modificacao.php` (`info/{numero}`) | link `rmas.show` | Icone Ver abre info do RMA | Link `#id` para o detalhe | Forma da acao difere | C | Baixo | [R] | Browser | Reavaliar |
| PAR15-AUD-004 | V2 | Logs de autenticacao | `subp/logs_de_autenticacao.php` | `identidade.historico-de-acesso._conteudo` | DATA, USUARIO, SISTEMA OPERACIONAL, NAVEGADOR, IP, APP, RETORNO + `Quantidade retornada` | Data, E-mail informado, Usuario, IP, Resultado | Faltam SO, navegador, app e total | A/B | Medio | [R] | Feature | Ver 3.6 (derivabilidade) |
| PAR15-AUD-005 | V2 | Geometria Controle | `page/controle.php` | hub de historico | Breadcrumb + menu Controle | Sem menu Controle | Falta geometria/breadcrumb | C | Baixo | [R] | Browser | Apos PAR15-AUD-001 |
| PAR15-AUD-006 | V2 | Alterar senha | `subp/senha.php` | `/perfil/senha` (V2) | Troca senha sem senha atual | Exige senha atual + confirmacao | Equivalente moderno mais seguro | E | Baixo | [x] | PAR-RES-E-04 | Manter |
| PAR15-AUD-007 | V2 | Anotacoes | `page/anotacoes.php` | `/anotacoes` (V2) | Quadro de anotacoes | Quadro dedicado | Equivalente | E | Baixo | [x] | PAR-RES-E-04 | Manter |

### 3.5 Relatorios V2

| ID | Tema | Superficie | Legacy | Novo | Legacy possui | Novo possui | Diferenca | Tipo | Impacto | Status | Teste | Decisao |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PAR15-REL-001 | V2 | Pagina Relatorios | `page/relatorios.php` | nao existe | Painel estatistico completo (Situacao, Resolucao, Origem, Fornecedores, NF, Dados do sistema, series mensais/anuais) | Nao existe hub V2; dropdown aponta para RCD/RPEC/RMPE | Superficie inteira ausente/diferente | A/C | Alto | [R] | Browser + Feature | Reabrir contrato Relatorios V2 |
| PAR15-REL-002 | V2 | Situacao | idem | ausente | Entrada, Recebido, Encaminhado, Concluido (contagens) | ausente | Falta bloco Situacao | A | Medio | [R] | Feature | Reabrir |
| PAR15-REL-003 | V2 | Resolucao | idem | ausente | Reparo, Troca produto, Troca peca, Devolucao, Reembolso, Reparo pelo RMA, Testado Tudo OK, Orcamento pago, Descrito na observacao, Sem garantia, PROCON, Pendente credito, Gerado credito | ausente | Falta bloco Resolucao | A | Medio | [R] | Feature | Reabrir |
| PAR15-REL-004 | V2 | Origem | idem | ausente | Licitacao, Cliente, Mercado Livre, AC, Leilao, Loja, Casa, Unknown | ausente | Falta bloco Origem | A | Medio | [R] | Feature | Reabrir |
| PAR15-REL-005 | V2 | NF | idem | ausente | Sem NF Compra, Sem NF Venda, Sem nenhuma nota | ausente | Falta bloco NF | A | Medio | [R] | Feature | Reabrir |
| PAR15-REL-006 | V2 | Dados do sistema | idem | ausente | Quantidade RMA/Clientes/Fornecedores/Fabricantes/Assistencias | ausente | Falta bloco Dados do sistema | A | Medio | [R] | Feature | Reabrir |
| PAR15-REL-007 | V2 | Series mensais/anuais | idem | ausente | Entrada/Encaminhado/Concluido por mes/ano + totais + taxas | ausente | Falta serie historica | A | Alto | [R] | Feature | Reabrir |
| PAR15-REL-008 | V2 | RPEC no V2 | 15.8.1 NAO possui RPEC/RCD/RMPE (confirmado por varredura) | `/rmas-relatorios/rpec` (V2) | Superficie inexistente no Legacy V2 | Rota RPEC servida tambem no V2 | Capacidade moderna sem equivalente Legacy V2 | G | Medio | [R] | Browser | Decidir apresentacao V2 de Relatorios |
| PAR15-REL-009 | V2 | RCD/RMPE no V2 | 15.8.1 NAO possui | `/rmas-relatorios/rcd` `/rmpe` (V2) | inexistente | rotas servidas tambem no V2 | Capacidade moderna sem equivalente Legacy V2 | G | Medio | [R] | Browser | Igual PAR15-REL-008 |
| PAR15-REL-010 | V2 | Reuso de markup | V1 e V2 organizavam relatorios de forma distinta | `temas.v1.rma.relatorios.rpec` e `temas.v2.rma.relatorios.rpec` incluem `rma.relatorios._conteudo_rpec` | Estruturas diferentes | Mesmo conteudo compartilhado | Reuso de markup mascara contratos diferentes | C/A | Medio | [R] | Feature | Separar views por tema se necessario |

### 3.6 Derivabilidade de dados (sem inventar)

Base: `database/migrations/2026_08_25_000001_create_tentativas_de_acesso_table.php`,
`2026_09_01_000000_create_modificacoes_de_rma_table.php`,
`app/Models/ModificacaoDeRma.php`, `app/Rma/Infraestrutura/Migracao/Importadores/`.

| ID | Dado Legacy | Origem Legacy | No novo | Derivavel? | Como | Decisao |
|---|---|---|---|---|---|---|
| PAR15-DATA-001 | QT Login | `usuario.quantidade_login` | nao migrado (decisao arquitetural antiga) | Sim | `COUNT(tentativas_de_acesso WHERE user_id=? AND resultado='Permitido')` | Projection/query, sem duplicar estado |
| PAR15-DATA-002 | Ultimo login | `usuario.ultimo_login` | nao migrado | Sim | `MAX(created_at)` de `tentativas_de_acesso` permitidas | Projection/query |
| PAR15-DATA-003 | Log: FABRICANTE/DESCRICAO/MODELO | `modificacao` (snapshot) | `modificacoes_de_rma.estado_apos` (json) | Sim (migrado) | `estado_apos.fabricante/descricao/modelo` no historico migrado; RMA atual em registros novos | View V2 pode projetar |
| PAR15-DATA-004 | Log: NAVEGADOR | `modificacao.navegador` | `modificacoes_de_rma.user_agent` | Sim (migrado) | `user_agent` | Projetar |
| PAR15-DATA-005 | Log auth: SISTEMA OPERACIONAL | `log.sistema_operacional` | nao migrado (decisao) | Nao (historico exato) | perdido nao migrado | Registrar PAR15-DATA-005 (diferenca de dado) |
| PAR15-DATA-006 | Log auth: APP | `log.app` | nao migrado (decisao) | Nao (historico exato) | perdido nao migrado | Registrar PAR15-DATA-006 |

## 4. Casos de ciclo de vida e listagens

| ID | Tema | Superficie | Legacy | Novo | Diferenca | Tipo | Status | Decisao |
|---|---|---|---|---|---|---|---|---|
| PAR15-RMA-LIST-001 | V2 | Entrada/Recebido/Encaminhado/Concluido | `page/entrada.php`, `recebido.php`, `encaminhado.php`, `concluido.php` | abas de `temas.v2.rma.index` + `_tabela_*` | Conferir colunas/zebra/acoes por aba | C | [R] | Browser por aba |
| PAR15-RMA-LIST-002 | V2 | Retornou | `page/retornou.php` (0 bytes) | nao existe | Rota morta no Legacy | F | [x] | Nao reconstruir (LEG-RMA-016) |
| PAR15-RMA-LIST-003 | V2 | Novo RMA | `page/novo_rma.php` | aba Novo (`_form_novo_v2`) | Equivalente (PAR-V2-NOVO-01) | E | [x] | Manter |
| PAR15-SEARCH-001 | V2 | Pesquisar | `subp/pesquisar_rma.php`, `pesquisar_sn.php`, `pesquisar_nf.php`, `pesquisar_descricao.php` | aba Pesquisar (`_pesquisar_conteudo`) | Conferir abrangencia de campos | A | [R] | PAR-RMA-003 |
| PAR15-CREDIT-001 | V2 | Creditos | `page/credito.php` | `/rmas-credito` | Conferir acoes/colunas | A | [R] | Reabrir |
| PAR15-PART-001 | V2 | Parceiros CRUD | `subp/listar_*` / `ver_*` / `novo_*` / `apagar_*` | `temas.v2.parceiros.*` | Detalhe/RMAs do parceiro ainda ausentes | A | [R] | PAR-PARCEIRO-001 |
| PAR15-LOG-001 | V2 | Login/Logout/Troca de versao | `login.php`, `page/logout.php`, `../trocarapp.php` | `identidade.login`, `logout`, `tema.alternar` | Equivalente moderno | E | [x] | Manter |
| PAR15-EMAIL-001 | V2 | Enviar e-mail / Avisar alguem | `page/enviar_email.php`, `page/avisar_alguem.php` | nao existe | Decisao de produto registrada | H | [R] | DEC-02 |
| PAR15-RMA-MARCAR-001 | V2 | Marcar como | `subp/marcarcomo.php` | ciclo moderno no detalhe | Equivalente por acao segura | E | [R] | Conferir |

## 5. Detalhe RMA - inventario campo a campo

### 5.1 V1 `page/detalhes.php` x `temas.v1.rma.show`

| Campo | Tipo Legacy | Editavel | Obrigatorio | Opcoes | Posicao | Novo V1 | Persiste | Status |
|---|---|---|---|---|---|---|---|---|
| numero | hidden/disabled | nao | - | - | topo | disabled | sim | [x] |
| fabricante | input + datalist | sim | - | datalist | grade | select | sim | [x] |
| descricao | input + datalist | sim | sim | datalist | grade | input | sim | [x] |
| modelo | input + datalist | sim | - | datalist | grade | input | sim | [x] |
| os | input | sim | - | - | grade | input | sim | [x] |
| origem | input + datalist | sim | sim | Loja, Licitacao, Cliente, Mercado Livre, Leilao | grade | input | sim | [x] |
| sn | input | sim | - | - | grade | input | sim | [x] |
| empresa | input + datalist | sim | - | Cellsystem, T A, Registros Ativos, Expert, Informatica, R A, Cliente | grade | input | sim | [x] |
| pn | input | sim | - | - | grade | input | sim | [x] |
| snid | input | sim | - | - | grade | input | sim | [x] |
| prazo/tempo | disabled | nao | - | - | grade | disabled | nao | [x] |
| cliente | input | sim | - | - | grade | input | sim | [x] |
| nfentrada_cli | input | sim | - | - | grade | input | sim | [x] |
| nfretorno_cli | input | sim | - | - | grade | input | sim | [x] |
| rastreio_ida | input | sim | - | - | grade | input | sim | [x] |
| rastreio_retorno | input | sim | - | - | grade | input | sim | [x] |
| nfcompra/_emissao/_chave | input | sim | - | - | grade | input | sim | [x] |
| nfvenda/_emissao/_chave | input | sim | - | - | grade | input | sim | [x] |
| nfremessa/_emissao/_chave | input | sim | - | - | grade | input | sim | [x] |
| nfretorno/_emissao/_chave | input | sim | - | - | grade | input | sim | [x] |
| destinatario | input + datalist | sim | - | - | grade | select | sim | [x] |
| nfdevolucaodevenda | input | sim | - | - | grade | input | sim | [x] |
| valor | input | sim | - | format 2 decimais | grade | input | sim | [x] |
| snretorno | input | sim | - | - | grade | input | sim | [x] |
| destinatario_email | input | sim | - | - | grade | input | sim | [x] |
| destinatario_fone | input + datalist | sim | - | - | grade | input | sim | [x] |
| protocolo | input | sim | - | - | grade | input | sim | [x] |
| solucao | select | sim | - | 15 opcoes | grade | select | sim | [x] |
| defeito | input | sim | - | - | grade | input | sim | [x] |
| observacao | textarea | sim | - | - | grade | textarea | sim | [x] |
| status | disabled | nao | - | - | grade | derivado | - | [x] |
| marcarestoque | checkbox | sim | - | - | rodape | checkbox | sim | [R] |
| creditodisponivel | checkbox | sim | - | - | rodape | checkbox | sim | [R] |
| politica de garantia | textarea disabled | nao | - | - | rodape | textarea disabled | - | [x] |
| acao do ciclo | select + OK | sim | - | SALVAR/RETORNAR/RECEBER/ENCAMINHAR/CONCLUIR | rodape | select + OK | - | [x] |

### 5.2 V2 `page/rma.php` x `temas.v2.rma._form_detalhe`

| Campo | Tipo Legacy | Editavel | Obrigatorio | Opcoes | Posicao | Novo V2 | Persiste | Status |
|---|---|---|---|---|---|---|---|---|
| numero | hidden | nao | - | - | hidden | breadcrumb | - | [x] |
| urlbd | hidden | nao | - | - | hidden | nao existe | - | [x] |
| usuario | hidden | nao | - | - | hidden | sessao | - | [x] |
| snretorno | hidden | nao | - | - | hidden | ausente | - | [ ] |
| selectacaoup | select + OK | sim | - | SALVAR/RETORNAR/RECEBER/ENCAMINHAR/CONCLUIR | TOPO | ausente (so botao SALVAR) | - | [R] |
| descricao | input + datalist | sim | sim | datalist | 1a col | input | sim | [x] |
| modelo | input + datalist | sim | - | datalist | 1a col | input | sim | [x] |
| fabricante | input + datalist | sim | - | datalist | 1a col | select | sim | [x] |
| sn | input | sim | - | - | 1a col | input | sim | [x] |
| snid | input | sim | - | - | 1a col | input | sim | [x] |
| pn | input | sim | - | - | 1a col | input | sim | [x] |
| os | input | sim | - | - | 2a col | input | sim | [x] |
| origem | select | sim | sim | Unknown, Loja, Casa, Cliente, Licitacao, Leilao, Mercado Livre, Credito, AC | 2a col | select (mesmas) | sim | [x] |
| prioridade | select | sim | sim | Baixa, Normal, Alta | 2a col | select | sim | [x] |
| protocolo | input | sim | - | - | 2a col | input | sim | [x] |
| defeito | textarea | sim | - | - | 2a col | textarea | sim | [x] |
| marcarestoque | select | sim | sim | Nao, Sim | 3a col | select | sim | [R] |
| empresa | input | sim | - | - | 3a col | input | sim | [x] |
| creditodisponivel | select | sim | sim | Nao, Sim | 3a col | select `credito_disponivel` | sim | [R] |
| entrada/recebido/encaminhado/concluido | input disabled | nao | - | - | 4a col | valores | - | [x] |
| tempo | input disabled | nao | - | - | 4a col | calculado | - | [x] |
| nfvenda/_emissao/_chave | input | sim | - | - | 2a linha | input | sim | [x] |
| cliente/cliente_email | input + datalist | sim | - | datalist | 2a linha | input | sim | [x] |
| nfdevolucaodevenda/nfentrada_cli/nfretorno_cli | input | sim | - | - | 2a linha | input | sim | [x] |
| nfcompra/_emissao/_chave | input | sim | - | - | 2a linha | input | sim | [x] |
| fornecedor | input + datalist | sim | - | datalist | 2a linha | select | sim | [x] |
| nfremessa/_emissao/_chave | input | sim | - | - | 3a linha | input | sim | [x] |
| valor | input | sim | - | 2 decimais | 3a linha | input | sim | [x] |
| destinatario/fone/email | input + datalist | sim | - | datalist | 3a linha | select + inputs | sim | [x] |
| rastreio_ida | input | sim | - | - | 3a linha | input | sim | [x] |
| nfretorno/_emissao/_chave | input | sim | - | - | 4a linha | input | sim | [x] |
| lancadoretorno | select | sim | - | "", PENDENTE, NF DE DEVOLUCAO, SEM MOVIMENTACAO, NAO, SIM | 4a linha | select | sim | [x] |
| rastreio_retorno | input | sim | - | - | 4a linha | input | sim | [x] |
| solucao | select | sim | - | 15 opcoes | 4a linha | select | sim | [x] |
| observacao | textarea | sim | - | - | rodape | textarea | sim | [x] |
| politica de garantia | textarea disabled | nao | - | - | rodape | textarea disabled | - | [x] |
| selectacaodown | select + OK | sim | - | SALVAR/RETORNAR/RECEBER/ENCAMINHAR/CONCLUIR | RODAPE | select + OK | - | [x] |

## 6. Fila de correcao priorizada

Ordem definida pela instrucao (secao 23). Nada aqui e "so parecer": cada item
termina em codigo, teste dirigido, browser Legacy x novo, matriz atualizada,
plano atualizado, `git diff --check`, commit e continuacao.

| Ordem | ID | Resumo da correcao | Status |
|---|---|---|---|
| 1 | PAR15-USR-001 | Restaurar organizacao historica de /v2/usuarios (colunas + 3 acoes compactas) preservando HTTP moderno | [x] |
| 2 | PAR15-RMA-DET-001 | Restaurar select operacional + OK no topo do detalhe RMA V2 | [x] |
| 3 | PAR14-RMA-STOCK-001 / PAR14-RMA-CREDIT-001 / PAR15-RMA-STOCK-001 / PAR15-RMA-CREDIT-001 | Gaps de credito/estoque medidos no runtime | [ ] |
| 4 | PAR15-AUD-001..005 | Auditoria/historico V2 (colunas, acao Ver, logs de autenticacao, geometria) | [ ] |
| 5 | PAR14-REL-RPEC-001..005 | RPEC V1 (colunas, selecao, totais, info adicional, rotulos) | [ ] |
| 6 | PAR14-REL-RCD-001..003 | RCD V1 (colunas, regra, totais, info adicional) | [ ] |
| 7 | PAR15-REL-001..010 | Relatorios V2 (painel estatistico, blocos e superficie) | [ ] |
| 8 | demais | Gaps restantes da matriz (parceiros, busca, credito, usuarios V1) | [ ] |

## 7. Decisoes pendentes abertas

| ID | Assunto | Por que esta pendente |
|---|---|---|
| PAR15-USR-004 | Exclusao definitiva de usuario | Hard delete do Legacy cascatearia `modificacoes_de_rma` (auditoria); precisa decisao de produto/seguranca |
| PAR15-USR-007 | Criacao de usuario | Sem rota no V3; precisa decidir contrato e Policy |
| PAR15-REL-008/009 | RPEC/RCD/RMPE no V2 | Superficie sem equivalente no Legacy V2; decidir como o V2 apresenta Relatorios |
| PAR15-EMAIL-001 | Avisar alguem / enviar e-mail | DEC-02 ja registrada; sem implementacao |
| PAR14-REL-001 | Hub de Relatorios V1 | Legacy tinha hub vazio; avaliar se reproduz |

## 8. Proximo passo

Comecar a fila pela prioridade 1 (PAR15-USR-001), em ciclo:
investigar -> codigo -> teste dirigido -> browser Legacy x novo -> atualizar
matriz -> atualizar plano -> `git diff --check` -> commit -> continuar.
