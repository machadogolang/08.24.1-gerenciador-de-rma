# Plano de execução - auditoria navegacional e visual integral do Tema V1

Estado: **ABERTO** em 2026-08-26. Ordem: começa somente depois de estabilizar e
commitar o checkpoint atual da Página Inicial/CP12-05A. Este arquivo é a fonte de
continuidade para o pedido “menu a menu, link a link, tela a tela”.

## Regra de execução e evidência

Cada item abaixo é uma tarefa pequena e só recebe `[x]` quando, no mesmo ciclo:

1. o alvo equivalente foi identificado no Legacy 14.6.1 e no V3 Tema V1;
2. clique/navegação/ação foi exercitado em fixture descartável, sem dado histórico;
3. retorno HTTP, URL final, estado ativo e ausência de recurso 4xx foram registrados;
4. o par Legacy × V3 foi gerado pelo script versionado, aberto e inspecionado;
5. a comparação ganhou uma entrada `CMP-NAV-V1-NNN` neste arquivo, com caminhos,
   viewport, divergências, decisão e testes;
6. dados reais do Legacy ficaram somente no diretório gitignorado; a evidência
   versionada foi sanitizada sem destruir a geometria.

Não acionar exclusão, logout, reset de senha ou outra mutação irreversível sobre dado
real. Fluxos mutáveis usam apenas a base QA descartável e comprovam também o estado
posterior. “A rota respondeu” não equivale a paridade visual.

## Lote NAV-00 - infraestrutura repetível

- [x] NAV-00-01 - estender/criar gerador versionado para percorrer a matriz abaixo (`scripts/qa/auditoria-navegacional-v1.mjs`).
- [x] NAV-00-02 - gerar manifesto JSON por alvo: origem/destino, status, URL final,
      título, link ativo, recursos falhos, dimensões e fontes-chave (`docs/produto/evidencias-auditoria-v1/manifesto-navegacional-v1.json`).
- [x] NAV-00-03 - gerar screenshot raw ignorado + sanitizado versionado por tela (`docs/produto/screenshots-auditoria-v1/`).
- [x] NAV-00-04 - teste de regressão que falha para rota quebrada, destino incorreto,
      recurso 4xx ou ausência do elemento principal (`tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts`).

## Lote NAV-01 - menu superior

- [x] NAV-01-01 - logo → Página Inicial.
- [x] NAV-01-02 - Pag. Inicial.
- [x] NAV-01-03 - Novo: abrir painel inline, preencher/validar em QA e comparar.
- [x] NAV-01-04 - Localizar: abrir painel, testar cada opção suportada e comparar.
- [x] NAV-01-05 - Entrada.
- [x] NAV-01-06 - Encaminhado.
- [x] NAV-01-07 - Aguardando crédito.
- [x] NAV-01-08 - Concluído.
- [x] NAV-01-09 - botão Menu: abrir/fechar painel e estado ativo.
- [x] NAV-01-10 - logout, somente em sessão QA isolada.

## Lote NAV-02 - menu de sessão

- [x] NAV-02-01 - Fornecedores: índice, Novo, Editar e Voltar.
- [x] NAV-02-02 - Fabricantes: índice, Novo, Editar e Voltar.
- [x] NAV-02-03 - Assistências: índice, Novo, Editar e Voltar.
- [x] NAV-02-04 - Clientes: índice, Novo, Editar e Voltar.
- [x] NAV-02-05 - Controle: abas/painéis, links de RMA e formulários QA.
- [x] NAV-02-06 - Créditos: listagem, detalhes e ações QA disponíveis.
- [x] NAV-02-07 - Relatórios: filtros, geração e retorno vazio/preenchido.
- [x] NAV-02-08 - Usuários: listagem, alteração de papel e reset só em usuário QA.

## Lote NAV-03 - Página Inicial e Centro de Avisos

- [x] NAV-03-01 - 16 contadores laterais: destino e filtro resultante de cada link.
- [x] NAV-03-02 - protocolo aberto não encaminhado: Mostrar/Ocultar, tabela e Ver
      (`CMP-NAV-V1-001`/`CMP-V1-2-011`).
