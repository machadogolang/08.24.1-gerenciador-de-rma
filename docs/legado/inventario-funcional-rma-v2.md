# Inventário funcional — CellSystem RMA V2 (15.9.7)

Data: 2026-08-24. Catálogo de funcionalidades reais do produto, com ID estável
(`LEG-RMA-NNN`). Fonte: leitura já feita de TEMA V1 (14.6.1), TEMA V2 (15.8.1) e camada
compartilhada — ver `docs/legado/regras-negocio-rma-legado.md` e
`docs/legado/modelo-dominio-rma-legado.md` para o detalhe de cada regra referenciada
aqui. Este documento não repete a regra inteira, referencia por RN-XX quando já existe.

Colunas: ID · Nome · Tema V1 · Tema V2 · Rota/tela · Permissão · Situação.
Situação: **funcional** · **parcial** · **quebrado** · **legado** · **código morto** ·
**inconclusivo**.

## Autenticação e usuários

| ID | Nome | Tema V1 | Tema V2 | Rota/tela | Permissão | Situação |
|---|---|---|---|---|---|---|
| LEG-RMA-001 | Login/logout | `inc/signin.php` | `pp/senha.php`, `page/logout.php`; `login.php` órfão (redireciona sem renderizar) | tela inicial de cada tema | pública (login), qualquer autenticado (logout) | funcional |
| LEG-RMA-002 | Autocadastro de usuário com convite | `inc/signup.php` — exige "Key" comparado a segredo fixo hardcoded | **[DÚVIDA]** equivalente em Tema V2 não verificado | tela de signup do Tema V1 | pública + segredo de convite | funcional (Tema V1); dúvida (Tema V2) |
| LEG-RMA-003 | Resetar senha de outro usuário (admin) | `post/mudar_senha.php` (grava mesmo valor em `Key1461`/`Key1581`) | `subp/resetar_senha.php` (correta) | tela de gestão de usuários | permissão ≥3 | funcional |
| LEG-RMA-004 | Trocar a própria senha | `mudarSenha()` — SQL **correto**, funcional | `alterar_senha()` — SQL inválido (`SET ... SET ...`) | tela de perfil | qualquer autenticado | **funcional em Tema V1, quebrado em Tema V2** — regressão confirmada (RN-21), V3 usa Tema V1 como especificação |
| LEG-RMA-005 | Gerenciar usuários e permissões | `menujs-right/usuarios.php` | `subp/usuarios.php`, `pp/mudar_permissao.php` | painel de usuários | permissão ≥3 (gerenciar), ≥4 invisível na lista | funcional |
| LEG-RMA-006 | Selecionar/trocar tema (V1↔V2) | `inc/menuright.php` (link "Trocar p/ 15.8.1") | link equivalente | `trocarapp.php` | qualquer autenticado | funcional |
| LEG-RMA-043 | Auditoria de autenticação (log) | grava em `log` | idem | `subp/logs_de_autenticacao.php` | permissão ≥3 vê ampliado (exceto dev), ≥4 vê tudo | funcional |
| LEG-RMA-044 | Auditoria de modificação de RMA | `registra_modificacao()` (compartilhada) | idem | `subp/logs_de_modificacao.php` | permissão ≥3 | funcional |

## Ciclo de vida do RMA

