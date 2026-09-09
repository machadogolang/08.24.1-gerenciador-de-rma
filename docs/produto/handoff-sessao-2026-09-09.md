# Handoff de sessão — CellSystem RMA V3

Data de encerramento: 2026-09-09 (fechamento parcial da frente de ações; sessão em
andamento até UI-03+). Substitui o conteúdo anterior deste arquivo como ponto de
partida. Fonte de status sempre atualizada: `PLANO-ATAQUE.md`; leitura de estado e
lacunas: `docs/produto/diagnostico-estado-pos-gate-2026-09-09.md`.

## Estado geral

- Frente ativa **FRONT-003**: UI-01/UI-02 (crédito com shell) e **UI-02B/C/D**
  (auditoria + contrato visual de ações/botões) concluídas nesta sessão.
- Trilha A formalmente encerrada; EVO-SAAS-001 permanece ABERTO (S9.8/S10.4/S11.4/
  S13/S14) e não é fechado pela correção visual.
- Suíte PHPUnit corrente: **477 testes / 1242 assertions, 100% verde**.
- Vite build verde (SCSS V1/V2 alterado); Playwright dirigido verde (2 testes).
- PUSH NÃO REALIZADO por agente; `origin/main` avançou por sincronização externa
  até `694732d` durante a sessão.

## Incidente operacional

- Sandbox continua com `bwrap: loopback: Failed RTM_NEWADDR` para comandos não
  aprovados; `apply_patch` não edita arquivos existentes por depender do mesmo
  wrapper. Solução comprovada: scripts temporários em `/tmp` + execução escalada
  para edição pontual, conforme `docs/operacao/incidentes/2026-09-09-sandbox-bwrap-loopback.md`.

## A Frente de Ações — baseline e resultado

**SHA inicial da frente:** `78e4719` (HEAD = origin/main, working tree limpa).

### Causa raiz
- V1 Parceiros: `Novo`/`Editar` como `<a>` crus; `Remover` `<button>` cru sem
  `cursor:pointer` (SCSS `button` não definia cursor).
- V2 Parceiros: `Novo` `btn formSubmit`, `Editar` cru, `Remover` `btn btn-xs` —
  três linguagens na mesma tabela.
- Problema transversal: `rma._acoes_de_transicao` crua, crédito cru, relatórios
  standalone com `Filtrar` cru, formulários sem papel distinto.

### Legado consultado (fonte histórica real)
- V1 14.6.1: lista de parceiros com linha inteira navegável, detalhe com botão
  desabilitado `USE A 15.8.1 P/ SALVAR`; paleta de botão `#662D37`, hover
  `#9B3949`/`gold`, sem cursor em `button`.
- V2 15.8.1: breadcrumb `Novo`, coluna ACAO com ícones Apagar/Ver, form com
  `btn btn-default formButtonCadastrar2`; paleta `#224A5D`, hover `#185A78`,
  `#333`/`#E1DEAC`, vermelho `#904141`.
- Evidência e decisões completas:
  `docs/produto/2026-09-09-investigacao-contrato-visual-acoes-botoes.md`.

### Contrato definido
- HTML carrega semântica por papel: `.acao`, `.acao--primaria`, `.acao--secundaria`,
  `.acao--perigo`, `.acao--operacional`, `.acao--compacta`; cada tema estiliza no
  próprio SCSS (V1 não passa a parecer V2).
- Estados: hover, `:focus-visible` com outline, `button:not(:disabled)` com
  `cursor:pointer`, `button:disabled` sem pointer + opacidade.
- Semântica preservada: GET continua `<a href>`; mutações continuam `<button>` em
  `<form>` com CSRF/Gates; nenhum JS de navegação novo.

### Telas auditadas e tratadas
- Parceiros V1/V2 (clientes, fabricantes, fornecedores, assistências): Novo/Editar/
  Remover com contrato.
- RMA: busca V1 (Ver/Editar compactas), detalhe V1/V2 (Editar primário; V2 mantém
  ícone "Ver" no índice por fidelidade do legado).
- Ciclo de vida: `rma._acoes_de_transicao` com papéis semânticos.
- Crédito: `Marcar crédito disponível` primário.
- Formulários/identidade: Voltar V1 secundário, Alternar tema V1 secundário, Abrir
  novo RMA V2 primário, foco global por tema.
- Resíduo classificado: views genéricas órfãs (UI-07), relatórios standalone
  RPEC/RMPE (UI-03), componentes históricos de geometria própria (buttonSave/
  formSubmit/formButtonEnviarPanel/JSformLocalizarButton) — todos agora com
  cursor/foco globais.

### Commits da frente (ordem)
1. `694732d` — `#DOC-RMA - Audita contrato visual das acoes no V1 e V2`.
2. `624e548` — `#FRONT-RMA - Padroniza acoes de parceiros nos temas V1 e V2`.
3. `1c1a1e6` — `#FRONT-RMA - Padroniza acoes das listagens e detalhe de RMA`.
4. `5ce8654` — `#FRONT-RMA - Padroniza botoes do ciclo de vida do RMA`.
5. `42a4235` — `#FRONT-RMA - Padroniza acoes de formularios credito e identidade`.
6. `7a17120` — `#QA-RMA - Cobre contrato visual de acoes no browser`.
7. `da43f14` — `#DOC-RMA - Atualiza plano e tasks apos contrato visual de acoes`.

### Testes
- PHPUnit completo: **477 testes / 1242 assertions** (regressão ampla, incluindo
  novos testes de contrato em `tests/Feature/Temas/ContratoVisual*`).
- Playwright dirigido: `tests/Browser/ContratoVisualAcoes.spec.ts` — 2 testes:
  Parceiros V1/V2 (`computedStyle.cursor === 'pointer'`, hover, TAB/focus-visible,
  semântica `<a>`/POST+DELETE) e Detalhe RMA + Crédito V1/V2.
- Build Vite verde; `git diff --check` limpo antes de cada commit.

## Próximos itens exatos

1. **UI-03** — RCD/RPEC/RMPE em shell V1/V2 com impressão limpa; filtros já nascem
   com `.acao`.
2. **UI-04** — alertas/históricos/logística em shell.
3. **UI-05** — Controle V1 (alinhamento do bloco representante).
4. **UI-06** — auditoria de identificador RMA.
5. **UI-07/FRONT-006** — remover views genéricas órfãs com zero consumidor
   (inventário em `2026-09-09-investigacao-contrato-visual-acoes-botoes.md`).
6. **UI-08** — regressão browser ampla (já incorpora UI-02D).
7. Depois: EVO-SAAS-001 S10.4, S11.4, S13.2, S14.

## Comando de retomada
```
docker compose up -d mysql laravel.test
docker compose exec -T laravel.test php artisan test
docker compose exec -T laravel.test npx playwright test tests/Browser/ContratoVisualAcoes.spec.ts --project=chromium
```
