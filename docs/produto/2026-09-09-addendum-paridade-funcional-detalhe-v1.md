# Addendum - Paridade funcional do detalhe RMA Tema V1 (reabertura A5)

Data: 2026-09-09. Baseline HEAD `7d095de` = origin/main. Fonte viva:
`PLANO-ATAQUE.md`. PUSH NAO AUTORIZADO.

## 1. Contexto

O dono validou no browser o detalhe do Tema V1 depois da rodada visual e encontrou
duas regressões reais de comportamento. A5 foi marcado `[x]` cedo demais: a
aparência aproximou do Legacy, mas a paridade funcional (edição inline, controles
reais e persistência) não foi preservada. A7/T3-11 permanece `[ ]` e nao deve ser
iniciado antes do A5 fechar de novo com prova funcional.

Arquivo atual apontado: `resources/views/temas/v1/rma/show.blade.php`. A ultima
implementacao converteu campos editáveis do Legacy em `<span class="TDDX
somente-leitura">` e acrescentou `.somente-leitura { pointer-events: none; }`.

## 2. IDs

### PAR-DET-V1-EDIT-01 - campos do boletim viraram spans somente leitura

| Campo | Valor |
|---|---|
| Rota | `GET /v1/rma/{id}` |
| Sintoma | Inputs reais do Legacy (`<input class="TDDX">`, `<input class="TDD_NF">`)
  foram substituídos por spans com pointer-events:none; o usuário não consegue
  editar na própria tela. |
| Legacy | `legacy-source/14.6.1/page/detalhes.php` |
| V3 atual | `resources/views/temas/v1/rma/show.blade.php` |
| Causa raiz | Decisão anterior de manter show separado de edit foi aplicada de
  forma absoluta ao Tema V1, mas o Legacy V1 é uma tela de edição inline: o detalhe
  abre em form com inputs reais e salva por `post/processa_detalhes.php`. |
| Correcao proposta | Restaurar `<form>` no detalhe V1 com inputs reais integrados à
  célula (mesma linguagem visual TDDX/TDD_NF/TDD_DEFEITO/textarea), sem
  pointer-events:none, e persistir pelo backend V3 seguro (RmaController/EditarRma/
  Policy/CSRF/tenant), nunca copiando o post monolítico do Legacy. |

Campos Legacy editáveis confirmados (fonte detalhes.php):
fabricante, descricao, modelo, os, origem, sn, empresa, pn, snid, cliente,
nfentrada_cli, nfretorno_cli, rastreio_ida, rastreio_retorno, nfcompra,
nfcompra_emissao, nfcompra_chave, nfvenda, nfvenda_emissao, nfvenda_chave,
nfremessa, nfremessa_emissao, nfremessa_chave, nfretorno, nfretorno_emissao,
nfretorno_chave, destinatario, nfdevolucaodevenda, valor, snretorno,
destinatario_email, destinatario_fone, protocolo, solucao, defeito, observacao.

Campos Legacy não editáveis: numero do BD (input desabilitado) e tempo/prazo
(input desabilitado). Políticas de garantia são textarea desabilitada no Legacy.

### PAR-DET-V1-ACTION-01 - rodapé operacional perdeu composição

| Campo | Valor |
|---|---|
| Rota | `GET /v1/rma/{id}` |
| Sintoma | V3 empilha texto de estoque/crédito, Editar e ações de ciclo de vida à
  esquerda; Legacy posiciona controles de estoque/crédito à esquerda e painel
  operacional `select acao` + `OK` à direita. |
| Legacy | final de `legacy-source/14.6.1/page/detalhes.php` |
| Causa raiz | A restauração visual manteve blocos de ações genéricos do partial
  compartilhado no fluxo vertical, sem reproduzir o rodapé histórico do V1. |
| Correcao proposta | Rodapé V1 com dois lados: esquerda com checkboxes reais de
  `marcarestoque`/`creditoDisponivel`; direita com seletor de ação compatível com a
  máquina de estados atual e botão OK. Podem ser forms separados/seguros, sem
  endpoint monolítico e sem margin negativa aleatória. |

### PAR-DET-V1-STOCK-01 - estoque e crédito viraram texto estático

| Campo | Valor |
|---|---|
| Rota | `GET /v1/rma/{id}` |
| Sintoma | V3 mostra "O ITEM E DO ESTOQUE"/"MARQUE P/ VALIDAR CREDITO" como texto;
  Legacy usa `<input type="checkbox">` reais que persistem no mesmo save. |
| Correcao proposta | Restaurar checkbox reais no detalhe V1 e persistir por caso de
  uso moderno (ver regra atual de crédito antes de decidir divergência). |