| ID | Nome | Tema V1 | Tema V2 | Rota/tela | Permissão | Situação |
|---|---|---|---|---|---|---|
| LEG-RMA-007 | Cadastrar novo RMA | `post/novo.php` | `pp/novo_rma.php` | `menujs-top/novo.php` (V1), `page/entrada.php`+`inc/menu_novo.php` (V2) | qualquer autenticado (gravação exige >1) | funcional |
| LEG-RMA-008 | Localizar/pesquisar RMA | `page/localizar.php` | `subp/pesquisar_{rma,nf,sn,descricao}.php` — **os 4 são o mesmo código** (mesmo MD5), busca genérica em 23 colunas | `menujs-top/localizar.php` (V1), `page/pesquisar.php` (V2) | qualquer autenticado | funcional (V2 tem SQLi confirmada, ver §Segurança em `INV-RMA-00`) |
| LEG-RMA-009 | Ver detalhes do RMA | `page/detalhes.php` | `page/rma.php` (759 linhas — tela central do sistema) | rota por número | qualquer autenticado (edição exige >1) | funcional |
| LEG-RMA-010 | Editar/salvar RMA | `post/processa_detalhes.php` | `pp/salvar_rma.php` | formulário de detalhes | permissão >1 (senão dispara e-mail de alerta, RN correlata) | funcional — é o núcleo de todas as normalizações (RN-13 a RN-17) |
| LEG-RMA-011 | Receber RMA (transição) | `banco.oo.php::receber()` | `banco.php::receber()` | select de ação em `page/detalhes.php`/`page/rma.php` | >1 | funcional |
| LEG-RMA-012 | Encaminhar RMA (transição) | idem, `encaminhar()` | idem | idem | >1, exige `destinatario` preenchido (validação JS) | funcional |
| LEG-RMA-013 | Concluir RMA (transição) | idem, `concluir()` | idem | idem | >1, exige `solucao`+confirmação de lançamento em estoque (validação JS) | funcional — dispara `ezequiel()` (e-mail de conclusão) |
| LEG-RMA-014 | Arquivar RMA | **[BUG-LEGADO]** `post/arquivar.php` chama classe inexistente → Fatal Error | `banco.php::arquivar()` — funcional, status paralelo reabrível | painel de controle | permissão ≥3 (inferido, não confirmado) | **quebrado** (Tema V1) / funcional (Tema V2) — ver RN correlata "arquivar" em `INV-RMA-00` |
| LEG-RMA-015 | Retornar RMA para "entrada" (rollback) | permitido só no mesmo dia do encaminhamento, ou permissão==4 | regra idêntica (bug cosmético de string com espaço numa das duas cópias do select) | select de ação | >1 (mesmo dia) / ==4 (qualquer dia) | funcional |
| LEG-RMA-016 | Estado "retornou" | rota existe (`.htaccess`), tela vazia | idem | — | — | **código morto** em ambos os temas |
| LEG-RMA-017 | Registrar solução/resolução | domínio de 17 valores (`REPARO`, `TROCA DO PRODUTO`, `GERADO CREDITO`, `PROCON`, `SEM GARANTIA`...) | idêntico | parte do formulário de detalhes | >1 | funcional |

## Alertas / filas de prioridade (as 10 regras — camada compartilhada)

Todas em `metodo.php`, herdadas por ambos os temas (Tema V1 via include cruzado da pasta
de Tema V2). Detalhe completo em `regras-negocio-rma-legado.md` RN-01 a RN-10.

| ID | Nome | RN correspondente | Situação |
|---|---|---|---|
| LEG-RMA-018 | Recebido >30d e não encaminhado | RN-01 | funcional (com bug de `num_rows` mentiroso) |
| LEG-RMA-019 | Não vai dar garantia (inclui regra MARKVISION) | RN-02 | funcional (com bug de `num_rows` e de formato de data) |
| LEG-RMA-020 | NF de retorno pendente de lançamento | RN-03 | funcional |
| LEG-RMA-021 | Protocolo aberto mas não encaminhado | RN-04 | funcional |
| LEG-RMA-022 | Garantia do fornecedor expirada (>1 ano) | RN-05 | funcional (bug de `num_rows`) |
| LEG-RMA-023 | Menos de 30 dias para expirar garantia | RN-06 | funcional — único que corrige `num_rows` corretamente |
| LEG-RMA-024 | Prazo do destinatário estourado | RN-07 | funcional (bug de `num_rows`) |
| LEG-RMA-025 | Prioridade alta sem encaminhamento | RN-08 | funcional |
| LEG-RMA-026 | Sem nota fiscal (compra e venda) | RN-09 | funcional |
| LEG-RMA-027 | Sem número de série | RN-10 | funcional |
| LEG-RMA-028 | Classificação visual de inconformidade (TrInconformidade/TrUrgente/TrSemGarantia/TrZebrada) | RN-11 | funcional em Tema V2; **[DÚVIDA]** equivalente exato em Tema V1 |
| LEG-RMA-029 | Urgência por threshold econômico (R$ 75) | RN-12 | funcional, **confirmado só em Tema V2** (`15.8.1/banco.php`, não na camada compartilhada) |

## Cadastros de contraparte

