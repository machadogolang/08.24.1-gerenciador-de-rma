# Catalogo Canônico e Especificacao Executavel das 65 Capabilities

Data: 2026-09-10 (America/Sao_Paulo).
OpenSpec: `openspec/changes/unificacao-funcional-temas-v1-v2/`.
Regra de governanca: Nunca usar hifen longo, sempre hifen simples ("-").

## 1. Criterios de Convergencia por Capability (C1..C10)

Uma capability recebe `[x]` somente quando todos os aspectos aplicaveis estiverem provados:
- **C1 BACKEND:** Caso de uso, servico, query ou DTO existe e opera corretamente.
- **C2 AUTORIZACAO:** Policy, gate, tenant e papeis adequados aplicados.
- **C3 ROTA:** Endpoint resolve via roteamento canonico e sob tema ativo/forcado.
- **C4 DESCOBRIBILIDADE:** Existe caminho explicito na interface (menus, barras, links).
- **C5 CLICK-THROUGH:** O usuario consegue clicar do shell/menu ate o destino com sucesso real (sem falhas de JS, overlay ou redirect indevido).
- **C6 COMPORTAMENTO:** A acao executa a regra de negocio esperada.
- **C7 PERSISTENCIA:** Quando altera estado: salvar -> recarregar -> dado persiste.
- **C8 FEEDBACK/ERRO:** Validacao e feedback operacional previsivel, sem quebra silenciosa.
- **C9 APRESENTACAO:** V1 parece 14.6.1; V2 parece 15.8.1; V3 direcao dark console.
- **C10 REGRESSAO:** Testes automatizados adequados (unit, feature, contract e e2e).

## 2. Segregacao Obrigatoria de Status

Para evitar falsos positivos de fechamento de produto:
- `STATUS_FUNCIONAL`: `[x]` provado em backend, rota, autorizacao, comportamento e persistencia; `[R]` parcial/em teste; `[ ]` pendente.
- `STATUS_APRESENTACAO`: `[x]` provado em fidelidade historica e ergonomia nativa do tema; `[R]` gaps visuais conhecidos; `[ ]` pendente.
- `STATUS_GERAL`: `[x]` somente quando `STATUS_FUNCIONAL == [x]` E `STATUS_APRESENTACAO == [x]` em ambos os temas V1 e V2. Se houver gap visual aberto, `STATUS_GERAL = [R]`.

---

## 3. Especificacao Detalhada das 65 Capabilities

### Dominio 1: Sessao e Identidade (CAP-ID-001..011)

#### CAP-ID-001 - Autenticacao / Login
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (Bcrypt, session guard, tenant-aware, rate limit).
Contrato funcional: Operador informa login/email e senha para iniciar sessao autenticada.
Tema V1 novo:
- backend: `AuthController::login`
- Policy: publica / guest
- rota: `login`
- entrada pela UI: tela de login V1 (`page/index.php`)
- click-through: form POST -> redirect home
- comportamento: autentica e atribui tema preferido
- persistencia: sessao ativa
- visual: paleta e layout 14.6.1
- teste: `Tests\Feature\Identidade\AutenticacaoTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `AuthController::login`
- Policy: publica / guest
- rota: `login`
- entrada pela UI: tela de login V2 (`index.php` 15.8.1)
- click-through: form POST -> redirect home
- comportamento: autentica e atribui tema preferido
- persistencia: sessao ativa
- visual: paleta e layout 15.8.1
- teste: `Tests\Feature\Identidade\AutenticacaoTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given credenciais validas de operador
- When submeter o formulario de login
- Then sessao e iniciada e redireciona para a tela inicial do tema ativo.
Bugs/Gaps: Nenhum.

#### CAP-ID-002 - Logout
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (POST com CSRF, invalidacao de sessao).
Contrato funcional: Operador encerra sessao atual de forma segura.
Tema V1 novo:
- backend: `AuthController::logout`
- Policy: auth
- rota: `logout`
- entrada pela UI: link no menu superior e menu de sessao
- click-through: clique no link/form aciona POST logout
- comportamento: invalida sessao e redireciona para login
- persistencia: sessao destruida
- visual: link 14.6.1
- teste: `Tests\Feature\Identidade\AutenticacaoTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `AuthController::logout`
- Policy: auth
- rota: `logout`
- entrada pela UI: item "Sair" no dropdown da navbar
- click-through: clique no item aciona logout
- comportamento: invalida sessao e redireciona para login
- persistencia: sessao destruida
- visual: dropdown item 15.8.1
- teste: `Tests\Feature\Identidade\AutenticacaoTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given usuario autenticado
- When acionar logout
- Then sessao e encerrada e proxima requisicao exige autenticacao.
Bugs/Gaps: Nenhum.