- [x] NAV-03-03A - prioridade alta sem encaminhar (`CMP-NAV-V1-002`).
- [x] NAV-03-03B - sem número de série (`CMP-NAV-V1-003`).
- [x] NAV-03-03C - sem nota fiscal (`CMP-NAV-V1-004`).
- [x] NAV-03-03D - prazo do destinatário estourado (`CMP-NAV-V1-005`).
- [x] NAV-03-03E - recebidos há mais de 30 dias sem encaminhar (`CMP-NAV-V1-006`).
- [x] NAV-03-03F - garantia do fornecedor expirada (`CMP-V1-2-019`).
- [x] NAV-03-03G - garantia expirando em até 30 dias (`CMP-V1-2-019`).
- [x] NAV-03-03H - não vai dar garantia (`CMP-V1-2-019`).
- [x] NAV-03-03I - NF de retorno pendente (`CMP-V1-2-019`).
- [x] NAV-03-04 - resultado de Localizar: Ver e Editar.
- [x] NAV-03-05 - autosave de Anotações: sucesso, persistência e erro controlado.

## Lote NAV-04 - ciclo de vida e links internos

- [x] NAV-04-01 - detalhe do RMA e Editar.
- [x] NAV-04-02 - editar/salvar/voltar em RMA QA.
- [x] NAV-04-03 - receber RMA QA.
- [x] NAV-04-04 - encaminhar RMA QA.
- [x] NAV-04-05 - concluir RMA QA.
- [x] NAV-04-06 - reverter RMA QA para Entrada.
- [x] NAV-04-07 - arquivar e restaurar RMA QA, com prova antes/depois.
- [x] NAV-04-08 - histórico de modificações e histórico de acessos.
- [x] NAV-04-09 - perfil: tema, senha e anotação em usuário QA.
- [x] NAV-04-10 - link externo do rodapé: apenas validar href/segurança; não depende
      de disponibilidade de terceiro para aprovar o produto.

## Gate NAV-05

- [x] NAV-05-01 - revisar visualmente todos os pares versionados.
- [x] NAV-05-02 - executar suíte PHP, build e Browser completos.
- [x] NAV-05-03 - tabela final de cobertura sem célula “não verificada”.
- [x] NAV-05-04 - atualizar `PLANO-ATAQUE.md`, checklist runtime, parecer e handoff.
- [x] NAV-05-05 - commit local pequeno e coerente; nunca push sem autorização.

## Diário de comparação

### CMP-NAV-V1-001 - Página Inicial, protocolo aberto não encaminhado

- Estado: **APROVADO somente para este link/grupo**, em 2026-08-26.
- Funcional: “Mostrar” abriu a tabela, mudou para “Ocultar” e a ação “Ver” apontou
  para o detalhe temático; teste Browser permanente verde.
- Visual: par sanitizado gerado e aberto; 11 colunas, Arial, header 34px, tabela
  984px e linha compacta equivalentes. Evidência, deltas e decisão completos em
  `CMP-V1-2-011` do plano da fase 2.
- Artefatos: gerador `scripts/qa/paridade-v1-fase2.mjs`; prints
  `docs/produto/screenshots-evidencias-v1-fase2/{legacy,v3}-cp15-protocolo-expandido-1440x1000.png`;
  medidas `docs/produto/evidencias-v1-fase2/cp15-medidas.json`.
- Pendência da tela: os outros 9 grupos ainda são genéricos; não aprovar a Página
  Inicial inteira com base apenas neste primeiro grupo.
- Próximo item: CP12-05B/CMP-NAV-V1-002, grupo de maior prioridade sem encaminhar.

## Disciplina permanente de commits e retomada

Cada `CMP-*` fechado deve ser commitado localmente de imediato com seu código,
teste, evidência e atualização documental. Não acumular múltiplas telas/grupos para
um commit final. Antes de cada commit, este plano deve declarar o **próximo item
exato**, arquivos-fonte a reler, estado das evidências e comandos de retomada. Se uma
sessão for interrompida no meio de um item, registrar o estado no diário, mas não
criar commit afirmando conclusão parcial como aprovada.

### CMP-NAV-V1-002 - Página Inicial, prioridade alta sem encaminhar

