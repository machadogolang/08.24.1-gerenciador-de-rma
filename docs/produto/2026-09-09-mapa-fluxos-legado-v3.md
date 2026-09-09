# Mapa de fluxos — Legacy executável × V3

Data: 2026-09-09. Frente: paridade total dirigida por fluxos (OpenSpec
`paridade-fluxos-legado-v3`). Fontes: V3 `main` local `a254d58`/origin `75c110d`;
Legacy `08.24.4-legacy-gerenciador-de-rma` HEAD `f83542c` (somente leitura).

Cada fluxo tem ID estável `FLOW-*`. Quando existe ID `LEG-RMA-*` correspondente, o
fluxo referencia a matriz de paridade. Status de paridade usa as classes da instrução:
A PARIDADE REAL · B BUG V3 · C FUNCIONALIDADE LEGACY AUSENTE · D GAP DE NAVEGAÇÃO ·
E GAP VISUAL · F BUG LEGACY (não reproduzir) · G DIVERGÊNCIA V3 CONSCIENTE/SEGURA ·
H DOC DESATUALIZADA · I DECISÃO DO DONO · J ROTA/CODIGO MORTO.

## 1. Perfil de campos usado para completar cada fluxo

Para cada fluxo abaixo, a verificação registra: ator/papel; pré-condição; ponto de
entrada; passos Legacy (V1/V2); passos V3 (V1/V2); campos envolvidos; validações;
transições de estado; efeitos no banco; side effects/e-mail/auditoria; permissão;
rotas/views; teste existente; teste faltante; status; ação necessária. O detalhe por
fluxo fica no corpo deste documento e o controle de fechamento no OpenSpec.

## 2. Inventário de fluxos

| ID | Fluxo | Entrada Legacy | V3 | Status | Ação |
|---|---|---|---|---|---|
| FLOW-RMA-001 | Login | `/login` ambos | `SessaoController` | A | manter; Playwright fluxo |
| FLOW-RMA-002 | Logout | V1/V2 menu | `POST /logout` | A/G | manter (POST seguro V3) |
| FLOW-RMA-003 | Trocar V1↔V2 | V1 `menuright.php`, V2 `menu.php` → `trocarapp.php` | `POST /tema/alternar`; V2 tem item; **V1 não tem** | D/B | adicionar item V1 (P1) |
| FLOW-RMA-004 | Trocar própria senha | V1 funcional; V2 quebrado | `TrocarPropriaSenha` | G (V1 spec) | manter/cobertura |
| FLOW-RMA-005 | Gestão usuários/papéis/reset | Controle V1/V2 | `UsuarioController` | A | manter |
| FLOW-RMA-006 | Anotação pessoal | V1 quadro; V2 `page/anotacoes.php` | perfil + quadro V1 | C/D parcial | criar página dedicada V2 reaproveitando caso (P8) |
| FLOW-RMA-010 | Cadastrar cliente | V2 subp `novo_cliente` | `ClienteController` | A | manter |
| FLOW-RMA-011 | Cadastrar fabricante | V2 subp | `FabricanteController` | A | manter |
| FLOW-RMA-012 | Cadastrar fornecedor | V2 subp | `FornecedorController` | A | manter |
| FLOW-RMA-013 | Cadastrar assistência | V2 subp | `AssistenciaTecnicaController` | A | manter |
| FLOW-RMA-014 | Editar parceiro | V2 `ver_*` form Salvar | V1/V2 `_form` | A | manter |
| FLOW-RMA-015 | Remover parceiro | V2 ícone Apagar | form DELETE | B/G | confirmação UX-002 (P8) |
| FLOW-RMA-016 | Detalhe/RMAs do parceiro | V1 `page/*`, V2 `ver_*` | sem rota `show` | C | implementar (P5) |
| FLOW-RMA-020 | Criar RMA | V1 painel Novo, V2 aba novo_rma | `RmaController::create/store` | A | manter |
| FLOW-RMA-021 | Localizar texto | V1 localizar, V2 pesquisar | `BuscarRmas` | A/G parcial | ampliar contrapartes (P6) |
| FLOW-RMA-022 | Localizar número/CHAVE | V1 CHAVE | `numero_legado` | A | manter |
| FLOW-RMA-023 | Localizar serial/SN/PN/SNID | V1/V2 | `CriterioDeBusca` | A | manter |
| FLOW-RMA-024 | Localizar NF | V1/V2 | `nota_fiscal` | A | manter |
| FLOW-RMA-025 | Visualizar RMA | V1 detalhes, V2 rma | `RmaController::show` | A | manter |
| FLOW-RMA-026 | Editar RMA | V1 detalhes, V2 rma | `RmaController::edit/update` | A | manter |
| FLOW-RMA-030 | Listagem Entrada | V1 `entrada`, V2 aba entrada | V1/V2 | A | manter |
| FLOW-RMA-031 | Receber | V1 select+OK, V2 select+OK | `POST receber` | A | manter |
| FLOW-RMA-032 | Encaminhar | V1/V2 select+OK | `POST encaminhar` | B/C | UX-003 destinatário por seleção (P7) |
| FLOW-RMA-033 | Concluir | V1/V2 | `POST concluir` | A | manter; investigar lançamento (P7) |
| FLOW-RMA-034 | Registrar solução | V1/V2 | `POST solucao` | A | manter |
| FLOW-RMA-035 | Arquivar | V2 ok; V1 Fatal Error | `POST arquivar` | A/G | V2 spec |
| FLOW-RMA-036 | Reverter | V1/V2 | `POST reverter` | A | manter |
| FLOW-RMA-040 | Alertas | V1/V2 painel | `PainelDeAlertas` shell | A | manter |
| FLOW-RMA-041 | Centro de avisos | V1 startpage, V2 inicio | home | A | manter |
| FLOW-RMA-042 | Filas por status | V1/V2 | listagens/abas | A | manter |
| FLOW-RMA-043 | Filtros solução/status | V1/V2 | busca solução; RPEC status | A | manter |
| FLOW-RMA-050 | Crédito pendente | V1 listagem, V2 painel | aguardando crédito | A | manter |
| FLOW-RMA-051 | Solução GERADO CREDITO | V1/V2 | select solução | A | manter |
| FLOW-RMA-052 | Marcar crédito disponível | V2 painel | `MarcarCreditoDisponivel` | A | manter |
| FLOW-RMA-060 | RCD | V1 relatorios RCRD, V2 relatorios | shell UI-03 | A | manter |
| FLOW-RMA-061 | RPEC | V1/V2 | shell UI-03 | A | manter |
| FLOW-RMA-062 | RMPE | V1/V2 | shell UI-03 | A | manter |
| FLOW-RMA-070 | Frete Porto Alegre | V2 | shell UI-04 | A | manter |
| FLOW-RMA-071 | Boletins relacionados | V2 | shell UI-04 | A | manter |
| FLOW-RMA-080 | Histórico de RMA | V2 controle logs | shell UI-04 | A | manter |
| FLOW-RMA-081 | Histórico de acesso | V2 controle logs | shell UI-04 | A | manter |
| FLOW-RMA-090 | Controle V1 | V1 page/controle | painel V1 | A/E | UI-05 alinhamento |
| FLOW-RMA-091 | RMAs arquivados | V1 controle | listagem V1 | A | reconciliar doc |
| FLOW-RMA-092 | Procedimento RMA | V1 help | Controle V1 | A | reconciliar doc |
| FLOW-RMA-100 | Notificações/e-mail | V1/V2 conclusão | Mailable | G | manter |