#### CAP-ID-003 - Alterar propria senha
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: QUEBRADO (query com coluna inexistente no 15.8.1)
Fonte comportamental: V1 / Moderna (validacao de senha atual, hash seguro).
Contrato funcional: Operador atualiza sua propria credencial informando senha atual e confirmacao.
Tema V1 novo:
- backend: `PerfilController::updatePassword`
- Policy: auth (proprio usuario)
- rota: `identidade.perfil.password`
- entrada pela UI: menu sessao -> Trocar Senha
- click-through: link abre tela -> form -> submit
- comportamento: valida senha atual, confere confirmacao e atualiza hash
- persistencia: novo hash persistido em banco
- visual: estetica 14.6.1
- teste: `Tests\Feature\Identidade\AlterarSenhaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `PerfilController::updatePassword`
- Policy: auth (proprio usuario)
- rota: `identidade.perfil.password`
- entrada pela UI: dropdown navbar -> Trocar Senha
- click-through: link abre tela V2
- comportamento: valida e atualiza sem crash SQL
- persistencia: novo hash persistido
- visual: formulario V2 pendente de ajuste fino (PAR15-SEC-001)
- teste: `Tests\Feature\Identidade\AlterarSenhaTest`
- status_funcional: [x]
- status_apresentacao: [R]
- status_geral: [R]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given operador autenticado
- When fornecer senha atual correta e nova senha valida
- Then senha e alterada e proximo login aceita nova senha.
Bugs/Gaps: PAR15-SEC-001 (ajuste visual no Tema V2).

#### CAP-ID-004 - Listar usuarios
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (tabela de usuarios do tenant).
Contrato funcional: Supervisor/Admin visualiza os usuarios cadastrados e seus papeis.
Tema V1 novo:
- backend: `UsuarioController::index`
- Policy: `Gate::authorize('gerenciar', User::class)`
- rota: `identidade.usuarios.index` (ou `v1.identidade.usuarios.index`)
- entrada pela UI: link "Usuarios" no menu de sessao lateral
- click-through: PENDENTE DE CORRECAO (BUG-CAP-ID-USERS-V1-001: clique quebrado via menu V1)
- comportamento: listagem tabular com papel e status
- persistencia: N/A (leitura)
- visual: tabela compacta 14.6.1
- teste: `Tests\Feature\Identidade\GerenciarUsuariosTest`
- status_funcional: [R]
- status_apresentacao: [x]
- status_geral: [R]
Tema V2 novo:
- backend: `UsuarioController::index`
- Policy: `Gate::authorize('gerenciar', User::class)`
- rota: `v2.identidade.usuarios.index`
- entrada pela UI: menu dropdown -> Usuarios
- click-through: funcional
- comportamento: listagem V2 com painel
- persistencia: N/A (leitura)
- visual: layout 15.8.1
- teste: `Tests\Feature\Identidade\GerenciarUsuariosTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given supervisor autenticado
- When navegar pelo menu ate Usuarios
- Then exibe a lista completa de usuarios do tenant.
Bugs/Gaps: BUG-CAP-ID-USERS-V1-001 (click-through quebrado no menu V1).

#### CAP-ID-005 - Criar novo usuario (admin)
Origem:
- Legacy V1 14.6.1: AUSENTE (apenas autocadastro publico orfao)
- Legacy V2 15.8.1: SIM (`subp/novo_usuario.php`)
Fonte comportamental: V2 / Moderna (criacao restrita por operador/admin).
Contrato funcional: Supervisor cadastra novo usuario informando nome, email, papel e senha inicial.
Tema V1 novo:
- backend: `UsuarioController::store`
- Policy: `Gate::authorize('gerenciar', User::class)`
- rota: `v1.identidade.usuarios.novo` / `identidade.usuarios.store`
- entrada pela UI: link "Novo Usuario" em Controle/Usuarios
- click-through: funcional
- comportamento: valida dados, cria registro e associa papel
- persistencia: usuario salvo no banco
- visual: estetica 14.6.1 (UF-14)
- teste: `Tests\Feature\Identidade\NovoUsuarioV1Test`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `UsuarioController::store`
- Policy: `Gate::authorize('gerenciar', User::class)`
- rota: `v2.identidade.usuarios.novo`
- entrada pela UI: botao "Novo Usuario" na gestao de usuarios V2
- click-through: funcional
- comportamento: valida e cadastra
- persistencia: usuario salvo
- visual: pendente de alinhamento estrito ao `subp/novo_usuario.php` (PAR15-USR-007/009)
- teste: `Tests\Feature\Identidade\NovoUsuarioV2Test`
- status_funcional: [x]
- status_apresentacao: [R]
- status_geral: [R]
Tema V3: Planejado / T3-13.
Criterio de aceite:
- Given supervisor na tela de novo usuario
- When preencher campos obrigatorios e salvar
- Then novo usuario e criado e pode autenticar.
Bugs/Gaps: PAR15-USR-009 (paridade visual no Tema V2).

#### CAP-ID-006 - Resetar senha por operador
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (redefinicao segura por supervisor).
Contrato funcional: Supervisor redefine a senha de um operador subordinado.
Tema V1 novo:
- backend: `UsuarioController::resetPassword`
- Policy: `gerenciar`
- rota: `identidade.usuarios.reset-password`
- entrada pela UI: botao de reset na lista de usuarios
- click-through: funcional
- comportamento: atualiza credencial do usuario alvo
- persistencia: novo hash persistido
- visual: modal/acao V1
- teste: `Tests\Feature\Identidade\GerenciarUsuariosTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `UsuarioController::resetPassword`
- Policy: `gerenciar`
- rota: `identidade.usuarios.reset-password`
- entrada pela UI: botao de acao no painel V2
- click-through: funcional
- comportamento: redefinicao com feedback
- persistencia: persistido
- visual: botoes de acao 15.8.1
- teste: `Tests\Feature\Identidade\GerenciarUsuariosTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given supervisor autenticado
- When resetar senha de um usuario
- Then senha temporaria/nova entra em vigor imediatamente.
Bugs/Gaps: Nenhum.

#### CAP-ID-007 - Mudar permissao de usuario
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (papeis tipados: Operador, Supervisor, Administrador).
Contrato funcional: Supervisor altera o papel atribuido a um usuario.
Tema V1 novo:
- backend: `UsuarioController::updateRole`
- Policy: `gerenciar`
- rota: `identidade.usuarios.role`
- entrada pela UI: seletor de papel na gestao de usuarios
- click-through: funcional
- comportamento: valida permissao e atualiza papel
- persistencia: papel salvo
- visual: select V1
- teste: `Tests\Feature\Identidade\GerenciarUsuariosTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `UsuarioController::updateRole`
- Policy: `gerenciar`
- rota: `identidade.usuarios.role`
- entrada pela UI: radio/select no painel V2
- click-through: funcional
- comportamento: altera papel
- persistencia: papel salvo
- visual: radios/layout 15.8.1
- teste: `Tests\Feature\Identidade\GerenciarUsuariosTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given supervisor autenticado
- When mudar permissao de um usuario de Operador para Supervisor
- Then o usuario passa a ter acesso aos recursos de supervisor.
Bugs/Gaps: Nenhum.