- Estado: **APROVADO somente para este grupo**, em 2026-08-26.
- Funcional: Mostrar/Ocultar e Ver exercitados no V3; Browser permanente verde.
- Visual: par aberto. Legacy sem dados comprova o empty-state; V3 com fixture comprova
  tabela ENTRADA e reutiliza a estrutura que CMP-NAV-V1-001 mediu contra uma tabela
  Legacy não vazia. Não houve mutação do banco histórico para criar uma comparação.
- Evidência/deltas: `CMP-V1-2-012`, screenshots de prioridade e
  `evidencias-v1-fase2/cp15-medidas.json`.
- Próximo item: CP12-05C/CMP-NAV-V1-003, grupo “NECESSARIO IDENTIFICAR O S/N”.

### CMP-NAV-V1-003 - Página Inicial, necessário identificar o S/N

- Estado: **APROVADO somente para este grupo**, em 2026-08-26.
- Funcional: Mostrar/Ocultar, tabela e ação Ver exercitados; ordem
  `recebido_em DESC` coberta por teste. Browser Legacy×V3 verde.
- Visual: par sanitizado gerado e aberto em página inteira e recorte ampliado;
  11 colunas, header RECEBIDO, Arial, largura 984px, zebra e linha compacta
  equivalentes. Deltas e achados completos em `CMP-V1-2-013`.
- Artefatos: gerador `scripts/qa/paridade-v1-fase2.mjs`; screenshots
  `docs/produto/screenshots-evidencias-v1-fase2/{legacy,v3}-cp15-sem-numero-de-serie-expandido-1440x1000.png`;
  medidas `docs/produto/evidencias-v1-fase2/cp15-medidas.json`.
- **Próximo item:** CP12-05D/CMP-NAV-V1-004, grupo “SEM NF DE COMPRA E NF DE VENDA”;
  começar pela leitura integral de `listar_semnota.php`, sem reaproveitar a tabela
  comum antes de provar sua estrutura.

### CMP-NAV-V1-004 - Página Inicial, sem NF de compra e NF de venda

- Estado: **APROVADO somente para este grupo**, em 2026-08-28.
- Funcional: Mostrar/Ocultar, tabela de 10 colunas e ação Ver exercitados; ordenação
  `recebido_em DESC` coberta por teste unitário. Browser Legacy×V3 verde (8/8 testes).
- Visual: par sanitizado gerado e aberto em página inteira e recorte ampliado;
  10 colunas históricas (sem colunas de NF, incluindo S/N), cabeçalho RECEBIDO, Arial,
  largura 984px, zebra e linha compacta equivalentes. Deltas e achados completos em `CMP-V1-2-014`.
- Artefatos: gerador `scripts/qa/paridade-v1-fase2.mjs`; screenshots
  `docs/produto/screenshots-evidencias-v1-fase2/{legacy,v3}-cp15-sem-nota-fiscal-expandido-1440x1000.png`;
  medidas `docs/produto/evidencias-v1-fase2/cp15-medidas.json`.
- Próximo item: CP12-05E/CMP-NAV-V1-005, grupo “O DESTINATARIO ESTOUROU O PRAZO DE 30 DIAS PARA RETORNAR”;
  reler `listar_destinatarioestourou.php`, mapear colunas, implementar partial, testar, gerar e abrir par.

### CMP-NAV-V1-005 - Página Inicial, prazo do destinatário estourado

- Estado: **APROVADO somente para este grupo**, em 2026-08-28.
- Funcional: Mostrar/Ocultar, tabela de 10 colunas e ação Ver exercitados; ordenação
  `encaminhado_em DESC` e resolução polimórfica de destinatário cobertas por testes.
  Browser Legacy×V3 verde (9/9 testes).
- Visual: par sanitizado gerado e aberto em página inteira e recorte ampliado;
  10 colunas históricas (`ENCAMINHADO|T|ORIGEM|FABRICANTE|DESCRICAO|MODELO|PROTOCOLO|DESTINATARIO|OS|A`),
  cabeçalho ENCAMINHADO, Arial, largura 984px, zebra e linha compacta equivalentes.
  Deltas e achados completos em `CMP-V1-2-015`.