| ID | Nome | Tema V1 | Tema V2 | Situação |
|---|---|---|---|---|
| LEG-RMA-030 | Cadastro de clientes (CRUD) | `page/cliente.php` | `subp/{listar,novo,editar,apagar,ver}_cliente*.php` | funcional; auto-criação sem dedup real (ver `modelo-dominio-rma-legado.md`) |
| LEG-RMA-031 | Cadastro de fabricantes (CRUD) | `page/fabricante.php` | `subp/*_fabricante*.php` | funcional |
| LEG-RMA-032 | Cadastro de fornecedores (CRUD) | `page/fornecedor.php` | `subp/*_fornecedor*.php` | funcional |
| LEG-RMA-033 | Cadastro de assistências técnicas (CRUD) | `page/assistencia_tecnica.php` | `subp/*_assistencia_tecnica*.php` | funcional |
| LEG-RMA-034 | "Autorizada" (entidade separada) | não existe | arquivos `subp/*_autorizada*.php` existem, cópias de `*_assistencia_tecnica*`, sem rota | **código morto** (Tema V2) |
| LEG-RMA-035 | Tabela unificada `assistencias(tipo)` | `menujs-right/fornecedores.php` referencia; inconsistente com política de garantia | não referenciada | **legado/abandonado** — só Tema V1, tentativa de unificação não completada |

## Financeiro / logística

| ID | Nome | Tema V1 | Tema V2 | Situação |
|---|---|---|---|---|
| LEG-RMA-036 | Fluxo de crédito (pendente→gerado→disponível) | `page/aguardandocredito.php` | `page/credito.php` | funcional (controle manual em duas camadas independentes, ver `modelo-dominio-rma-legado.md`) |
| LEG-RMA-037 | Relatório RCD — créditos disponíveis | presente | presente | funcional |
| LEG-RMA-038 | Relatório RPEC — produtos em estoque para contagem | presente | presente | funcional |
| LEG-RMA-039 | Relatório RMPE — produtos encaminhados pelo RMA | presente | presente | funcional (um relatório tem intervalo hardcoded para 2014 — bug, RN correlata) |
| LEG-RMA-040 | Consolidação de frete por cidade (Porto Alegre) | presente, **código morto/comentado** (`inc/startpage.php`) | `right_portoalegre()`, ativa | funcional só em Tema V2; existiu e foi desativada em Tema V1 (RN-16) |
| LEG-RMA-041 | Boletins de defeito relacionados (histórico por contraparte) | **[DÚVIDA]** | rodapé de `page/rma.php`, sem LIMIT | funcional (Tema V2), risco de performance |
| LEG-RMA-042 | Bloco de notas pessoal do usuário | `post/salvarnotas.php` | equivalente **[DÚVIDA]** | funcional (Tema V1 confirmado) |
| LEG-RMA-048 | Módulo de Créditos "pendentes/usados/disponíveis" | N/A — nunca existiu essa divisão em Tema V1 | rotas existem no `.htaccess`, arquivos de destino não existem | **quebrado só em Tema V2** (RN-18); Tema V1 nunca tentou o split |

## Normalização de dados (regras escondidas na gravação)

| ID | Nome | Situação |
|---|---|---|
| LEG-RMA-045 | Notificação por e-mail (conclusão / tentativa negada) | funcional, destinatários hardcoded (`naopermitido()`, `ezequiel()`) |
| LEG-RMA-046 | Normalização automática (HGST→Hitachi, cascata de `origem`) | **funcional em ambos os temas**, implementação duplicada (não compartilhada) — RN-13/RN-14 |
| LEG-RMA-047 | S/N de retorno auto-preenchido (anti-fraude) | funcional só em Tema V2 — **ausente em Tema V1** (RN-15) |

## Cobertura e método

Este inventário nasceu da leitura já registrada em `regras-negocio-rma-legado.md`,
`modelo-dominio-rma-legado.md` e dos relatórios originais de arqueologia — não é uma
nova leitura de código, é a primeira consolidação em formato de catálogo com ID estável.
48 itens catalogados. **ARQ-06b concluído (2026-08-24):** comparação linha a linha
`14.6.1/post/*` vs `15.8.1/pp/*` resolveu as dúvidas de LEG-RMA-004, 040, 046, 047, 048 —
ver `regras-negocio-rma-legado.md` §Notas de cobertura para o detalhe. Único item ainda
em aberto: LEG-RMA-029 (threshold R$75) — busca textual não encontrou equivalente em
Tema V1, sem confirmação absoluta por leitura linha a linha completa.

**Não catalogado ainda** (pendente de leitura): plugins JS específicos além dos já
citados (iCheck, Lightbox — uso real não confirmado), atalhos de teclado (nenhum
encontrado até agora, não é garantia de que não existam), comportamento AJAX específico
(bloco de notas usa AJAX; não verificado se outras telas usam), menu contextual (nenhum
encontrado até agora).