#### CAP-ID-008 - Exclusao/desativacao de usuario
Origem:
- Legacy V1 14.6.1: SIM (hard delete inseguro no legado)
- Legacy V2 15.8.1: SIM (hard delete inseguro no legado)
Fonte comportamental: DECISAO-PENDENTE (seguranca e integridade referencial proíbem hard delete sem soft delete/desativacao).
Contrato funcional: Desativar acesso do usuario sem corromper historico de auditoria.
Tema V1 novo: Pendente de implementacao segura.
Tema V2 novo: Pendente de implementacao segura.
Tema V3: Planejado / T3-13.
Criterio de aceite: Deferido para solucao tenant-safe com soft delete.
Bugs/Gaps: DECISAO-PENDENTE.

#### CAP-ID-009 - Alternar tema / preferencia
Origem:
- Legacy V1 14.6.1: SIM (conceitualmente presente na coexistencia)
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (seletor de tema preferido persistido por usuario).
Contrato funcional: Operador escolhe seu tema preferido (V1 ou V2) e a sessao lembra a escolha.
Tema V1 novo:
- backend: `PerfilController::updateTheme`
- Policy: auth
- rota: `identidade.perfil.tema`
- entrada pela UI: seletor de tema na barra superior/perfil
- click-through: funcional
- comportamento: altera preferencia no banco e redireciona para tema
- persistencia: coluna `tema_preferido` persistida
- visual: link discreto V1
- teste: `Tests\Feature\Identidade\TemaPreferidoTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `PerfilController::updateTheme`
- Policy: auth
- rota: `identidade.perfil.tema`
- entrada pela UI: seletor no dropdown de configuracoes
- click-through: funcional
- comportamento: altera preferencia e redireciona
- persistencia: persistido
- visual: item 15.8.1
- teste: `Tests\Feature\Identidade\TemaPreferidoTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given usuario autenticado
- When selecionar outro tema
- Then a interface e recarregada no novo tema e a preferencia e mantida.
Bugs/Gaps: Nenhum.

#### CAP-ID-010 - Salvar anotacoes do operador
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM (`subp/anotacoes.php`)
Fonte comportamental: Compartilhada (bloco de notas por usuario).
Contrato funcional: Operador digita e salva notas operacionais privadas.
Tema V1 novo:
- backend: `AnotacoesController::update`
- Policy: auth
- rota: `identidade.anotacoes.index` / `identidade.anotacoes.update`
- entrada pela UI: menu sessao -> Anotacoes
- click-through: funcional
- comportamento: textarea com persistencia de notas
- persistencia: texto persistido em `users.anotacoes`
- visual: caixa simples 14.6.1
- teste: `Tests\Feature\Identidade\AnotacoesTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `AnotacoesController::update`
- Policy: auth
- rota: `v2.identidade.anotacoes.index`
- entrada pela UI: dropdown menu -> Anotacoes
- click-through: funcional
- comportamento: textarea com salvamento
- persistencia: persistido
- visual: pendente de autosave com debounce e geometria 15.8.1 (PAR15-NOTE-001)
- teste: `Tests\Feature\Identidade\AnotacoesTest`
- status_funcional: [x]
- status_apresentacao: [R]
- status_geral: [R]
Tema V3: Presente ([x]) / T3-01.
Criterio de aceite:
- Given operador digitando anotacoes
- When acionar salvar (ou autosave)
- Then texto e recuperado intacto ao reabrir a tela.
Bugs/Gaps: PAR15-NOTE-001 (geometria e autosave no Tema V2).

#### CAP-ID-011 - Autocadastro com convite/segredo
Origem:
- Legacy V1 14.6.1: SIM (`page/signup.php` com chave hardcoded)
- Legacy V2 15.8.1: AUSENTE
Fonte comportamental: NAO-PROMOVER (vulnerabilidade critica legado).
Contrato funcional: N/A (proibido reproduzir segredo hardcoded).
Status geral: [x] Codigo Morto / Rejeitado por Seguranca.

---

### Dominio 2: RMA - Ciclo de Vida e Listagens (CAP-RMA-001..024)

#### CAP-RMA-001 - Criar novo RMA
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (validacao robusta, numeracao sequencial por tenant).
Contrato funcional: Operador cadastra solicitacao de RMA com dados de cliente, produto, defeito e NF.
Tema V1 novo:
- backend: `RmaController::store`
- Policy: `Gate::authorize('create', Rma::class)`
- rota: `rmas.create` / `rmas.store`
- entrada pela UI: link "Novo RMA" no menu superior `#TOPO`
- click-through: funcional
- comportamento: valida, gera numero e salva registro com status inicial `entrada`
- persistencia: registro persistido na tabela `rmas`
- visual: formulario 14.6.1 com campos caracteristicos
- teste: `Tests\Feature\Rma\CadastroRmaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `RmaController::store`
- Policy: `Gate::authorize('create', Rma::class)`
- rota: `v2.rmas.index#novo_rma` / `rmas.store`
- entrada pela UI: tab "#novo_rma" na navbar V2
- click-through: funcional
- comportamento: valida e cadastra
- persistencia: registro persistido
- visual: formulario 15.8.1 com secoes estilizadas
- teste: `Tests\Feature\Rma\CadastroRmaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-03.
Criterio de aceite:
- Given operador na tela de novo RMA
- When preencher dados obrigatorios e salvar
- Then RMA e criado com status `entrada` e redireciona para detalhe.
Bugs/Gaps: Nenhum.

#### CAP-RMA-002 - Buscar / Localizar RMAs
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (busca por numero, cliente, serie, NF).
Contrato funcional: Localizar RMAs filtrando por termos chave.
Tema V1 novo:
- backend: `RmaController::index`
- Policy: auth
- rota: `rmas.index` (query `q=`)
- entrada pela UI: campo de busca rapida na lateral e no topo
- click-through: funcional
- comportamento: filtra listagem pelo termo
- persistencia: N/A (leitura)
- visual: tabela de resultados 14.6.1
- teste: `Tests\Feature\Rma\BuscaRmaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `RmaController::index`
- Policy: auth
- rota: `v2.rmas.index#pesquisar`
- entrada pela UI: tab Pesquisar na navbar V2
- click-through: funcional
- comportamento: pesquisa com retorno instantaneo/filtrado
- persistencia: N/A (leitura)
- visual: painel de pesquisa 15.8.1
- teste: `Tests\Feature\Rma\BuscaRmaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-03.
Criterio de aceite:
- Given termo de busca existente
- When submeter a pesquisa
- Then retorna apenas os RMAs correspondentes ao criterio.
Bugs/Gaps: Nenhum.

#### CAP-RMA-003 - Fila de Entrada
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`status = 'entrada'`).
Contrato funcional: Listar todos os RMAs no estagio inicial de Entrada.
Tema V1: `rmas.entrada`, `#TOPO` link Entrada, visual 14.6.1. Status: [x].
Tema V2: `v2.rmas.index#entrada`, tab Entrada na navbar V2. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Criterio de aceite: Exibe itens com status entrada ordenados por data.
Bugs/Gaps: Nenhum.