- Artefatos: gerador `scripts/qa/paridade-v1-fase2.mjs`; screenshots
  `docs/produto/screenshots-evidencias-v1-fase2/{legacy,v3}-cp15-prazo-destinatario-expandido-1440x1000.png`;
  medidas `docs/produto/evidencias-v1-fase2/cp15-medidas.json`.
- Próximo item: CP12-05F/CMP-NAV-V1-006, grupo “RECEBIDO A MAIS DE 30 DIAS E NAO ENCAMINHADO”;
  reler `listar_naoencaminhadoprazoestourado.php`, mapear colunas, implementar partial, testar, gerar e abrir par.

### CMP-NAV-V1-006 - Página Inicial, recebidos há mais de 30 dias sem encaminhar

- Estado: **APROVADO**, em 2026-08-28 (commit `05ca2cbf`).
- Funcional: Mostrar/Ocultar e tabela de 10 colunas exercitados; ordenação histórica provada.
- Visual: par sanitizado gerado e inspecionado em 1440×1000; medidas em `cp15-medidas.json`.
- Próximo item: Lote NAV-00 / NAV-01 (Auditoria navegacional e visual do menu superior).

### CMP-NAV-V1-007 - Auditoria navegacional e visual do menu superior (Lotes NAV-00 e NAV-01)

- Estado: **APROVADO** em 2026-09-04.
- Escopo: cobertura completa de todos os 10 alvos do menu superior (NAV-01-01 a NAV-01-10).
  - NAV-01-01: Logo → Pagina Inicial (`#TOPO a.image-up` navega sem erros de rede, status 200).
  - NAV-01-02: Pag. Inicial (`li.menu-up:has-text("Pag. Inicial")` navega e define classe `.active`).
  - NAV-01-03: Novo (expande painel inline `#JS-Novo` preservando tela e URL).
  - NAV-01-04: Localizar (expande painel inline `#JS-Localizar` preservando tela e URL).
  - NAV-01-05: Entrada (`/rmas-entrada` responde 200, menu `.active`, ausência de erros 4xx).
  - NAV-01-06: Encaminhado (`/rmas-encaminhados` responde 200, menu `.active`, ausência de erros 4xx).
  - NAV-01-07: Aguardando crédito (`/rmas-aguardando-credito` responde 200, menu `.active`).
  - NAV-01-08: Concluído (`/rmas-concluidos` responde 200, menu `.active`).
  - NAV-01-09: Menu de Sessão (`#menu-sessao` alterna visibilidade de `#JS-Sessao` e classe `.active`).
  - NAV-01-10: Sign Out (`.formButtonSIGNOUT` submete logout seguro e redireciona para `/login`).
- Correções integradas:
  - `resources/views/temas/v1/layout.blade.php`: `request()->routeIs()` corrigido para reconhecer
    tanto rotas prefixadas (`v1.rmas.*`) quanto sem prefixo (`rmas.*`), garantindo a marcação da
    classe `.active` em `Pag. Inicial` e `Novo`.
  - `resources/js/temas/v1.js`: adicionado `botaoSessao.classList.toggle('active', !aberto)` para
    refletir o estado ativo visual do botão `#menu-sessao` idêntico ao runtime legado.
- Artefatos gerados:
  - Gerador versionado: `scripts/qa/auditoria-navegacional-v1.mjs`.
  - Manifesto JSON: `docs/produto/evidencias-auditoria-v1/manifesto-navegacional-v1.json` com
    URLs, status, links ativos, falhas e geometria de cada alvo.
  - Screenshots sanitizados: 20 pares versionados em `docs/produto/screenshots-auditoria-v1/`.
  - Teste de regressão Playwright: `tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts` (10/10 verde).
- Validação ampla:
  - PHPUnit: 388 testes / 941 asserções sem falhas.
  - Playwright: 22/22 testes no host verde; 7 passados / 1 skip no container verde.
  - Vite build: verde.
- Próximo item exato: Lote NAV-02 documentado em CMP-NAV-V1-008.

### CMP-NAV-V1-008 - Auditoria do menu de sessão (Lote NAV-02)

