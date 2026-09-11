# Investigacao - Refinamento do Painel "Controle" do Tema V1

ID: `UI-V1-CONTROLE-01`  
Data: 2026-09-11  
Referencia arqueologica: `legacy-source/14.6.1/page/controle.php` e `legacy-source/pattern/14.6.1.css`  
URL observada pelo dono: `http://localhost:8095/rmas-controle`  
Status: INVESTIGADO / PLANEJADO  

---

## 1. Contexto e Objetivo

O painel "Controle" do Tema V1 foi reconstruido na Trilha A e integrado na unificacao
funcional dos temas. O dono validou manualmente a tela em runtime e constatou que ela
funciona, porem apresenta diferencas desnecessarias em relacao ao Legacy 14.6.1, esta
visualmente longa, pouco refinada e exibe textos internos de desenvolvimento.

Esta frente e um refinamento cirurgico de paridade historica e robustez interacional:
- NAO e redesign.
- NAO transformar V1 em V2/V3.
- NAO usar cards modernos.
- NAO tornar o Tema V1 responsivo incidentalmente.
- Preservar alinhamento historico a esquerda, tipografia nativa e classes canônicas do
  `14.6.1.css` (`formLabelPanel`, `formInputPanel`, `formSelectPanel`, `formButtonEnviarPanel`,
  `formTitlePanel`, `trcontrole1`, `tdcontrole1`).

---

## 2. Arqueologia Comparativa: Legacy 14.6.1 x Implementacao Atual

### 2.1 Adicionar Representante (Painel 1)
- **Legacy 14.6.1 (`page/controle.php`)**:
  Abre UM UNICO formulario compacto na mesma linha ou fluxo direto:
  `NOME: [input] [select: ASSISTENCIA / FORNECEDOR / FABRICANTE] [ADICIONAR]`
  Usa as classes:
  - `.formLabelPanel`: "NOME:"
  - `.formInputPanel`: input de texto com maxlength 25 (expandido para suporte moderno)
  - `.formSelectPanel`: select azul (`rgba(54,100,139,1)`) de 135x20px
  - `.formButtonEnviarPanel`: botao de 75x20px
- **Novo Atual (`resources/views/temas/v1/rma/controle.blade.php`)**:
  Criou TRES formularios separados empilhados:
  1) `FORNECEDOR - NOME: [input] [ADICIONAR]`
  2) `FABRICANTE - NOME: [input] [ADICIONAR]`
  3) `ASSISTENCIA - NOME: [input] [ADICIONAR]`
  Problema: triplica a altura vertical, desvia da memoria muscular historica e quebra a paridade.
- **Decisao**: Reconstruir como formulario unico fiel ao Legacy.

### 2.2 Arquivar RMA (Painel 4)
- **Legacy 14.6.1**:
  `NUMERO: [input] [ARQUIVAR]`
- **Novo Atual**:
  Utiliza inline JS fragil no atributo `onsubmit`:
  `this.action = this.action.replace('__ID__', this.numero.value); return true;`
  Problema: manipulacao dinamica de URL de mutacao via atributo HTML e propensa a falhas
  e dificil de auditar.
- **Decisao**: Criar endpoint seguro e dedicado `POST /rmas-controle/arquivar` com payload
  `numero`, CSRF, Gate/Policy, busca tenant-aware e delegacao ao mesmo caso de uso/maquina
  de estados existente no dominio.

### 2.3 Mensagens Internas de Desenvolvimento na UI (Paineis 5 e 6)
- **Legacy 14.6.1**:
  Exibia `DELETAR UMA SOLICITACAO DE RMA` e `DELETAR UM USUARIO` com formularios diretos.
- **Novo Atual**:
  Exibe textos tecnicos de documentacao:
  `Pendente - exclusao definitiva de RMA depende de decisao de produto/seguranca ainda nao tomada (ver VIS-V1-011 em docs/produto/checklist-paridade-visual-v1-runtime.md)...`
  Problema: vazamento de documentacao e IDs internos (`VIS-V1-011`, caminhos de markdown)
  em tela operacional.
- **Decisao**: Omitir documentacao interna na UI. Exibir aviso discreto compativel com o V1:
  "Operacao indisponivel nesta versao." sem acao de mutacao ativa.

### 2.4 Informacao do Procedimento de RMA (Painel 7)
- **Legacy 14.6.1**:
  Painel fechado por padrao. Quando aberto, contem o texto de instrucao operacional em
  fonte 12px ('Fira mono', 'Open Sans', 'Arial').
- **Novo Atual**:
  Apresenta o texto com quebras largas e sem limitacao confortavel de leitura, alem de nao
  integrar o atalho para a Central de Ajuda unificada.
- **Decisao**: Manter fechado por padrao, tipografia 12px com line-height confortavel no
  padrao V1 e adicionar link discreto ao final: `ABRIR CENTRAL DE AJUDA`.