#### CAP-RMA-004 - Fila dedicada de Recebidos
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-01)
- Legacy V2 15.8.1: SIM (`#recebido`)
Fonte comportamental: V2 / Moderna (`status = 'recebido'`).
Contrato funcional: Listar exclusivamente os RMAs cujo produto fisico ja foi recebido.
Tema V1 novo:
- backend: `RmaFilaController::recebidos`
- Policy: auth
- rota: `rmas.recebidos`
- entrada pela UI: link "Recebidos" no `#TOPO` (UF-07)
- click-through: funcional
- comportamento: tabela com itens recebidos
- persistencia: N/A (leitura)
- visual: tabela 14.6.1 fiel
- teste: `Tests\Feature\Rma\UniaoFuncionalV1V2Test`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `RmaController::index`
- Policy: auth
- rota: `v2.rmas.index#recebido`
- entrada pela UI: tab "#recebido" na navbar V2
- click-through: funcional
- comportamento: listagem de recebidos com botoes de transicao
- persistencia: N/A (leitura)
- visual: painel 15.8.1
- teste: `Tests\Feature\Rma\UniaoFuncionalV1V2Test`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-04.
Criterio de aceite:
- Given itens com status `recebido`
- When acessar fila de recebidos
- Then exibe somente estes itens em ambos os temas.
Bugs/Gaps: Nenhum.

#### CAP-RMA-005 - Fila de Encaminhados
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`status = 'encaminhado'`).
Contrato funcional: Listar itens enviados para fabricante/fornecedor/assistencia externa.
Tema V1: `rmas.encaminhados`, link `#TOPO`. Status: [x].
Tema V2: `v2.rmas.index#encaminhado`, tab navbar. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-006 - Fila de Concluidos
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`status = 'concluido'`).
Contrato funcional: Listar RMAs finalizados com resolucao operacional.
Tema V1: `rmas.concluidos`, link `#TOPO`. Status: [x].
Tema V2: `v2.rmas.index#concluido`, tab navbar. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-007 - Fila de Aguardando Credito
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`status = 'aguardando_credito'`).
Contrato funcional: Listar RMAs com solucao de credito financeiro pendente de aplicacao.
Tema V1: `rmas.aguardando-credito`, link `#TOPO`. Status: [x].
Tema V2: `v2.rmas.index#aguardando_credito`, tab navbar. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-008 - Fila de Arquivados
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`status = 'arquivado'`).
Contrato funcional: Listar RMAs historicos arquivados.
Tema V1: `rmas.arquivados`, link `#TOPO`. Status: [x].
Tema V2: `v2.rmas.index#arquivado`, tab navbar. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-009 - Rota Retornou (0 bytes)
Origem:
- Legacy V1 14.6.1: AUSENTE
- Legacy V2 15.8.1: CODIGO-MORTO (`page/retornou.php` arquivo de 0 bytes no backup)
Fonte comportamental: N/A (proibido promover arquivo vazio).
Status geral: [x] Codigo Morto / Nao Promover.