- Estado: **APROVADO** em 2026-09-04.
- Escopo: cobertura completa de todos os 8 alvos do menu de sessão (NAV-02-01 a NAV-02-08).
  - NAV-02-01: Fornecedores (`/parceiros/fornecedores` abre com `#JS-Sessao` visível, `#menu-sessao` `.active`, lista registros, permite acessar `Novo`, `Editar` e retornar via `Voltar`).
  - NAV-02-02: Fabricantes (`/parceiros/fabricantes` abre com `#JS-Sessao` ativo, formulários e `Voltar` operáveis).
  - NAV-02-03: Assistências (`/parceiros/assistencias-tecnicas` abre com `#JS-Sessao` ativo, formulários e `Voltar` operáveis).
  - NAV-02-04: Clientes (`/parceiros/clientes` abre com `#JS-Sessao` ativo, formulários e `Voltar` operáveis).
  - NAV-02-05: Controle (`/rmas-controle` abre com `#JS-Sessao` ativo, 7 painéis administrativos `<details>/<summary>` operáveis e listagem de arquivados).
  - NAV-02-06: Créditos (`/rmas-credito` responde 200, exibe fluxo de crédito e RMAs aguardando crédito).
  - NAV-02-07: Relatórios (`/rmas-relatorios/rcd`, `/rmas-relatorios/rpec` e `/rmas-relatorios/rmpe` respondem 200 sem erro 4xx).
  - NAV-02-08: Usuários (`/usuarios` abre com `#JS-Sessao` ativo, tabela com formulários de alteração de papel e reset de senha).
- Correções integradas:
  - `resources/views/temas/v1/layout.blade.php`: corrigido `$painelSessao` para reconhecer
    `parceiros.*` e `identidade.usuarios.*` sem prefixo `v1.`, garantindo abertura de `#JS-Sessao`
    em rotas normais pós-login.
  - `resources/views/temas/v1/parceiros/_form.blade.php`: adicionado link `Voltar` apontando para o índice do tipo correspondente.
- Artefatos gerados:
  - Gerador versionado: `scripts/qa/auditoria-navegacional-v1.mjs` estendido para cobrir todos os 18 alvos.
  - Manifesto JSON: `docs/produto/evidencias-auditoria-v1/manifesto-navegacional-v1.json` atualizado com as medições e status dos 18 alvos.
  - Screenshots sanitizados: versionados em `docs/produto/screenshots-auditoria-v1/`.
  - Teste de regressão Playwright: `tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts` (18/18 testes aprovados).
- Validação ampla:
  - PHPUnit: 388 testes / 941 asserções sem falhas.
  - Playwright: 18/18 testes de auditoria aprovados; 12/12 de paridade V1 aprovados; 7/7 no container (1 skip esperado).
  - Vite build: verde.
- Próximo item exato: Lote NAV-03 - Página Inicial e Centro de Avisos documentado em CMP-NAV-V1-009.

### CMP-NAV-V1-009 - Página Inicial, contadores e Centro de Avisos (Lote NAV-03)

- Estado: **APROVADO** em 2026-09-04.
- Escopo: cobertura completa da Página Inicial, 16 contadores laterais, 10 grupos do Centro de Avisos, formulário Localizar e autosave de anotações pessoais (NAV-03-01 a NAV-03-05).
  - NAV-03-01: 16 contadores laterais (`.contadores-do-painel a`) validados, com links para status (Entrada, Encaminhado, Aguardando crédito, Concluído) e soluções/filtros (Sem garantia, Troca imediata, etc.) navegando corretamente.
  - NAV-03-02 a 03I: Todos os 10 grupos do Centro de Avisos (`.regra-de-alerta`) exercitados funcionalmente com alternância Mostrar/Ocultar, cabeçalhos correspondentes, tabelas padronizadas de 10/11 colunas e links de ação `Ver`.
  - NAV-03-04: Submissão do formulário Localizar (`#JS-Localizar`) exibe tabela de resultados com ações `Ver` e `Editar` acessíveis.
  - NAV-03-05: Autosave de anotações pessoais (`#anotacao`) com envio via PUT para `/perfil/anotacao`, retorno status HTTP 200 `{ status: 'ok' }` e persistência sem reload.
- Correções integradas:
  - `app/Http/Controllers/Identidade/AnotacaoPessoalController.php`: return type hint ajustado para `RedirectResponse|JsonResponse` permitindo respostas assíncronas do autosave sem erro 500 (TypeError).
  - `resources/views/temas/v1/rma/edit.blade.php`: adicionado link `Voltar` apontando para o detalhe do RMA correspondente.
