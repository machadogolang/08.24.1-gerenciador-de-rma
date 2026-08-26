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

- [ ] NAV-00-01 — estender/criar gerador versionado para percorrer a matriz abaixo.
- [ ] NAV-00-02 — gerar manifesto JSON por alvo: origem/destino, status, URL final,
      título, link ativo, recursos falhos, dimensões e fontes-chave.
- [ ] NAV-00-03 — gerar screenshot raw ignorado + sanitizado versionado por tela.
- [ ] NAV-00-04 — teste de regressão que falha para rota quebrada, destino incorreto,
      recurso 4xx ou ausência do elemento principal.

## Lote NAV-01 — menu superior

- [ ] NAV-01-01 — logo → Página Inicial.
- [ ] NAV-01-02 — Pag. Inicial.
- [ ] NAV-01-03 — Novo: abrir painel inline, preencher/validar em QA e comparar.
- [ ] NAV-01-04 — Localizar: abrir painel, testar cada opção suportada e comparar.
- [ ] NAV-01-05 — Entrada.
- [ ] NAV-01-06 — Encaminhado.
- [ ] NAV-01-07 — Aguardando crédito.
- [ ] NAV-01-08 — Concluído.
- [ ] NAV-01-09 — botão Menu: abrir/fechar painel e estado ativo.
- [ ] NAV-01-10 — logout, somente em sessão QA isolada.

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
- [ ] NAV-03-03 — cada um dos outros 9 grupos: Mostrar/Ocultar, tabela e Ver.
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