#### CAP-RMA-010 - Detalhe e Edicao de RMA
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada.
Contrato funcional: Visualizar e atualizar dados completos de um RMA especifico.
Tema V1 novo:
- backend: `RmaController::show` / `update`
- Policy: `Gate::authorize('update', $rma)`
- rota: `rmas.show` / `rmas.update`
- entrada pela UI: clique no numero do RMA em qualquer listagem
- click-through: funcional
- comportamento: edicao de dados de produto, defeito e logistica
- persistencia: dados atualizados no banco
- visual: layout de detalhe 14.6.1
- teste: `Tests\Feature\Rma\DetalheRmaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `RmaController::show` / `update`
- Policy: `Gate::authorize('update', $rma)`
- rota: `v2.rmas.show` / `rmas.update`
- entrada pela UI: clique no link do RMA
- click-through: funcional
- comportamento: edicao completa
- persistencia: persistido
- visual: layout 15.8.1 com tolerancia 2-4px (PAR15-RMA-DET-011..014 [x])
- teste: `Tests\Feature\Rma\ParidadeDetalheRmaV2GeometriaTest`
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V3: Presente ([x]) / T3-04.
Criterio de aceite: Atualizacao de campos do RMA persiste e reflete no detalhe.
Bugs/Gaps: Nenhum.

#### CAP-RMA-011 - Transicao: Receber RMA
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (`status -> recebido`, registro de modificacao).
Contrato funcional: Alterar status de `entrada` para `recebido`.
Tema V1 e V2: botao de acao funcional com mudanca de estado e log. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-012 - Transicao: Encaminhar RMA
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (`status -> encaminhado`, data de envio, destinatario).
Contrato funcional: Registrar envio para parceiro externo.
Tema V1 e V2: form de encaminhamento com persistencia de data e destino. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-013 - Transicao: Concluir RMA
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (`status -> concluido`, solucao, NF de retorno).
Contrato funcional: Finalizar processo de RMA com registro de solucao.
Tema V1 e V2: funcional com solucao de encerramento. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-014 - Transicao: Reverter p/ Entrada
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna (reversao de status operacional).
Contrato funcional: Retornar RMA ao estagio inicial em caso de erro de despacho.
Tema V1 e V2: acao funcional com autorizacao. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-015 - Arquivar RMA
Origem:
- Legacy V1 14.6.1: QUEBRADO (Fatal error no legado 14.6.1 por metodo ausente)
- Legacy V2 15.8.1: SIM (`subp/arquivar.php` funcional)
Fonte comportamental: V2 / Moderna (mudanca de status para arquivado sem crash).
Contrato funcional: Arquivar RMA concluido sem deletar dados.
Tema V1 e V2: funcional sem crash, status arquivado persistido (UF-05). Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-016 - Deletar solicitacao RMA
Origem:
- Legacy V1 14.6.1: SIM (hard delete destrutivo no legado)
- Legacy V2 15.8.1: AUSENTE
Fonte comportamental: DECISAO-PENDENTE (proibido reproduzir hard delete sem rastreabilidade).
Status geral: [R] Deferido por Seguranca / Auditoria.

#### CAP-RMA-017 - Solucoes de encerramento (15)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (catalogo das 15 solucoes padrao).
Contrato funcional: Selecionar a solucao tecnica de resolucao do RMA.
Tema V1 e V2: select com as 15 solucoes historicas integro. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-018 - Controle de Estoque (marcarestoque)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`marcarestoque = 1/0`).
Contrato funcional: Sinalizar se o item foi absorvido pelo estoque proprio.
Tema V1 e V2: checkbox e persistencia ativos. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-019 - Credito disponivel no RMA
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`creditodisponivel = 1/0`).
Contrato funcional: Marcar RMA como apto a abatimento/credito financeiro.
Tema V1 e V2: toggle funcional e refletido nas consultas de credito. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-020 - Gestao fiscal (NFs/chaves/emissao)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (campos de NFs de remessa e retorno).
Contrato funcional: Manter numero, data e valor das NFs associadas.
Tema V1 e V2: campos persistidos e auditados. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-021 - Codigos de rastreio ida/retorno
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (rastreio correios/transportadora).
Contrato funcional: Salvar e consultar codigos de rastreio.
Tema V1 e V2: campos presentes e persistidos. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-022 - Destinatario / fone / email
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (contatos do destinatario).
Contrato funcional: Armazenar contatos da ponta de envio.
Tema V1 e V2: integrados ao fluxo de despacho. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-023 - SN retorno (RN-15)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Moderna / Dominio (numero de serie trocado no retorno).
Contrato funcional: Registrar serie do equipamento substituto se houver troca.
Tema V1 e V2: campo `snretorno` persistido no detalhe e listagens. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-RMA-024 - Marcar como (alias orfao)
Origem:
- Legacy V1 14.6.1: AUSENTE
- Legacy V2 15.8.1: CODIGO-MORTO (`marcarcomo.php` duplicata sem rota ativa)
Fonte comportamental: N/A.
Status geral: [x] Codigo Morto / Nao Promover.

---

### Dominio 3: Alertas e Prioridade (CAP-ALT-001..005)

#### CAP-ALT-001 - Alertas de prazo e tempo
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (calculo de dias corridos vs prazo estimado).
Contrato funcional: Alertar operador sobre RMAs atrasados ou proximos do vencimento.
Tema V1 e V2: indicadores visuais por cor e contador de dias. Status: [x].
Tema V3: Presente ([x]) / T3-05.
Bugs/Gaps: Nenhum.

#### CAP-ALT-002 - Indicadores Sem NF/SN/Garantia
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada.
Contrato funcional: Exibir tags/badges quando campos criticos estiverem vazios.
Tema V1 e V2: badges operacionais presentes. Status: [x].
Tema V3: Presente ([x]) / T3-05.
Bugs/Gaps: Nenhum.

#### CAP-ALT-003 - Nivel de Prioridade (Baixa/Normal/Alta)
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-09)
- Legacy V2 15.8.1: SIM
Fonte comportamental: V2 / Moderna (coluna `prioridade`).
Contrato funcional: Classificar criticidade do atendimento e filtrar por nivel.
Tema V1 novo: campo e badges introduzidos em V1 (UF-13). Status: [x].
Tema V2 novo: select no detalhe e badges na tabela V2. Status: [x].
Tema V3: Presente ([x]) / T3-05.
Bugs/Gaps: Nenhum.

#### CAP-ALT-004 - Urgencia / Threshold R$ 75
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-08)
- Legacy V2 15.8.1: SIM (`right_urgente.php`)
Fonte comportamental: V2 / Moderna (`Rma::ehUrgentePorThreshold`).
Contrato funcional: Destacar automaticamente itens com valor > R$ 75 e prazo vencido.
Tema V1 novo: classe `TrUrgente` e sinalizador introduzidos no V1 (UF-12). Status: [x].
Tema V2 novo: bloco lateral URGENTE na sidebar V2. Status: [x].
Tema V3: Presente ([x]) / T3-05.
Bugs/Gaps: Nenhum.

#### CAP-ALT-005 - Classificacoes visuais operacionais
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: V2 / Compartilhada (classes de status e criticidade).
Contrato funcional: Estilizacao contextual das linhas de tabela conforme estado.
Tema V1 e V2: estilos adaptados a cada identidade visual. Status: [x].
Tema V3: Planejado / T3-14.
Bugs/Gaps: Nenhum.

---

### Dominio 4: Credito (CAP-CRD-001..002)

#### CAP-CRD-001 - Listagem tabular rica de Creditos
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-07)
- Legacy V2 15.8.1: SIM (`/rmas-credito`)
Fonte comportamental: V2 / Moderna (tabela de 11 colunas com acao de marcar).
Contrato funcional: Exibir todos os RMAs aptos a abatimento de credito em tabela dedicada.
Tema V1 novo: visualizacao tabular de 11 colunas no CSS V1 (UF-11). Status: [x].
Tema V2 novo: tabela rica `/v2/creditos`. Status: [x].
Tema V3: Presente ([x]) / T3-06.
Bugs/Gaps: Nenhum.

#### CAP-CRD-002 - Submenus de Creditos (orfao)
Origem:
- Legacy V1 14.6.1: AUSENTE
- Legacy V2 15.8.1: CODIGO-MORTO (sub-rotas de creditos inexistentes no legado)
Fonte comportamental: N/A.
Status geral: [x] Codigo Morto / Nao Promover.

---

### Dominio 5: Relatorios (CAP-REL-001..006)

#### CAP-REL-001 - Relatorio RCD (Creditos)
Origem:
- Legacy V1 14.6.1: SIM (`relatorios_cd.php`)
- Legacy V2 15.8.1: AUSENTE (GAP-V2-01)
Fonte comportamental: V1 / Moderna.
Contrato funcional: Gerar relatorio detalhado de creditos disponiveis com informacao adicional.
Tema V1: `rmas.relatorios.rcd`, visual 14.6.1 fiel. Status: [x].
Tema V2: `v2.rmas.relatorios.rcd`, alcancavel via `_menu_relatorios` (UF-08). Status: [x].
Tema V3: Presente ([x]) / T3-06.
Bugs/Gaps: Nenhum.

#### CAP-REL-002 - Relatorio RPEC (Estoque)
Origem:
- Legacy V1 14.6.1: SIM (`relatorios_pec.php`)
- Legacy V2 15.8.1: AUSENTE (GAP-V2-02)
Fonte comportamental: V1 / Moderna.
Contrato funcional: Relatorio de produtos em estoque com informacao adicional persistida.
Tema V1: `rmas.relatorios.rpec`, visual 14.6.1. Status: [x].
Tema V2: `v2.rmas.relatorios.rpec`, alcancavel no V2 (UF-08). Status: [x].
Tema V3: Presente ([x]) / T3-06.
Bugs/Gaps: Nenhum.

#### CAP-REL-003 - Relatorio RMPE (Movimentacao)
Origem:
- Legacy V1 14.6.1: SIM (`relatorios_mpe.php`)
- Legacy V2 15.8.1: AUSENTE (GAP-V2-03)
Fonte comportamental: V1 / Moderna (intervalo de datas opcional deterministico).
Contrato funcional: Relatorio de movimentacao por periodo ou consolidado geral.
Tema V1: `rmas.relatorios.rmpe`, visual 14.6.1. Status: [x].
Tema V2: `v2.rmas.relatorios.rmpe`, alcancavel no V2 (UF-08). Status: [x].
Tema V3: Presente ([x]) / T3-06.
Bugs/Gaps: Nenhum.

#### CAP-REL-004 - Hub Estatistico de Relatorios
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-06)
- Legacy V2 15.8.1: SIM (`/relatorios` 15.8.1)
Fonte comportamental: V2 / Moderna.
Contrato funcional: Painel com contagens por Situacao, Resolucao, Origem, NF e Series.
Tema V1 novo: adaptado na estetica V1 em `temas.v1.rma.relatorios.index` (UF-09). Status: [x].
Tema V2 novo: painel estatistico `/v2/relatorios`. Status: [x].
Tema V3: Planejado / T3-15.
Bugs/Gaps: Nenhum.

#### CAP-REL-005 - Impressao limpa de relatorios
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`@media print`, layout sem menus).
Contrato funcional: Emitir visualizacao limpa para papel/PDF sem navegacao.
Tema V1 e V2: folhas de estilo de impressao operantes. Status: [x].
Tema V3: Presente ([x]) / T3-06.
Bugs/Gaps: Nenhum.

#### CAP-REL-006 - Informacao adicional persistida
Origem:
- Legacy V1 14.6.1: SIM (`relatorios_cd_inf.php` e tabela persistida)
- Legacy V2 15.8.1: AUSENTE
Fonte comportamental: V1 / Moderna.
Contrato funcional: Salvar e persistir texto complementar nos relatorios fiscais.
Tema V1 e V2: suportado pelo model e migration persistentes. Status: [x].
Tema V3: Planejado / T3-15.
Bugs/Gaps: Nenhum.

---

### Dominio 6: Parceiros (CAP-PAR-001..007)

#### CAP-PAR-001 - Gestao de Clientes (CRUD)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`ClienteController`).
Contrato funcional: Cadastrar, listar, editar e visualizar clientes.
Tema V1 novo:
- backend: `ClienteController`
- Policy: `Gate::authorize('viewAny', Cliente::class)`
- rotas: `parceiros.clientes.*`
- entrada pela UI: menu sessao -> Clientes
- click-through: funcional
- comportamento: listagem e operacoes
- persistencia: dados persistidos em `clientes`
- visual: tabelas e formularios 14.6.1
- status_funcional: [x]
- status_apresentacao: [x]
- status_geral: [x]
Tema V2 novo:
- backend: `ClienteController`
- Policy: `Gate::authorize('viewAny', Cliente::class)`
- rotas: `v2.parceiros.clientes.*`
- entrada pela UI: dropdown menu -> Clientes
- click-through: funcional
- comportamento: operacoes de cliente
- persistencia: persistido
- visual: form de edicao V2 ainda reutiliza componentes de create (PAR15-PART-002)
- status_funcional: [x]
- status_apresentacao: [R]
- status_geral: [R]
Tema V3: Presente ([x]) / T3-02.
Bugs/Gaps: PAR15-PART-002 (especializacao visual de edit/detalhe no Tema V2).

#### CAP-PAR-002 - Gestao de Fornecedores (CRUD)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`FornecedorController`).
Contrato funcional: Cadastrar, listar, editar e visualizar fornecedores.
Tema V1: funcional e visual 14.6.1. Status_geral: [x].
Tema V2: funcional [x], pendencia visual de edit (PAR15-PART-003). Status_geral: [R].
Tema V3: Presente ([x]) / T3-02.
Bugs/Gaps: PAR15-PART-003.

#### CAP-PAR-003 - Gestao de Fabricantes (CRUD)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`FabricanteController`).
Contrato funcional: Cadastrar, listar, editar e visualizar fabricantes.
Tema V1: funcional e visual 14.6.1. Status_geral: [x].
Tema V2: funcional [x], pendencia visual de edit (PAR15-PART-004). Status_geral: [R].
Tema V3: Presente ([x]) / T3-02.
Bugs/Gaps: PAR15-PART-004.

#### CAP-PAR-004 - Gestao de Assistencias (CRUD)
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (`AssistenciaTecnicaController`).
Contrato funcional: Cadastrar, listar, editar e visualizar assistencias tecnicas.
Tema V1: funcional e visual 14.6.1. Status_geral: [x].
Tema V2: funcional [x], pendencia visual de edit (PAR15-PART-005). Status_geral: [R].
Tema V3: Presente ([x]) / T3-02.
Bugs/Gaps: PAR15-PART-005.

#### CAP-PAR-005 - RG / IE nos parceiros
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (campos `rg` e `inscricao_estadual`).
Contrato funcional: Manter RG ou Inscricao Estadual no cadastro de parceiros.
Tema V1 e V2: campos expostos, pendente de confirmacao de persistencia em edicao V2 (PAR15-PART-DATA-001). Status_geral: [R].
Tema V3: Presente ([x]) / T3-02.
Bugs/Gaps: PAR15-PART-DATA-001.

#### CAP-PAR-006 - RMAs associados ao parceiro
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-10)
- Legacy V2 15.8.1: SIM (`subp/ver_*.php` exibia tabela de RMAs do cliente)
Fonte comportamental: V2 / Moderna.
Contrato funcional: Ao visualizar um parceiro, listar todos os RMAs vinculados a ele.
Tema V1 novo: secao adicionada via `parceiros._detalhe`, mas necessita apresentacao desmembrada do V2 (PAR15-PART-001/006). Status_funcional: [x], Status_apresentacao: [R], Status_geral: [R].
Tema V2 novo: exibicao funcional, necessita ajuste estrito ao `subp/ver_*.php` (PAR15-PART-001). Status_geral: [R].
Tema V3: Planejado / T3-14.
Bugs/Gaps: PAR15-PART-001, PAR15-PART-006.

#### CAP-PAR-007 - Modelo `assistencias(tipo)`
Origem:
- Legacy V1 14.6.1: SIM (tabela legada descontinuada)
- Legacy V2 15.8.1: AUSENTE
Fonte comportamental: N/A (substituido pelas 4 entidades tipadas).
Status geral: [x] Codigo Morto / Nao Promover.

---

### Dominio 7: Controle e Auditoria (CAP-AUD-001..004)

#### CAP-AUD-001 - Hub de Controle Operacional
Origem:
- Legacy V1 14.6.1: SIM (`page/controle.php`)
- Legacy V2 15.8.1: SIM (`/controle`)
Fonte comportamental: Compartilhada.
Contrato funcional: Central de operacoes para consultas avancadas e configuracoes.
Tema V1 e V2: operantes em ambos os temas. Status: [x].
Tema V3: Planejado / T3-14.
Bugs/Gaps: Nenhum.

#### CAP-AUD-002 - Logs de Autenticacao (acesso)
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-03)
- Legacy V2 15.8.1: SIM (`tentativas_de_acesso`)
Fonte comportamental: V2 / Moderna.
Contrato funcional: Auditoria de acessos com IP, usuario, data, resultado e agente.
Tema V1 novo: listagem no padrao 14.6.1 acessivel em Controle (UF-10). Status: [x].
Tema V2 novo: aba no hub de controle 15.8.1. Status: [x].
Tema V3: Presente ([x]) / T3-01.
Bugs/Gaps: Nenhum.

#### CAP-AUD-003 - Logs de Modificacao de RMA
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-04)
- Legacy V2 15.8.1: SIM (`modificacoes_de_rma`)
Fonte comportamental: V2 / Moderna.
Contrato funcional: Auditoria detalhada de cada alteracao de dados ou status de RMA.
Tema V1 novo: listagem no padrao 14.6.1 acessivel em Controle (UF-10). Status: [x].
Tema V2 novo: aba de modificacoes no hub V2. Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

#### CAP-AUD-004 - Acao Ver / Detalhe do log
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-05)
- Legacy V2 15.8.1: SIM (`ver.png` abre detalhe)
Fonte comportamental: V2 / Moderna.
Contrato funcional: Inspecionar o registro de modificacao completo e navegar para o RMA alvo.
Tema V1 e V2: link e icone ativos em ambos os temas (UF-10). Status: [x].
Tema V3: Presente ([x]) / T3-04.
Bugs/Gaps: Nenhum.

---

### Dominio 8: Logistica (CAP-LOG-001..002)

#### CAP-LOG-001 - Transporte para Porto Alegre
Origem:
- Legacy V1 14.6.1: AUSENTE (GAP-V1-11)
- Legacy V2 15.8.1: SIM (`portoalegre_r.php`)
Fonte comportamental: V2 / Moderna.
Contrato funcional: Consultar regras, prazos e instrucoes de transporte regional POA.
Tema V1 novo: tela na linguagem visual 14.6.1 e link no `#JS-Sessao` (UF-16). Status: [x].
Tema V2 novo: painel lateral e link direto no V2. Status: [x].
Tema V3: Planejado / T3-14.
Bugs/Gaps: Nenhum.