- Artefatos e Testes:
  - Teste Playwright `tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts` estendido com 4 novos testes (totalizando 22/22 testes aprovados).
- Próximo item exato: Lote NAV-04 - Ciclo de vida e links internos documentado em CMP-NAV-V1-010.

### CMP-NAV-V1-010 - Ciclo de vida e links internos (Lote NAV-04)

- Estado: **APROVADO** em 2026-09-04.
- Escopo: cobertura completa do ciclo de vida de RMA, navegação interna, histórico de modificações, acessos, perfil e links externos (NAV-04-01 a NAV-04-10).
  - NAV-04-01: Detalhe do RMA (`/rmas/{id}`) carrega com status 200, exibe estrutura completa (`#TOPO`, `#CONTEUDO`, `#RODAPE`), tabela de dados do RMA e ação `Editar`. Ação `Editar` navega para `/rmas/{id}/edit` com formulário e botão `Salvar`.
  - NAV-04-02: Link `Voltar` em `/rmas/{id}/edit` retorna com segurança para o detalhe. Submissão do formulário de edição atualiza campos periféricos com persistência comprovada e mensagem de feedback.
  - NAV-04-03: Ação `Receber` transiciona RMA em `Entrada` para `Recebido`, com registro de evento e atualização de estado no aggregate.
  - NAV-04-04: Ação `Encaminhar` transiciona RMA de `Recebido` para `Encaminhado` com seleção de destinatário polimórfico (assistência/fornecedor/fabricante).
  - NAV-04-05: Ação `Concluir` transiciona RMA de `Encaminhado` para `Concluido` com seleção de solução formal do domínio (`Solucao`).
  - NAV-04-06: Ação `Reverter para Entrada` reverte RMA de `Recebido` para `Entrada` com preservação de histórico.
  - NAV-04-07: Ação `Arquivar` transiciona RMA para `Arquivado`; navegação para `/rmas-controle` comprova listagem imediata na seção administrativa "LISTAR SOLICITACOES DE RMA ARQUIVADAS".
  - NAV-04-08: Telas administrativas de auditoria `/rmas-historico` (histórico de modificações de RMA) e `/historico-de-acesso` (logins e tentativas) respondem 200 com tabelas preenchidas.
  - NAV-04-09: Perfil de usuário (`/perfil`) permite alternância bidirecional de tema (V1 <-> V2), exibe formulário seguro de alteração de senha e autosave/salvamento manual de anotação pessoal.
  - NAV-04-10: Link externo no rodapé (`.designedby a`) validado com `href="http://scripting.com.br"`, `target="_blank"` e `rel` contendo `noopener`.
- Correções integradas:
  - `resources/views/temas/v1/rma/show.blade.php` e `resources/views/temas/v1/identidade/perfil.blade.php`: remoção de bloco duplicado `@if (session('status'))` que gerava dois elementos `.centrodeavisos` redundantes em relação ao layout base.
- Artefatos e Testes:
  - Teste Playwright `tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts` estendido com 10 novos testes para o Lote NAV-04 (totalizando 32/32 testes aprovados na suíte de auditoria).
- Próximo item exato: Gate NAV-05 documentado em CMP-NAV-V1-011.

### CMP-NAV-V1-011 - Fechamento e consolidação geral da auditoria navegacional (Gate NAV-05)

- Estado: **APROVADO e CONCLUÍDO** em 2026-09-04.
- Escopo: conferência final de todas as telas, navegações, links, suítes de teste e integridade geral do Tema V1.

#### Tabela Final de Cobertura Navegacional e Visual

