# Contrato P7 - encaminhar por seleção validada (UX-003) + PAR-RMA-008

Data: 2026-09-10. Baseline: `origin/main` = `66f904f`. Fonte histórica: backup 15.9.7
(`~/github/_rma-arqueologia/backup-15.9.7/`). Regra de texto: hífen simples.

## 1. O que o Legacy fazia (evidência)

### Escolha do destinatário

- V1 (`14.6.1/page/detalhes.php:186`): `<input name="destinatario" list="destinatario">`
  - texto livre com `<datalist>` de nomes.
- V2 (`15.8.1/page/rma.php:489`): `<input name="destinatario" list="destinatarios"
  maxlength="30">` - mesmo padrão (texto livre + datalist de nomes de
  fabricantes/assistências).
- Não existe no Legacy "tipo + id": o vínculo é resolvido por NOME no backend.
- A ação de ciclo de vida vem de um `<select>` único com opções condicionadas ao
  status: `receber` (entrada), `encaminhar` (recebido), `concluir` (recebido==TRUE).
  Ver `15.8.1/page/rma.php:161-167` e `14.6.1/page/detalhes.php:328`.

### O que a transição grava

- `encaminhar()`: `UPDATE bd SET status='encaminhado', encaminhado=<hoje>`.
  - V1: `14.6.1/banco.oo.php:393`; V2: `15.8.1/banco.php:1782`.
- `concluir()`: `UPDATE bd SET status='concluido', concluido=<hoje>`.
  - V1: `14.6.1/banco.oo.php:413`; V2: `15.8.1/banco.php:1790`.
  - O handler da tela (`15.8.1/pp/salvar_rma.php:255`) chama `concluir($numero)` e,
    além disso, dispara e-mail; não grava estoque/crédito.

## 2. Estado atual do V3

- `resources/views/rma/_acoes_de_transicao.blade.php` (compartilhado V1/V2) pede
  `destinatario_tipo` (select de 3 tipos) + `destinatario_id` como `<input
  type="number">` cru.
- `CicloDeVidaController::encaminhar()` valida o tipo contra uma whitelist
  (`TIPOS_DE_DESTINATARIO`) e um inteiro, e chama `EncaminharRma`. Não há, na
  requisição, prova visual de que o id existe, pertence ao tenant ativo ou é do tipo
  escolhido - o erro só aparece depois (ou nunca, se o id pertencer a outro escopo).

## 3. Contrato do P7 (a implementar)

1. Substituir o par tipo+id cru por UMA seleção validada de destinatário
   (`<select name="destinatario">` com `value` no formato `tipo:id`), populada apenas
   com entidades do tenant ativo.
2. O servidor NUNCA confia no navegador: revalidar formato, tipo permitido, existência
   e vínculo com o tenant (`ContextoDeTenant`) antes de chamar `EncaminharRma`;
   ausência/entidade de outro tenant = erro de validação (não 500, não escrita).
3. Preservar o relacionamento polimórfico `destinatario` (o caso de uso continua
   recebendo `class-string` + id resolvidos no servidor).
4. Manter POST + CSRF + `Gate::authorize('update', RmaEloquent::class)` e o
   comportamento de status (`podeEncaminhar()`), sem mover autorização para o browser.
5. V1 e V2 compartilham o partial: a mudança vale para os dois temas; nenhuma regra de
   negócio nova.
6. Cobertura: Feature (validação/tenant/persistência) + Playwright (seleção aparece,
   encaminha, erro visível para id inexistente/estrangeiro).

## 4. PAR-RMA-008 (conclusão/legado) - decisão fechada com evidência

- Evidência: `concluir()` é IDÊNTICO em 14.6.1 (`banco.oo.php:413`) e 15.8.1
  (`banco.php:1790`) - grava `status='concluido'` + `concluido=<data>`, nada mais.
- O checkbox `marcarestoque` e o campo `solucao` são salvos pelo caminho geral de
  gravação da tela de RMA, não pela transição de conclusão.
- Conclusão: a paridade correta do V3 é manter `concluir` = status + data (como já
  está). Não acoplar estoque/crédito à transição. `DEC-03` deixa de ser
  "diagnóstico insuficiente".

## 5. Implementação (2026-09-10)

- `App\Rma\Aplicacao\Destinatarios\OpcoesDeDestinatario` - whitelist de tipos,
  `agrupadas()` (opções do tenant ativo) e `resolver()` (revalida tipo, existência e
  tenant; inválido/estrangeiro = `ValidationException`, sem escrita).
- `rma/_acoes_de_transicao.blade.php` (V1) - `<select name="destinatario">` com
  `<optgroup>` por tipo e `value="tipo:id"`; o `<input name="destinatario_id">` sumiu.
- `CicloDeVidaController::encaminhar` (V1) e `RmaController::executarAcaoDoDetalhe`
  (detalhe V2) usam o mesmo `resolver()`.
- `EditarRma` passou a revalidar o destinatário no salvamento geral (antes gravava o
  id cru e o slug virava uma classe inexistente - `App\Rma\Aplicacao\AssistenciaTecnica`).
  Com isso a relação polimórfica guarda sempre o FQCN correto.
- Provas: `EncaminharRmaTest` 5/5 (inclui tenant estrangeiro e id inexistente),
  `ContratoVisualAcoesCicloDeVidaTest`, `ParidadeEncaminharSelecaoV1` (browser),
  `ContratoVisualAcoes` 2/2, `SmokesParidadeFuncional -g M-04`; suíte completa
  548 testes / 1691 assertions verde.