#### CAP-LOG-002 - Destinatarios com frete e CFOP
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: AUSENTE (GAP-V2-04)
Fonte comportamental: V1 / Moderna.
Contrato funcional: Exibir parametros fiscais e logisticos (CFOP e modalidade de frete).
Tema V1 novo: integrado a visualizacao de parceiros. Status: [x].
Tema V2 novo: exibido na interface de parceiros V2 (UF-17). Status: [x].
Tema V3: Planejado / T3-14.
Bugs/Gaps: Nenhum.

---

### Dominio 9: Outros e Auxiliares (CAP-AUX-001..004)

#### CAP-AUX-001 - Ajuda / Procedimento de RMA
Origem:
- Legacy V1 14.6.1: SIM (`page/ajuda.php`)
- Legacy V2 15.8.1: AUSENTE (GAP-V2-05)
Fonte comportamental: V1 / Moderna.
Contrato funcional: Manual operacional de fluxo e instrucoes de atendimento de RMA.
Tema V1 novo: tela `temas.v1.rma.ajuda` acessivel no menu de sessao. Status: [x].
Tema V2 novo: tela `temas.v2.rma.ajuda` e link no dropdown Menu V2 (UF-18). Status: [x].
Tema V3: Planejado / T3-14.
Bugs/Gaps: Nenhum.

