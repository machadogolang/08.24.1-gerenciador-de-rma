# Handoff de sessão — CellSystem RMA V3

Data de encerramento: 2026-08-25.

Este documento permite iniciar uma nova sessão sem depender do histórico da conversa.
A fonte granular continua sendo `docs/produto/checklist-master-v3.md`; este handoff
registra o contexto operacional, o que foi efetivamente concluído e a ordem de retomada.

## Estado exato no encerramento

- Repositório V3: `/home/legionario/github/08.24.1-gerenciador-de-rma`.
- Branch: `main`.
- HEAD anterior ao commit deste handoff: `dfc389a`.
- Working tree antes da documentação de encerramento: limpa.
- Repositório Legacy: `/home/legionario/github/08.24.4-legacy-gerenciador-de-rma`.
- Legacy branch/HEAD: `main`, `f83542c`; nenhuma alteração realizada nesta sessão.
- Nenhum push, PR, merge ou alteração de visibilidade remota foi realizado.
- V2/Legacy executável: `http://localhost:8094/`; TEMA V1 em `/14.6.1/`.
- V3: `http://localhost:8095/`.
- Credenciais e modos locais estão documentados em
  `docs/produto/ambientes-locais-v2-v3.md`; não reproduzir credenciais históricas.

## O que foi concluído nesta sessão

### 1. Consolidação do estado e do roadmap

Foi conferido o trabalho que já existia na árvore, concluída a comparação final e
organizado o estado operacional do projeto. Commits relevantes:

- `c5bdfb4 #ARQ-RMA - Consolida comparacao final V3 e legado`;
- `c490e9d #DEV-QA - Prepara base local deterministica do V3`;
- `0b3f72d #ARQ-RMA - Documenta ambientes locais V2 e V3`;
- `50a460d #ROADMAP-RMA - Consolida checklist mestre operacional`.

O checklist mestre passou a refletir F1–F9 concluídas e F10 separada nos eixos
funcional, visual, dados e fechamento. A Trilha B foi ordenada, mas continua bloqueada
até `F10-GATE-07`.

### 2. Auditoria paralela da Trilha B

A auditoria identificou 17 itens `EVO-*`. Nenhum foi implementado. Achados incorporados
ao checklist:

- `EVO-DOM-001` misturava uma FK já entregue com a futura unificação polimórfica de
  parceiros; o residual precisa ser separado antes de especificar migração.
- `EVO-CONF-001` já possui decisões posteriores no OpenSpec; o backlog não deve voltar
  a tratar persistência append-only, autorização e tela única como dúvidas abertas.
- `EVO-ARQ-001` precisa acrescentar testes de binding aninhado/IDOR e, após SaaS,
  isolamento cross-tenant.
- `EVO-PERF-001` exige medição de queries/`EXPLAIN`; não pode ser concluído por
  inferência.
- `EVO-AUD-001` depende de decisão do usuário.
- Toda implementação da Trilha B permanece bloqueada pelo gate da Trilha A.

### 3. Paridade visual dirigida do TEMA V1

Foi executada comparação real entre Legacy 14.6.1 e V3 em dez superfícies desktop de
1440 px: login, home, usuários, clientes, fabricantes, fornecedores, assistências,
listagem, novo e detalhe de RMA.

Causas comprovadas da aparência incorreta:

1. Vite carregava `v1.js` → `v1.scss` → `_compartilhado.scss`, mas o port CSS era
   incompleto.
2. O Blade usava breadcrumb vertical incompatível com o HTML histórico autenticado.
3. Faltavam seletores de cabeçalho, menu, painel de sessão, tabelas e formulários.
4. O caminho da Fira Mono gerava 404 em `/build/fonts/...`.
5. Os TTFs que existiam no repositório eram inválidos/corrompidos mesmo após corrigir o
   caminho.
6. O logo histórico necessário não pertencia ao build V3.

Correções realizadas:

- cabeçalho histórico com logo e três itens `.menu-up` flutuados;
- painel de sessão de 982 px, colunas `.JS-SessaoLEFT` 838 px e
  `.JS-SessaoRIGHT` 144 px;
- tabela e formulários de usuários com primitivas históricas, preservando forms,
  policies e rotas Laravel;