## 3. Fluxo de correção

1. Matriz campo por campo (Legacy x coluna V3 x edição atual x update atual x ação).
2. Extensão/uso da camada de aplicação para os campos que o update atual não grava
   (nunca SQL/update direto na view).
3. Show V1 com form e inputs reais.
4. Rodapé esquerda/direita com controles reais.
5. Testes browser de digitar/salvar/reload + alinhamento.
6. Regressão V1/V2 e fechamento.

## 4. Decisão registrada

- A tela `GET /v1/rma/{id}` volta a ter edição inline (comportamento do Tema V1).
- A rota `GET /v1/rma/{id}/edit` continua existindo como alternativa.
- O Tema V2 nao muda automaticamente; cada tema compara com o proprio Legacy.
- A7/T3-11 segue bloqueado ate A5 fechar.


## 5. Resultado pos-correcao (mesma sessao)

### Correcoes

- PAR-DET-V1-EDIT-01: show V1 voltou a ser form real (POST+PUT em v1.rmas.update,
  CSRF/Policy) com inputs TDDX/TDD_NF/TDD_DEFEITO e textarea TDD_TAOBSERVACAO.
- PAR-DET-V1-STOCK-01: checkboxes reais de marcarestoque e creditoDisponivel com
  rotulos alternando no JS.
- PAR-DET-V1-ACTION-01: rodape com estoque/credito a esquerda e painel select
  acao+OK a direita. As acoes especificas continuam em bloco recolhivel
  (detalhe-bd-acoes-avancadas) para manter endpoints/contratos sem empilhar o topo.

### Backend

- Dominio/Rma passou a carregar e persistir os campos historicos editaveis
  (nfentrada/retorno cliente, rastreios, nfremessa/nfretorno/chaves, emails/fone,
  devolucao de venda, destinatario_nome_legado).
- RmasEmBanco hidrata/grava esses campos pela mesma infra.
- EditarRma aceita o boletim completo e so altera campos presentes (formularios
  antigos de edit continuam seguros).
- RmaController::update despacha acao do rodape para os casos de uso modernos
  (receber/encaminhar/concluir/reverter/arquivar) depois de salvar os campos.
- Solucao do detalhe e gravada via RegistrarSolucao (caso de uso moderno).

### Matriz de campos (resumo)

| Campo Legacy | Coluna/dominio V3 | Tipo V3 | Persiste? |
|---|---|---|---|
| fabricante | fabricante_id (select) | select moderno | sim |
| descricao/modelo/os/origem/sn/empresa/pn/snid | colunas nucleo | input | sim |
| cliente | cliente_id via cliente_nome | input | sim |
| NF compra/venda + datas + chaves | rmas nfcompra*/nfvenda* | input | sim |
| NF entrada/saida cliente | colunas legado | input | sim |
| NF remessa/retorno + chaves | colunas legado | input | sim |
| rastreios | colunas legado | input | sim |
| destinatario | morph type/id via select seguro | select | sim |
| email/fone destinatario | colunas legado | input | sim |
| protocolo/valor/snretorno | nucleo | input | sim |
| solucao | nucleo via RegistrarSolucao | select | sim |
| defeito/observacao | nucleo | input/textarea | sim |
| marcarestoque | boolean nucleo | checkbox | sim |
| creditodisponivel | boolean nucleo | checkbox | sim |
| numero do BD | legado/id | disabled | nao |
| tempo | calculado | disabled | nao |
| politica de garantia | parceiro | textarea disabled | nao |

### Divergencias conscientes (documentadas, nao silenciosas)

1. Fabricante e destinatario usam selects com entidades reais no lugar do texto
   livre/datalist do Legacy (seguranca/tenant/polimorfismo modernos).
2. O checkbox creditodisponivel pode ser gravado direto no detalhe V1, como no
   Legacy; a rota moderna MarcarCreditoDisponivel permanece com sua regra propria.
3. Bloco de acoes avancadas fica recolhido (details) para nao replicar a pilha
   vertical que o dono reprovou, preservando os endpoints/testes.

### Commits

- 2beecb3 - #FRONT-RMA - Restaura edicao inline do boletim no Tema V1.
- 70a2ed8 - #QA-RMA - Cobre edicao e alinhamento do detalhe RMA V1.

### QA

- PHPUnit completo: 523 testes / 1518 assertions, 100% verde.
- Playwright EdicaoInlineDetalheV1: 3/3 (editar/salvar/reload, acao do rodape,
  alinhamento esquerda/direita).
- Regressao Playwright V1/V2/consistencia: 27/27 verdes.
- Vite build verde; git diff --check limpo.