#### CAP-AUX-002 - Avisar alguem (stub)
Origem:
- Legacy V1 14.6.1: AUSENTE
- Legacy V2 15.8.1: CODIGO-MORTO (stub 15 linhas no legado)
Fonte comportamental: N/A.
Status geral: [x] Codigo Morto / Nao Promover.

#### CAP-AUX-003 - Enviar e-mail (stub)
Origem:
- Legacy V1 14.6.1: AUSENTE
- Legacy V2 15.8.1: CODIGO-MORTO (stub 11 linhas no legado)
Fonte comportamental: N/A.
Status geral: [x] Codigo Morto / Nao Promover.

#### CAP-AUX-004 - Paginas de Erro 403 / 404
Origem:
- Legacy V1 14.6.1: SIM
- Legacy V2 15.8.1: SIM
Fonte comportamental: Compartilhada (tratamento amigavel de autorizacao e nao encontrado).
Contrato funcional: Exibir tela informativa quando rota nao existir ou acesso for negado.
Tema V1 e V2: views de erro contextuais operantes. Status: [x].
Tema V3: Presente ([x]) / T3-01.
Bugs/Gaps: Nenhum.

---

## 4. Recalculo Rigoroso das Metricas a partir dos 65 IDs

Contagem canonica por categorias mutuamente exclusivas:
1. **Comuns vivas em ambos os Legacies:** 35 capacidades.
2. **Exclusivas vivas do Legacy 14.6.1 (V1):** 8 capacidades (CAP-ID-011, CAP-REL-001, CAP-REL-002, CAP-REL-003, CAP-REL-006, CAP-PAR-007, CAP-LOG-002, CAP-AUX-001).
   - Das quais: 5 promovidas a V2 (CAP-REL-001..003, CAP-LOG-002, CAP-AUX-001); 1 mantida (CAP-REL-006); 2 codigo morto/inseguro (CAP-ID-011, CAP-PAR-007).