- wrappers/estilos de busca e novo RMA;
- alternância funcional do painel pelo JS do tema;
- Fira Mono Regular/Bold oficiais e locais sob a OFL existente;
- logo `public/images/tema-v1/ferramenta-logo.png` local ao V3;
- teste `tests/Browser/ParidadeVisualTemaV1.spec.ts` para geometria, fontes, assets e
  capturas comparáveis;
- matriz completa em `docs/produto/paridade-visual-tema-v1.md`.

Decisões conscientes:

- largura histórica fixa de 984 px foi preservada; não se tentou responsividade;
- Open Sans, Roboto, Fira Sans e Cantarell não foram baixadas porque não determinam o
  resultado computado das superfícies reconstruídas;
- `http://scripting.com.br` permanece apenas como link histórico de navegação do
  rodapé, não como dependência de asset;
- login V3 permanece gateway compartilhado já aprovado na F8;
- diferenças de ações Laravel e massa de dados não são redesenho visual;
- nenhuma folha histórica foi copiada integralmente.

Commits:

- `24ba8be #F10-VIS - Corrige paridade visual do Tema V1`;
- `dfc389a #F10-VIS - Registra fonte residual do legado`.

### 4. Retomada do eixo funcional da F10

Foi criado `docs/qa/roteiro-paridade-funcional.md` e cada um dos 48 `LEG-RMA-*` recebeu
um método de prova:

- 44 `PARIDADE`;
- 2 `NÃO RECONSTRUIR` (`LEG-RMA-016` e `LEG-RMA-034`);
- 1 `RETOMAR IDEIA` (`LEG-RMA-035`);
- 1 `PENDENTE` por decisão externa (`LEG-RMA-002`).

Todos os arquivos de teste citados no mapa foram verificados como existentes. O OpenSpec
da F10 foi corrigido de um arquivo Playwright `.php` inexistente para a suíte real
`tests/Browser/*.spec.ts`.

Commit:

- `d1c12f8 #F10-FUNC - Mapeia evidencias dos 48 fluxos`.

## Evidências e testes finais

- `npm run build`: aprovado; CSS/JS/fontes gerados pelo Vite.
- PHPUnit focado no TEMA V1/usuários: 11 testes, 28 assertions, sem falhas.
- PHPUnit completo: 310 testes, 608 assertions, sem falhas.
- `ParidadeVisualTemaV1.spec.ts`: 3 testes aprovados.
- `ComparacaoVisualTemaV1Test.spec.ts`: 3 testes aprovados em 390/768/1440.
- Total do recorte Playwright V1 executado: 6 testes aprovados.
- 20 screenshots locais foram gerados em
  `docs/produto/screenshots-paridade-v1/`, um par Legacy/V3 para cada superfície.
- Os PNGs são ignorados pelo Git porque o banco histórico pode exibir dados reais; o
  teste os regenera. Não remover essa proteção nem versionar capturas sem sanitização.
- Nenhum recurso essencial de `/build/` ou `/images/tema-v1/` ficou com 4xx/5xx/falha.
- `fc-query` e FontFaceSet validaram Fira Mono Regular/Bold.

### Topologia importante dos testes

Não executar duas suítes PHPUnit em paralelo contra a mesma base: isso provocou
interferência em uma tentativa intermediária. A execução final, isolada, ficou verde.

Os testes Playwright V1 precisam ser separados por topologia:

- a comparação Legacy×V3 deve rodar no host, que alcança `localhost:8094` e `:8095`;
- o teste antigo `ComparacaoVisualTemaV1Test.spec.ts` deve rodar no container Laravel,
  pois seu `beforeAll` chama `php artisan tinker` e o PHP do host não possui o driver
  MySQL do projeto.

Comandos validados:

```bash
PLAYWRIGHT_BASE_URL=http://localhost:8095  LEGACY_BASE_URL=http://localhost:8094/14.6.1/  npx playwright test tests/Browser/ParidadeVisualTemaV1.spec.ts    --project=chromium --reporter=line

docker compose exec -T laravel.test npx playwright test    tests/Browser/ComparacaoVisualTemaV1Test.spec.ts    --project=chromium --reporter=line

docker compose exec -T laravel.test php artisan test --compact
```

## O que está pendente

### Prioridade 1 — concluir o lote funcional iniciado

