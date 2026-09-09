# Diagnóstico de estado pós-gate - CellSystem RMA V3

Data-base da auditoria: 2026-09-09 (código e histórico lidos nesta sessão).
Objetivo: responder ao checkpoint "como está o sistema e o que falta", cruzando o
gate da Trilha A (`F10-GATE-07`, 2026-09-04) com o estado documental e o código.

## 1. Estado confirmado do repositório

- `main` local, working tree limpa, **14 commits à frente de `origin/main`**, último
  commit `204a335` (`#ARQ-RMA - F10-GATE`, 2026-09-04). Nenhum push foi feito por
  esta sessão.
- Gate da Trilha A **formalmente aprovado** em `docs/qa/relatorio-paridade-final.md`:
  48 `LEG-RMA-*` reconciliados, 6 smokes M-01..M-06, 388 testes/941 asserções PHPUnit,
  58 testes Playwright, migração histórica real de 9 tabelas com reconciliação e
  idempotência, parecer executivo de 2026-09-04 homologado.
- Frentes visuais com gate fechado por evidência independente:
  - Tema V1 fase 1 (CP0–CP5) - `plano-execucao-paridade-estrutural-v1.md`;
  - Tema V1 fase 2 (CP6–CP15) - `plano-execucao-paridade-visual-v1-fase2.md`
    (CP15 **APROVADO e FECHADO** em 2026-09-03, commit `8dcef5c`);
  - Tema V2 (CP16–CP25) - `plano-execucao-paridade-v2.md` (CP25 APROVADO);
  - Auditoria navegacional V1 (NAV-00..NAV-05) - APROVADA em 2026-09-04
    (`plano-execucao-auditoria-navegacional-visual-v1.md`).
- Runtime: containers Docker parados no momento da auditoria; imagens
  `sail-8.3/rma-v3-app`, `mysql:8.4` e `mailpit` disponíveis localmente; PHP 8.3.6,
  `vendor/` e `.env` presentes no host.

## 2. Documentação com estado obsoleto (checklist/planos)

O checklist `docs/produto/checklist-master-v3.md` ainda marca como abertos itens que o
próprio histórico fecha com evidência. Cada caso abaixo cita a prova para a
reconciliação:

| Item no checklist | Estado no texto | Evidência de fechamento |
|---|---|---|
| `QA F10-V1-01..08` e `QA F10-VIS-01..09` | `[ ]` (texto de reabertura de 08-25) | CP0–CP15 + CP16–CP25 + NAV-00..05 aprovados e commitados (ver docs citados acima) |
| `DEV F10-COB-05` (listagem arquivados) | `[ ]` | commit `873e88a` adicionou a listagem de itens arquivados; NAV-04-07 cobre `Arquivar e Listar Controle` |
| `DEV F10-COB-07` (listagem Recebido V2) | `[ ]` | CP23/CP25: abas Entrada/Recebido/Encaminhado/Concluído sempre populadas; `PainelDeStatus::RecebidoSomente` e `_tabela_recebido.blade.php` |
| `DEV H-011 (FRONT-001)` classe de alerta | `[ ]` | corrigido no commit `71b8781`; parecer 2026-09-04 §4; `ClasseDeAlertaTest` 8/8 |
| `DEV H-012 (FRONT-002/PAR-V2-001)` abas V2 | `[ ]` | `RmaController::index` carrega `porStatusV2` sempre; diário CP23 (`CMP-V2-006`) |
| `QA G-04..G-07` | `[ ]` | `F10-GATE-01..07` `[x]` + relatório final + parecer 2026-09-04 |
| Matriz de temas (menu, dashboard, filas, busca NF/número, detalhe, módulos secundários etc.) | `preliminar`, `ausente/parcial` de 08-25 | várias linhas foram fechadas pelos CP/NAV acima; precisa reconciliação linha a linha |

## 3. Lacunas reais confirmadas no código nesta auditoria

### 3.1 FRONT-004 - raiz `/` ainda é scaffold `welcome`

- `routes/web.php`: `Route::get('/', fn () => view('welcome'))`.
- `resources/views/welcome.blade.php` presente (scaffold Laravel).
- `tests/Feature/ExampleTest.php` congela o placeholder (`/` → 200);
  `tests/Unit/ExampleTest.php` só afirma `true`.
- Impacto: quem acessa `/` autenticado não cai no dashboard/fluxo de tema; a suíte tem
  teste que protege o comportamento errado. Ação esperada: `/` deve redirecionar
  (convidado → `login`; autenticado → dashboard/rota do tema), e os `ExampleTest`
  devem ser substituídos por teste real ou removidos.

### 3.2 ARQ-004 / PAR-RMA-001 - busca por NF consulta `os`

- `app/Rma/Infraestrutura/RmasEmBanco.php` (`buscar()`): `nota_fiscal` faz
  `where('os', like, ...)`, com docblock registrando que era provisório "até os campos
  reais de NF entrarem".