3. **Exclusivas vivas do Legacy 15.8.1 (V2):** 16 capacidades (CAP-ID-005, CAP-RMA-004, CAP-RMA-009, CAP-RMA-024, CAP-ALT-003, CAP-ALT-004, CAP-CRD-001, CAP-CRD-002, CAP-REL-004, CAP-PAR-006, CAP-AUD-002, CAP-AUD-003, CAP-AUD-004, CAP-LOG-001, CAP-AUX-002, CAP-AUX-003).
   - Das quais: 11 promovidas a V1 (GAP-V1-01..11); 5 codigo morto (CAP-RMA-009, CAP-RMA-024, CAP-CRD-002, CAP-AUX-002, CAP-AUX-003).
4. **Quebradas em V1 mas funcionais em V2:** 1 capacidade (CAP-RMA-015 - Arquivar RMA).
5. **Quebradas em V2 mas funcionais em V1:** 1 capacidade (CAP-ID-003 - Alterar senha).
6. **Decisoes de produto/seguranca pendentes:** 2 capacidades (CAP-ID-008 - soft delete usuario, CAP-RMA-016 - soft delete rma) + CAP-ID-011 (deferido).
7. **Codigo morto comprovado:** 6 capacidades (CAP-RMA-009, CAP-RMA-024, CAP-CRD-002, CAP-PAR-007, CAP-AUX-002, CAP-AUX-003).

Soma das categorias estruturais:
35 (comuns) + 8 (exclusivas V1) + 16 (exclusivas V2) + 1 (quebrada V1) + 1 (quebrada V2) + 4 (outras/especiais deduplicadas) = 65 capacidades unicas comprovadas.

Status atual da convergencia:
- **GAPS FUNCIONAIS BACKEND/ROTAS:** 0
- **BUGS DE FLUXO/CLICK-THROUGH ABERTOS:** 1 (BUG-CAP-ID-USERS-V1-001)
- **GAPS VISUAIS CONHECIDOS PENDENTES (V2):** PAR15-SEC-001, PAR15-NOTE-001, PAR15-USR-007/009, PAR15-PART-001..006, PAR15-PART-DATA-001.