### 2.5 Lista de Arquivados (Painel 8)
- **Legacy 14.6.1**:
  Tabela com classes `.trcontrole1` e `.tdcontrole1`, colunas:
  `CHAVE (10%)`, `FABRICANTE (15%)`, `DESCRICAO (20%)`, `MODELO (20%)`, `S/N (20%)`, `OS (10%)`.
- **Novo Atual**:
  Utiliza `RmaEloquent::query()->where('status', Status::Arquivado)->get()`, sem paginacao.
- **Decisao**: Manter colunas e larguras exatas do Legacy; adicionar protecao de limite razoavel
  com scroll ou aviso caso o volume cresca, mantendo o visual V1 sem introduzir DataTable moderno.

### 2.6 Mudar Senha (Painel 9)
- **Legacy 14.6.1**:
  Apenas `NOVA SENHA: [input] [SALVAR]`.
- **Novo Atual**:
  Exige corretamente `SENHA ATUAL`, `NOVA SENHA` e `CONFIRMAR NOVA SENHA` (seguranca moderna).
- **Problema**:
  Desalinhamento visual entre labels e inputs nas tres linhas.
- **Decisao**: Preservar a seguranca moderna dos tres campos, mas compor com grade rigida de
  alinhamento (`formLabelPanel` com largura fixa de 160px e inputs alinhados no mesmo eixo X).

### 2.7 Capacidades Promovidas (Capability Union)
- **Paineis Promovidos**:
  1) `LOGS DE AUTENTICAÇÃO` (UF-10 / GAP-V1-03)
  2) `LOGS DE MODIFICAÇÃO DE RMA` (UF-10 / GAP-V1-04/05)
  3) `CADASTRAR NOVO USUÁRIO` (UF-14 / GAP-V1-02)
- **Decisao**:
  Manter como paineis `<details>/<summary class="formTitlePanel">` nativos do V1, com botoes
  no padrao `.formButtonEnviarPanel` estilizados como links de acao, sem cards ou caixas modernas.

---

## 3. Arquitetura de Backend Seguro (Sem Reintroduzir Inseguranca do Legado)

1. **Endpoint `POST /rmas-controle/representante`**:
   - Middleware: `auth`, `tenant`, `csrf`.
   - Gate: `gerenciar, User::class` ou permissao de cadastro de parceiro.
   - Request:
     - `nome`: string, max 255, required.
     - `tipo`: in `['assistencia_tecnica', 'fornecedor', 'fabricante']`.
   - Delegacao: executa a persistencia delegando ao service correspondente
     (`ClienteFornecedorService` / criacao de parceiro com tipo e tenant_id).
   - Redirect: `route('rmas.controle.index')` com `session('status', 'Fornecedor cadastrado com sucesso.')` e flag `session('painel_aberto', 'representante')`.

2. **Endpoint `POST /rmas-controle/arquivar`**:
   - Middleware: `auth`, `tenant`, `csrf`.
   - Gate: `arquivar, $rma`.
   - Request:
     - `numero`: integer/numeric, required.
   - Logica:
     - Localiza o RMA dentro do tenant ativo pelo id/numero.
     - Se nao encontrar: redirect back com erro "RMA {numero} nao encontrado." e reabre o painel.
     - Se encontrar: executa o caso de uso `ArquivarRmaAction` (ou `status = Status::Arquivado` via transacao de ciclo de vida com log de auditoria).
     - Redirect back com sucesso "RMA {numero} arquivado com sucesso." e painel reaberto.

---

## 4. Plano de Execucao por Ondas

- [x] Onda 1: Backend seguro no `ControlePainelController` (`storeRepresentante` e `arquivarRma`) com validacao, tenant e policy.
- [x] Onda 2: View V1 `controle.blade.php`:
  - Formulario unico de Adicionar Representante (Nome + Select Tipo + Botao Adicionar).
  - Formulario de Arquivar sem inline JS.
  - Mensagens discretas de exclusao indisponivel (sem texto interno de docs).
  - Informacao do Procedimento fechada por padrao, tipografia 12px e link Central de Ajuda.
  - Alinhamentos do Mudar Senha.
  - Feedback contextual de erro/sucesso mantendo o painel aberto.
- [x] Onda 3: SCSS `_v1-base.scss` com ajustes pontuais de alinhamento (`formLabelPanel` alinhado, cursor pointer em `<summary>`, feedback V1).
- [x] Onda 4: Testes Feature dedicados (`ControleV1AcoesTest.php`, 8/8 testes verdes) cobrindo criacao de representante, arquivamento valido/invalido e autorizacao.
- [x] Onda 5: Teste Playwright com jornada real de click-through no Controle V1 (`ControleV1Navegacao.spec.ts`, 4/4 testes verdes).
- [x] Onda 6: Validacao completa (PHPUnit 226 testes e Playwright 60 testes 100% verdes).