## 3. Fluxos/rotas além dos 48 LEG-RMA encontrados na árvore Legacy

| ID | Achado | Prova | Classificação |
|---|---|---|---|
| FLOW-EXT-001 | V2 `page/anotacoes.php` + menu `Anotacoes` | arquivo e `inc/menu.php` apontam | C (mesma anotação pessoal) |
| FLOW-EXT-002 | V2 rota `pomodoro` | `.htaccess` → `?p=pomodoro`; **nenhum** `page/pomodoro.php` existe | J (rota morta) |
| FLOW-EXT-003 | V2 `avisar/{id}` e `page/avisar_alguem.php` | arquivo real e rewrite | I (e-mail a quem avisar; requer investigar destinatários/dado) |
| FLOW-EXT-004 | V2 `enviar_email/{id}` e `page/enviar_email.php` | arquivo real e rewrite | I (fluxo de e-mail manual; investigar uso) |
| FLOW-EXT-005 | V2 `marcarcomo/{st}/{id}` | rewrite; `subp/marcarcomo.php` | J/I (sem rota no menu; verificar uso real) |
| FLOW-EXT-006 | V2 `representantes`/`novo_representante` e V1 Representante | page `representantes.php`, menu | I/C (V3 só representante como campo de parceiro; verificar equivalência) |

## 4. Estado de paridade resumido

- Já conformes (A): identidade, cadastros, RMA núcleo/ciclo, crédito, relatórios,
  alertas, filas, históricos e logística — todos com testes e/ou shell por tema.
- Bugs V3 conhecidos: troca de tema no menu V1 (D) e UX de destinatário por ID (B).
- Gaps Legacy→V3: detalhe de parceiro/RMAs (C), busca por contrapartes (C parcial),
  página de anotações V2 (C).
- Bug Legacy descartados: Fatal Error arquivar V1, troca de senha V2, hard deletes,
  SQL injection de busca, rota `retornou`/`pomodoro` morta.
- Docs desatualizadas: itens F10-COB-05/06/07 e H-018/H-020 citados no checklist foram
  reabertos? — verificar runtime antes de reconciliar (rodada P10).

## 5. Regra dos quatro quadrantes

Tema muda apresentação; não muda regra, permissão, informação, tenant ou capacidade
funcional. Diferenças V1×V2 no Legacy são resolvidas pela versão funcional como
especificação (ex.: arquivar usa V2; troca de senha usa V1).
