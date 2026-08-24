# Proposal — Alertas e prioridade (as 10 regras + MARKVISION + threshold)

Fase 5 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §10).

## Por quê

As 10 regras de alerta (`RN-01` a `RN-10`) são a funcionalidade mais sofisticada do
legado — todas na camada compartilhada, herdadas por ambos os temas. É o painel que o
operador olha para saber o que fazer primeiro. A Fase 4 já dá a este módulo tudo que
precisa (`status` + datas por transição); esta fase é a primeira a de fato ler esses
dados para produzir uma lista priorizada.

## O que entra

- As 10 regras (`LEG-RMA-018` a `027`), cada uma como classe própria em
  `app/Rma/Aplicacao/Alertas/`, filtrando por data **no SQL** (nunca em PHP pós-`SELECT`
  como o legado — decisão central desta fase, ver `design.md`).
- Classificação visual de inconformidade (`LEG-RMA-028`/RN-11), enum `ClasseDeAlerta`.
- Threshold econômico de urgência R$75 (`LEG-RMA-029`/RN-12).
- Enums `Origem` (fixa o domínio provisório da Fase 3), `Prioridade` (sem o valor morto
  `urgente`), `StatusDeLancamento`.
- Colunas `prioridade`, `marcarestoque`, `origem` (cast), NF (só os blocos compra/venda,
  usados pelas regras), `lancadoretorno`.

## O que não entra

- Fidelidade visual das cores/CSS por tema (Fase 8) — aqui só o enum de domínio existe.
- Crédito de fato (fluxo `PendenteCredito`→`GeradoCredito`, Fase 6).
- Consolidação de frete Porto Alegre (Fase 6).
- Notificação por e-mail enviada de fato (Fase 7 — o evento nasce na Fase 4/aqui, o
  envio é Fase 7).

## Decisão registrada — onde mora o cálculo de data

O legado traz o `SELECT` sem filtro de data e filtra `Diferenca_de_dias()` linha a linha
em PHP na view — causa direta do bug de `num_rows` mentiroso confirmado em 6 das 10
regras (`regras-negocio-rma-legado.md`). **A V3 filtra por data inteiramente no SQL**
(query builder do Eloquent) — o `SELECT` já devolve só as linhas corretas, eliminando a
classe de bug por construção. Cada regra é uma classe pequena em `Aplicacao/Alertas/`,
não um serviço único de 10 métodos (que reproduziria a bagunça de `metodo.php`).

## Decisão registrada — RN-12 (threshold R$75) implementada para os dois temas

Confirmado nesta revisão: RN-12 vive em `15.8.1/banco.php` (fora de `metodo.php`, a
camada realmente compartilhada) — é a única das 12 regras (10 alertas + RN-11 + RN-12)
fora dessa camada. Busca textual em `14.6.1/menujs-right/` e `14.6.1/page/` continua sem
achar equivalente (mesma conclusão de ARQ-06b). **Decisão adotada:** implementar uma vez
no domínio compartilhado, tratando a localização em `banco.php` como provável acidente
de organização de arquivo, não exclusão deliberada — mas **isto é inferência, não
evidência direta**, registrado como tal. Se o usuário souber que a exclusão foi
deliberada, reverter para regra condicionada ao tema ativo.

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `RecebidosSemEncaminhar30Dias` | RN-01, `LEG-RMA-018` |
| `NaoVaiDarGarantia` | RN-02, `LEG-RMA-019` (inclui regra MARKVISION hardcoded) |
| `NfRetornoPendenteDeLancar` | RN-03, `LEG-RMA-020` |
| `ProtocoloAbertoNaoEncaminhado` | RN-04, `LEG-RMA-021` |
| `GarantiaFornecedorExpirada` | RN-05, `LEG-RMA-022` |
| `GarantiaFornecedorExpirandoEm30Dias` | RN-06, `LEG-RMA-023` |
| `PrazoDestinatarioEstourado` | RN-07, `LEG-RMA-024` |
| `PrioridadeAltaSemEncaminhar` | RN-08, `LEG-RMA-025` |
| `SemNotaFiscal` | RN-09, `LEG-RMA-026` |
| `SemNumeroDeSerie` | RN-10, `LEG-RMA-027` |
| `ClasseDeAlerta`/`Rma::classeDeAlerta()` | RN-11, `LEG-RMA-028` |
| `UrgenciaPorThreshold` | RN-12, `LEG-RMA-029` |

## Pendência herdada, não decidida aqui

RN-11 (`[DÚVIDA]` se existe equivalente exato em TEMA V1) fica para a Fase 8 resolver —
o enum de domínio `ClasseDeAlerta` é único para os dois temas; o que pode variar é só a
apresentação (classe CSS/Blade), não a regra.