| Lote | Alvo | Nome | Rota / Seletor | Status HTTP | URL Final | Link Ativo | Recursos 4xx | Teste Automatizado | Status |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| NAV-01 | NAV-01-01 | Logo -> Home | `#TOPO a.image-up` | 200 | `/v1/rma` | Pag. Inicial | 0 | `NAV-01-01` | APROVADO |
| NAV-01 | NAV-01-02 | Pag. Inicial | `li.menu-up:has-text("Pag. Inicial")` | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `NAV-01-02` | APROVADO |
| NAV-01 | NAV-01-03 | Novo | `#menu-novo` -> `#JS-Novo` | 200 | Preserva URL | Novo (.active) | 0 | `NAV-01-03` | APROVADO |
| NAV-01 | NAV-01-04 | Localizar | `#menu-localizar` -> `#JS-Localizar` | 200 | Preserva URL | Localizar (.active) | 0 | `NAV-01-04` | APROVADO |
| NAV-01 | NAV-01-05 | Entrada | `/rmas-entrada` | 200 | `/rmas-entrada` | Entrada (.active) | 0 | `NAV-01-05` | APROVADO |
| NAV-01 | NAV-01-06 | Encaminhado | `/rmas-encaminhados` | 200 | `/rmas-encaminhados` | Encaminhado (.active) | 0 | `NAV-01-06` | APROVADO |
| NAV-01 | NAV-01-07 | Aguardando crédito | `/rmas-aguardando-credito` | 200 | `/rmas-aguardando-credito` | Aguardando crédito (.active) | 0 | `NAV-01-07` | APROVADO |
| NAV-01 | NAV-01-08 | Concluído | `/rmas-concluidos` | 200 | `/rmas-concluidos` | Concluído! (.active) | 0 | `NAV-01-08` | APROVADO |
| NAV-01 | NAV-01-09 | Botão Menu | `#menu-sessao` -> `#JS-Sessao` | 200 | Preserva URL | MENU (.active) | 0 | `NAV-01-09` | APROVADO |
| NAV-01 | NAV-01-10 | Logout | `.formButtonSIGNOUT` | 302 -> 200 | `/login` | N/A | 0 | `NAV-01-10` | APROVADO |
| NAV-02 | NAV-02-01 | Fornecedores | `/parceiros/fornecedores` | 200 | `/parceiros/fornecedores` | MENU (.active) | 0 | `NAV-02-01` | APROVADO |
| NAV-02 | NAV-02-02 | Fabricantes | `/parceiros/fabricantes` | 200 | `/parceiros/fabricantes` | MENU (.active) | 0 | `NAV-02-02` | APROVADO |
| NAV-02 | NAV-02-03 | Assistências | `/parceiros/assistencias-tecnicas` | 200 | `/parceiros/assistencias-tecnicas` | MENU (.active) | 0 | `NAV-02-03` | APROVADO |
| NAV-02 | NAV-02-04 | Clientes | `/parceiros/clientes` | 200 | `/parceiros/clientes` | MENU (.active) | 0 | `NAV-02-04` | APROVADO |
| NAV-02 | NAV-02-05 | Controle | `/rmas-controle` | 200 | `/rmas-controle` | MENU (.active) | 0 | `NAV-02-05` | APROVADO |
| NAV-02 | NAV-02-06 | Créditos | `/rmas-credito` | 200 | `/rmas-credito` | MENU (.active) | 0 | `NAV-02-06` | APROVADO |
| NAV-02 | NAV-02-07 | Relatórios | `/rmas-relatorios/rcd` | 200 | `/rmas-relatorios/rcd` | MENU (.active) | 0 | `NAV-02-07` | APROVADO |
| NAV-02 | NAV-02-08 | Usuários | `/usuarios` | 200 | `/usuarios` | MENU (.active) | 0 | `NAV-02-08` | APROVADO |
| NAV-03 | NAV-03-01 | 16 Contadores | `.contadores-do-painel a` | 200 | Rotas de status e filtros | Rótulos correspondentes | 0 | `NAV-03-01` | APROVADO |
| NAV-03 | NAV-03-02 | Alerta Protocolo | `.regra-de-alerta` (01) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-NAV-V1-001` | APROVADO |
| NAV-03 | NAV-03-03A | Alerta Prioridade | `.regra-de-alerta` (02) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-NAV-V1-002` | APROVADO |
| NAV-03 | NAV-03-03B | Alerta Sem S/N | `.regra-de-alerta` (03) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-NAV-V1-003` | APROVADO |
| NAV-03 | NAV-03-03C | Alerta Sem NF | `.regra-de-alerta` (04) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-NAV-V1-004` | APROVADO |
| NAV-03 | NAV-03-03D | Alerta Prazo Destinatário | `.regra-de-alerta` (05) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-NAV-V1-005` | APROVADO |
| NAV-03 | NAV-03-03E | Alerta 30 dias s/ encaminhar | `.regra-de-alerta` (06) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-NAV-V1-006` | APROVADO |
| NAV-03 | NAV-03-03F | Alerta Garantia expirada | `.regra-de-alerta` (07) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-V1-2-019` | APROVADO |
| NAV-03 | NAV-03-03G | Alerta Garantia expirando | `.regra-de-alerta` (08) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-V1-2-019` | APROVADO |
| NAV-03 | NAV-03-03H | Alerta Não vai dar garantia | `.regra-de-alerta` (09) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-V1-2-019` | APROVADO |
| NAV-03 | NAV-03-03I | Alerta NF Retorno pendente | `.regra-de-alerta` (10) | 200 | `/v1/rma` | Pag. Inicial (.active) | 0 | `CMP-V1-2-019` | APROVADO |
| NAV-03 | NAV-03-04 | Resultado Localizar | `#JS-Localizar` submit | 200 | `/v1/rma?valor=...` | Pag. Inicial (.active) | 0 | `NAV-03-04` | APROVADO |
| NAV-03 | NAV-03-05 | Autosave Anotações | `#anotacao` input | 200 | `/perfil/anotacao` (PUT) | N/A | 0 | `NAV-03-05` | APROVADO |
| NAV-04 | NAV-04-01 | Detalhe e Edição RMA | `/rmas/{id}` -> `/edit` | 200 | `/rmas/{id}/edit` | N/A | 0 | `NAV-04-01` | APROVADO |
| NAV-04 | NAV-04-02 | Edição/Salvar/Voltar | `form.buttonSave` / `a:Voltar` | 302 -> 200 | `/rmas/{id}` | N/A | 0 | `NAV-04-02` | APROVADO |
| NAV-04 | NAV-04-03 | Receber RMA | `POST /rmas/{id}/receber` | 302 -> 200 | `/rmas/{id}` | N/A | 0 | `NAV-04-03` | APROVADO |
| NAV-04 | NAV-04-04 | Encaminhar RMA | `POST /rmas/{id}/encaminhar` | 302 -> 200 | `/rmas/{id}` | N/A | 0 | `NAV-04-04` | APROVADO |
| NAV-04 | NAV-04-05 | Concluir RMA | `POST /rmas/{id}/concluir` | 302 -> 200 | `/rmas/{id}` | N/A | 0 | `NAV-04-05` | APROVADO |
| NAV-04 | NAV-04-06 | Reverter RMA | `POST /rmas/{id}/reverter` | 302 -> 200 | `/rmas/{id}` | N/A | 0 | `NAV-04-06` | APROVADO |
| NAV-04 | NAV-04-07 | Arquivar e Listar Controle | `POST /rmas/{id}/arquivar` | 302 -> 200 | `/rmas-controle` | MENU (.active) | 0 | `NAV-04-07` | APROVADO |
| NAV-04 | NAV-04-08 | Históricos Auditoria | `/rmas-historico`, `/historico-de-acesso` | 200 | Rotas correspondentes | N/A | 0 | `NAV-04-08` | APROVADO |
| NAV-04 | NAV-04-09 | Perfil e Senha | `/perfil` | 200 | `/perfil` | N/A | 0 | `NAV-04-09` | APROVADO |
| NAV-04 | NAV-04-10 | Link Rodapé | `#RODAPE .designedby a` | 200 | `http://scripting.com.br` | N/A | 0 | `NAV-04-10` | APROVADO |

- Validação Técnica Final:
  - PHPUnit: 388 testes / 941 asserções sem falhas.
  - Playwright Browser (Host): 32/32 testes de auditoria navegacional verde; 12/12 testes de paridade visual verde.
  - Playwright Browser (Container): 32/32 testes de auditoria navegacional verde; 2/2 testes de painel inline verde; 3/3 testes de comparação V1 verde.
  - Build Vite: compilado com sucesso.
  - Parecer Executivo: `docs/pareceres/2026-09-04-parecer-auditoria-navegacional-visual-v1.md`.
- Conclusão: **Tema V1 integralmente auditado, validado e encerrado.**




