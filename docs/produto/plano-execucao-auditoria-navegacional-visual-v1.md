# Plano de execução — auditoria navegacional e visual integral do Tema V1

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

## Lote NAV-00 — infraestrutura repetível

- [x] NAV-00-01 — estender/criar gerador versionado para percorrer a matriz abaixo (`scripts/qa/auditoria-navegacional-v1.mjs`).
- [x] NAV-00-02 — gerar manifesto JSON por alvo: origem/destino, status, URL final,
      título, link ativo, recursos falhos, dimensões e fontes-chave (`docs/produto/evidencias-auditoria-v1/manifesto-navegacional-v1.json`).
- [x] NAV-00-03 — gerar screenshot raw ignorado + sanitizado versionado por tela (`docs/produto/screenshots-auditoria-v1/`).
- [x] NAV-00-04 — teste de regressão que falha para rota quebrada, destino incorreto,
      recurso 4xx ou ausência do elemento principal (`tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts`).

## Lote NAV-01 — menu superior

- [x] NAV-01-01 — logo → Página Inicial.
- [x] NAV-01-02 — Pag. Inicial.
- [x] NAV-01-03 — Novo: abrir painel inline, preencher/validar em QA e comparar.
- [x] NAV-01-04 — Localizar: abrir painel, testar cada opção suportada e comparar.
- [x] NAV-01-05 — Entrada.
- [x] NAV-01-06 — Encaminhado.
- [x] NAV-01-07 — Aguardando crédito.
- [x] NAV-01-08 — Concluído.
- [x] NAV-01-09 — botão Menu: abrir/fechar painel e estado ativo.
- [x] NAV-01-10 — logout, somente em sessão QA isolada.

## Lote NAV-02 — menu de sessão

- [ ] NAV-02-01 — Fornecedores: índice, Novo, Editar e Voltar.
- [ ] NAV-02-02 — Fabricantes: índice, Novo, Editar e Voltar.
- [ ] NAV-02-03 — Assistências: índice, Novo, Editar e Voltar.
- [ ] NAV-02-04 — Clientes: índice, Novo, Editar e Voltar.
- [ ] NAV-02-05 — Controle: abas/painéis, links de RMA e formulários QA.
- [ ] NAV-02-06 — Créditos: listagem, detalhes e ações QA disponíveis.
- [ ] NAV-02-07 — Relatórios: filtros, geração e retorno vazio/preenchido.
- [ ] NAV-02-08 — Usuários: listagem, alteração de papel e reset só em usuário QA.

## Lote NAV-03 — Página Inicial e Centro de Avisos

- [ ] NAV-03-01 — 16 contadores laterais: destino e filtro resultante de cada link.
- [x] NAV-03-02 — protocolo aberto não encaminhado: Mostrar/Ocultar, tabela e Ver
      (`CMP-NAV-V1-001`/`CMP-V1-2-011`).
- [x] NAV-03-03A — prioridade alta sem encaminhar (`CMP-NAV-V1-002`).
- [x] NAV-03-03B — sem número de série (`CMP-NAV-V1-003`).
- [x] NAV-03-03C — sem nota fiscal (`CMP-NAV-V1-004`).
- [x] NAV-03-03D — prazo do destinatário estourado (`CMP-NAV-V1-005`).
- [ ] NAV-03-03E — recebidos há mais de 30 dias sem encaminhar.
- [ ] NAV-03-03F — garantia do fornecedor expirada.
- [ ] NAV-03-03G — garantia expirando em até 30 dias.
- [ ] NAV-03-03H — não vai dar garantia.
- [ ] NAV-03-03I — NF de retorno pendente.
- [ ] NAV-03-04 — resultado de Localizar: Ver e Editar.
- [ ] NAV-03-05 — autosave de Anotações: sucesso, persistência e erro controlado.

## Lote NAV-04 — ciclo de vida e links internos

- [ ] NAV-04-01 — detalhe do RMA e Editar.
- [ ] NAV-04-02 — editar/salvar/voltar em RMA QA.
- [ ] NAV-04-03 — receber RMA QA.
- [ ] NAV-04-04 — encaminhar RMA QA.
- [ ] NAV-04-05 — concluir RMA QA.
- [ ] NAV-04-06 — reverter RMA QA para Entrada.
- [ ] NAV-04-07 — arquivar e restaurar RMA QA, com prova antes/depois.
- [ ] NAV-04-08 — histórico de modificações e histórico de acessos.
- [ ] NAV-04-09 — perfil: tema, senha e anotação em usuário QA.
- [ ] NAV-04-10 — link externo do rodapé: apenas validar href/segurança; não depende
      de disponibilidade de terceiro para aprovar o produto.

## Gate NAV-05

- [ ] NAV-05-01 — revisar visualmente todos os pares versionados.
- [ ] NAV-05-02 — executar suíte PHP, build e Browser completos.
- [ ] NAV-05-03 — tabela final de cobertura sem célula “não verificada”.
- [ ] NAV-05-04 — atualizar `PLANO-ATAQUE.md`, checklist runtime, parecer e handoff.
- [ ] NAV-05-05 — commit local pequeno e coerente; nunca push sem autorização.

## Diário de comparação

### CMP-NAV-V1-001 — Página Inicial, protocolo aberto não encaminhado

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

### CMP-NAV-V1-002 — Página Inicial, prioridade alta sem encaminhar

- Estado: **APROVADO somente para este grupo**, em 2026-08-26.
- Funcional: Mostrar/Ocultar e Ver exercitados no V3; Browser permanente verde.
- Visual: par aberto. Legacy sem dados comprova o empty-state; V3 com fixture comprova
  tabela ENTRADA e reutiliza a estrutura que CMP-NAV-V1-001 mediu contra uma tabela
  Legacy não vazia. Não houve mutação do banco histórico para criar uma comparação.
- Evidência/deltas: `CMP-V1-2-012`, screenshots de prioridade e
  `evidencias-v1-fase2/cp15-medidas.json`.
- Próximo item: CP12-05C/CMP-NAV-V1-003, grupo “NECESSARIO IDENTIFICAR O S/N”.

### CMP-NAV-V1-003 — Página Inicial, necessário identificar o S/N

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

### CMP-NAV-V1-004 — Página Inicial, sem NF de compra e NF de venda

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

### CMP-NAV-V1-005 — Página Inicial, prazo do destinatário estourado

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

### CMP-NAV-V1-006 — Página Inicial, recebidos há mais de 30 dias sem encaminhar

- Estado: **APROVADO**, em 2026-08-28 (commit `05ca2cbf`).
- Funcional: Mostrar/Ocultar e tabela de 10 colunas exercitados; ordenação histórica provada.
- Visual: par sanitizado gerado e inspecionado em 1440×1000; medidas em `cp15-medidas.json`.
- Próximo item: Lote NAV-00 / NAV-01 (Auditoria navegacional e visual do menu superior).

### CMP-NAV-V1-007 — Auditoria navegacional e visual do menu superior (Lotes NAV-00 e NAV-01)

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
- Próximo item exato: Lote NAV-02 — menu de sessão (NAV-02-01 a NAV-02-08: Fornecedores,
  Fabricantes, Assistências, Clientes, Controle, Créditos, Relatórios e Usuários).

