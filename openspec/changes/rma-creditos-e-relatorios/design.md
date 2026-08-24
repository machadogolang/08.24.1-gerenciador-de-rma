# Design — Créditos e relatórios

## Schema (incremental sobre `rmas`)

```
rmas (coluna nova desta fase)
  credito_disponivel   boolean default false
```

## `MarcarCreditoDisponivel`

```php
final class MarcarCreditoDisponivel
{
    public function marcar(User $ator, Rma $rma): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->solucao === Solucao::GeradoCredito, 422);
        // grava credito_disponivel = true
    }
}
```

Sem transição automática `PendenteCredito`→`GeradoCredito` — o legado também não
automatiza (controle manual em duas camadas independentes, confirmado em
`modelo-dominio-rma-legado.md`); `EVO-AUT-002` já registra a automação como melhoria
futura, não implementada agora.

`AguardandoCredito` (lista `solucao=PendenteCredito`) fica em
`app/Rma/Aplicacao/Alertas/` — mesma família de consulta de leitura da Fase 5, reforça
que crédito não é módulo próprio.

## Relatórios (`app/Rma/Aplicacao/Relatorios/`)

- `RelatorioCreditosDisponiveis` — `credito_disponivel = true`.
- `RelatorioProdutosEmEstoqueParaContagem` — `marcarestoque = true`, filtro de status
  configurável pelo usuário (Form Request), não hardcoded.
- `RelatorioProdutosEncaminhados` — `status = Encaminhado`, intervalo de datas via Form
  Request (`data_inicio`/`data_fim` obrigatórios) — **substitui o intervalo hardcoded
  para 2014 do legado**, que é bug de manutenção sem nenhuma RN documentando "2014"
  como valor de negócio intencional.

## Testes

- `MarcarCreditoDisponivelTest` — exige `solucao=GeradoCredito`, nega em outros casos.
- `RelatorioCreditosDisponiveisTest`, `RelatorioProdutosEmEstoqueParaContagemTest`,
  `RelatorioProdutosEncaminhadosTest` (intervalo real, não 2014 hardcoded).

`ConsolidarFretePorCidade` e `BoletinsRelacionados` (RN-16, `LEG-RMA-040`/`041`) ficam
em `openspec/changes/rma-logistica-e-historico/design.md` (Fase 7) — ver nota em
`proposal.md`.