- Os campos reais **já existem** no schema/model: `nfcompra`, `nfvenda`,
  `nf_remessa`, `nf_retorno_numero` e as chaves/emissões correlatas.
- `RmaController::index` mapeia `campo=os→nota_fiscal` e faz `NF→fallback texto`.
- Ação esperada: buscar nos campos fiscais reais (compra/venda/remessa/retorno
  conforme o histórico 14.6.1/15.8.1) e cobrir com teste funcional.

### 3.3 ARQ-005 - validação de destinatário/erros esperados

- Encaminhamento usa tipo+ID cru; conversões de domínio (`Solucao::from` etc.) podem
  virar erro 500; RMA ausente em edição não tem tratamento explícito de 404/erro
  esperado. Confirmado pelo texto de `INV-RMA-10` §ARQ-005; sem commit posterior
  endereçando o item.

### 3.4 ARQ-006 - unidade consistente de mutação/auditoria/notificação

- Confirmado pelo texto de `INV-RMA-10`; dependência implícita de `Auth` em
  criar/editar permanece sem commit posterior endereçando o item.

### 3.5 ARQ-007 - custo da home

- `RmaController::index` dispara `contadoresDoPainel()` com 16 counts independentes +
  `ListarGruposDeAlertas` + 4 listagens por status na mesma requisição; é o padrão
  documentado em ARQ-007. Sem medição/commit posterior de redução.

### 3.6 ARQ-009 - duplicação/órfãos de parceiros e views

- Sem commit posterior endereçando duplicação/órfãos; `FRONT-006` exige prova de
  ausência de consumidores antes de remover views genéricas.

### 3.7 PAR/UX ainda sem fechamento comprovado

- Detalhe/RMAs do parceiro (`PAR-PARCEIRO-001`, `VIS-V1-009`, `F10-COB-01`): rotas
  `except(['show'])` nos 4 recursos de parceiros; parecer de 2026-08-25 registra
  "NÃO CORRIGIDO".
- Busca por número histórico (`PAR-RMA-002/004`): sem tipo/campo próprio no
  `CriterioDeBusca`; `numero_legado`/`protocolo` não entram na busca.
- Busca textual ampla (`PAR-RMA-003`): só 6 campos (`descricao`, `defeito`,
  `observacao`, `modelo`, `origem`, `empresa`) contra os ~23 do legado.
- Regra de conclusão por versão (`PAR-RMA-008`): sem prova histórica conclusiva
  registrada; permanece `[DÚVIDA]`/investigação.
- Contratos de UX: `UX-001..004` e `FRONT-003/005` sem commit posterior de fechamento.

## 4. Pendências documentais menores

- `DOC D-05` - contagens/resumos com números de suíte desatualizados nos documentos
  correntes (ex.: `checklist-master-v3.md` consolidação 2026-08-25 com textos antigos).
- `DOC D-07` - fechar OpenSpec da F10 por evidência.
- `ARQ C-02` (RN-12 no Tema V1), `ARQ C-03` (Lightbox2), `ARQ C-04` (skin AdminLTE):
  investigações residuais sem fechamento.
- `DECISAO C-08` - visibilidade do V3: decisão operacional do usuário, fora do
  repositório.

## 5. Trilha B

- `G-08` (liberação formal da Trilha B) permanece condicionada ao plano e à escolha da
  primeira iniciativa com o usuário; o relatório de 2026-09-04 autorizou o
  **planejamento**, e as mudanças desta frente não são código de tenancy/T3.
- Especificações OpenSpec prontas e dimensionadas: `configuracao-admin`
  (`EVO-CONF-001`) e `anexos-de-rma` (`EVO-ARQ-001`), ambas com pré-requisito F10
  satisfeito; nada implementado ainda.

## 6. Recomendação de próximos lotes

1. **DOC/estado:** reconciliar gates `G-04..G-07`, checklist F10 e matriz de temas com
   as evidências acima; atualizar `PLAN.md`/`PLANO-ATAQUE.md`.
2. **FRONT-004/D-06:** remover placeholder `/` e testes-fantasma (fecha lacuna real e
   higiene).
3. **ARQ-004:** busca por NF nos campos fiscais reais, com teste (fecha lacuna real
   rastreada desde `INV-RMA-10`).
4. **DOC residual:** C-02/C-03/C-04 com evidência dirigida ao legado, fechando ou
   transferindo para `[DÚVIDA]`.
5. **PAR/UX:** priorizar itens com prova histórica (parceiro-detalhe, busca por
   número/texto, boletins relacionados) em lotes pequenos.
6. **Trilha B:** apresentar plano com primeira iniciativa escolhida para decisão do
   usuário (sugestão: `EVO-CONF-001`, já totalmente especificado).