1. `F10-FUN-07`: executar e registrar M-01…M-06 do roteiro funcional.
2. Usar apenas leitura em M-03 e nas conferências possíveis de M-05.
3. Para M-02, M-04 e qualquer parte mutável de M-06, preparar registro/base
   descartável; não alterar o banco histórico por conveniência.
4. `F10-FUN-08`: reconciliar a matriz depois dos smokes.
5. Obter decisão ou adiamento explícito de `LEG-RMA-002`: convite seguro versus criação
   somente por administrador.

### Prioridade 2 — completar o eixo visual

O checkpoint V1/1440 está concluído, mas o gate visual geral não está:

1. fechar `F10-VIS-01/02`, fixando a matriz e reaproveitando evidências válidas da F8;
2. completar V1 em 390/768 conforme o comportamento fixo histórico, sem redesenhar;
3. executar TEMA V2 em 390/768/1440;
4. cobrir login/home, novo/detalhe/edição, busca/listagem, parceiros,
   alertas/crédito/relatórios/histórico;
5. classificar toda divergência como correção, rasterização ou evolução futura.

### Prioridade 3 — eixo de dados

Executar `F10-DAD-01…09` em ordem:

1. origem histórica somente leitura e contagens confirmadas;
2. alvo V3 descartável e rollback;
3. rede V3→Legacy controlada;
4. `rma:migrar-legado --dry-run` com relatório;
5. revisão de datas inválidas e eventual `status='retornou'` somente se aparecerem;
6. importação real no alvo descartável;
7. reconciliação das nove tabelas;
8. segunda execução para provar idempotência.

Nunca usar a base V3 corrente como alvo por inferência.

### Prioridade 4 — decisões, higiene e fechamento

- C-01: decisão de `LEG-RMA-002`.
- C-02: RN-12 no TEMA V1.
- C-03/C-04: Lightbox2 e skin AdminLTE, uso real ou resíduo.
- C-08: visibilidade do repositório, somente com autorização.
- C-09: prioridade futura de auditoria before/after.
- D-05: atualizar contagens em resumos correntes.
- D-06: auditar/remover `ExampleTest` se for placeholder sem valor.
- F-04/F-05/F-06: segurança, performance e links internos.
- `F10-GATE-01…07`: regressão final, confirmação dos três eixos, decisões, relatório e
  declaração explícita do gate da Trilha A.

## O que não deve ser feito na próxima sessão

- Não refazer a arqueologia consolidada nem assumir ordem sequencial entre 14.6.1 e
  15.8.1; eles coexistem no container 15.9.7.
- Não modernizar o TEMA V1 nem transformar os 984 px fixos em layout responsivo.
- Não restaurar os TTFs antigos: eram inválidos.
- Não remover o logo local para apontar ao Legacy.
- Não versionar screenshots históricos sem sanitização.
- Não executar testes concorrentes sobre a mesma base.
- Não implementar `EVO-*` antes de `F10-GATE-07`.
- Não alterar fontes, repositórios ou backups históricos.
- Não fazer push, PR ou merge sem autorização explícita.

## Ordem de abertura da próxima sessão

1. Executar `git status --short`, `git branch --show-current` e `git log --oneline -8`.
2. Confirmar `:8094` e `:8095` sem resetar dados automaticamente.
3. Ler, nesta ordem:
   - este handoff;
   - `PLANO-ATAQUE.md`;
   - `docs/produto/checklist-master-v3.md`;
   - `docs/qa/roteiro-paridade-funcional.md`;
   - `docs/produto/paridade-visual-tema-v1.md`.
4. Começar em `F10-FUN-07`, primeiro pelos passos somente leitura.
5. Registrar imediatamente esperado, observado, data, ambiente e evidência no roteiro.
6. Criar commit local pequeno ao concluir o checkpoint; não fazer push.

## Critério para declarar continuidade bem-sucedida

A próxima sessão não deve declarar F10 funcional concluída apenas porque os 48 itens têm
um teste citado. É necessário executar os seis smokes, tratar `LEG-RMA-002` e reconciliar
a matriz. O projeto só sai da Trilha A quando os eixos funcional, visual e de dados,
as decisões residuais, a suíte final e o relatório estiverem todos fechados.
