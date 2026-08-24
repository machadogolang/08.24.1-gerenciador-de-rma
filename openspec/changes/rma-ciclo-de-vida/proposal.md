# Proposal — Ciclo de vida do RMA (receber, encaminhar, concluir, arquivar, reverter)

Fase 4 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §9).

## Por quê

O núcleo do RMA (Fase 3) existe, mas um RMA sem transição de estado é só um cadastro —
o valor real do produto é acompanhar o item pelo fluxo logístico. As 5 transições
(`LEG-RMA-011` a `015`) e o registro de solução (`LEG-RMA-017`) são a máquina de estados
central do produto, confirmada idêntica em regra nos dois temas (com uma exceção
resolvida nesta fase: `arquivar`).

## O que entra

- Enum `Status` (`Entrada`/`Recebido`/`Encaminhado`/`Concluido`/`Arquivado`, sem
  `Retornou` — código morto, `LEG-RMA-016`, `NÃO RECONSTRUIR`), sem backing numérico,
  métodos de transição nomeados.
- Enum `Solucao` (16 valores confirmados por leitura direta de
  `15.8.1/page/rma.php:578-595`, backed string), com método
  `implicaMesmoAparelhoDeRetorno()` (RN-15).
- Colunas de data por transição (`recebido_em`, `encaminhado_em`, `concluido_em`,
  `arquivado_em`) — não um `updated_at` genérico, porque a Fase 5 precisa da data de
  cada transição individualmente (`Diferenca_de_dias` do legado).
- `destinatario` como relação polimórfica Eloquent (`AssistenciaTecnica`/`Fornecedor`/
  `Fabricante`) — substitui a cascata de resolução por nome do legado.
- Casos de uso: `ReceberRma`, `EncaminharRma`, `ConcluirRma`, `ArquivarRma`,
  `ReverterRmaParaEntrada`, `RegistrarSolucao`.
- Novo método em `Papel` (Fase 1, estendido): `podeReverterAlemDoMesmoDia()`.

## O que não entra

- As 10 regras de alerta e a classificação visual (Fase 5) — leem os campos criados
  aqui, mas são consultas, não transições.
- NF, `lancadoretorno`, `marcarestoque`, `prioridade` (Fase 5/6 — nenhuma transição
  desta fase os usa).
- Envio real do e-mail de conclusão (Fase 7 assina o evento `RmaConcluido` disparado
  aqui).
- Fidelidade visual (Fase 8).

## Decisão registrada — `ArquivarRma` usa TEMA V2 como especificação

`LEG-RMA-014` já registrava TEMA V1 como quebrado. Esta revisão confirmou a causa lendo
o código-fonte: `14.6.1/post/arquivar.php` instancia `new controle()`, mas
`14.6.1/banco.oo.php` só declara a classe `autenticacao` (linha 24) — `controle` não
existe, `Fatal Error` incondicional. `ArquivarRma` reproduz `15.8.1/banco.php::arquivar()`
(TEMA V2, funcional).

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `ReceberRma` | `LEG-RMA-011` |
| `EncaminharRma` | `LEG-RMA-012` |
| `ConcluirRma` | `LEG-RMA-013` |
| `ArquivarRma` | `LEG-RMA-014` — **TEMA V2 como especificação** (TEMA V1 confirmado quebrado nesta revisão, `Fatal Error` incondicional) |
| `ReverterRmaParaEntrada` | `LEG-RMA-015` |
| `RegistrarSolucao` | `LEG-RMA-017` |
| `Solucao::implicaMesmoAparelhoDeRetorno()` | RN-15, `LEG-RMA-047` (ausente em TEMA V1 — funcionalidade nova nesta fase, sem equivalente lá) |
| `Status` (sem case `Retornou`) | `LEG-RMA-016` — código morto em ambos, `NÃO RECONSTRUIR` (`paridade-v2-v3.md`) |

## Pendência herdada, não decidida aqui

`Status::podeArquivar()` (quais status de origem permitem arquivar) é **[INFERIDO]** —
o legado não documenta explicitamente a restrição; a implementação assume
`Entrada`/`Recebido`/`Encaminhado` (não `Concluido`, não já `Arquivado`), coerente com
"pausa reabrível" (parecer §6), mas sem confirmação direta de código. Revisar se
surgir evidência em contrário durante a implementação.
