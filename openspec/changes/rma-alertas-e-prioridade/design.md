# Design — Alertas e prioridade

## Schema (incremental sobre `rmas`)

```
rmas (colunas novas desta fase)
  prioridade            string nullable   -- cast Prioridade (Baixa/Media/Alta)
  marcarestoque         boolean default true
  -- origem já existe (Fase 3), passa a ter cast Origem
  nfcompra              string nullable
  nfcompra_emissao      date nullable
  nfcompra_chave        string nullable
  nfvenda               string nullable
  nfvenda_emissao       date nullable
  nfvenda_chave         string nullable
  lancadoretorno        string nullable   -- cast StatusDeLancamento
```

Só os blocos de NF `compra`/`venda` (usados por RN-02/05/06/09) — `nfremessa`/
`nfretorno` ficam para Fase 6/7 se alguma regra vier a precisar; não copiados "por
completude" (violaria o princípio de não criar coluna antes da regra que a usa).

## Enums novos

```php
enum Origem: string
{
    case Unknown = 'Unknown';
    case Loja = 'Loja';
    case Casa = 'Casa';
    case Cliente = 'Cliente';
    case Licitacao = 'Licitação';
    case Leilao = 'Leilão';
    case MercadoLivre = 'Mercado Livre';
    case Credito = 'Credito';
    case Ac = 'AC';
    case Rolo = 'Rolo';
}

enum Prioridade
{
    case Baixa;
    case Media;
    case Alta;
    // Sem case Urgente — RN-08: valor usado em ~14 arquivos de destaque visual,
    // mas inexistente no <select> real (resíduo de domínio anterior de 4 níveis).
    // Reproduzir um case morto que nenhum formulário grava violaria "sem
    // string mágica sem significado" tanto quanto reproduzir um bug.

    public function alta(): bool { return $this === self::Alta; }
}

enum StatusDeLancamento: string
{
    case Pendente = 'pendente';
    case NfDevolucao = 'nf_devolucao';
    case SemMovimentacao = 'sem_movimentacao';
    case Nao = 'nao';
    case Sim = 'sim';
}

enum ClasseDeAlerta
{
    case Inconformidade;
    case Urgente;
    case SemGarantia;
    case Neutro; // equivalente a TrZebrada — sem significado de alerta
}
```

`Origem::normalizar()` (RN-13/RN-14, Fase 3) passa a devolver este enum em vez de
string solta — o domínio completo já está fixado aqui.

## As 10 regras — filtro no SQL, não em PHP (decisão central desta fase)

```php
final class RecebidosSemEncaminhar30Dias
{
    public function listar(): Collection
    {
        return Rma::query()
            ->where('status', Status::Recebido)
            ->where('recebido_em', '<', now()->subDays(30))
            ->get();
        // SELECT já filtra por data — sem a classe de bug "num_rows mentiroso"
        // do legado (SELECT bruto + filtro PHP pós-query).
    }
}
```

Mesmo padrão para as outras 9, cada uma em seu próprio arquivo:

- `NaoVaiDarGarantia`: `status IN (Entrada,Recebido)` AND (`nfvenda_emissao` não nula
  AND `< hoje-365d`) OR (`fabricante.nome = 'MARKVISION'` AND (`fornecedor.nome =
  'Receita'` OR (`nfcompra_emissao` não nula AND `< hoje-365d`))) — join com
  `fabricantes`/`fornecedores` (FK real desde a Fase 2/3, não comparação de string).
- `NfRetornoPendenteDeLancar`: `status=Concluido AND lancadoretorno=Pendente`.
- `ProtocoloAbertoNaoEncaminhado`: `status=Recebido AND protocolo IS NOT NULL AND
  protocolo != ''`.
- `GarantiaFornecedorExpirada`: `status IN (Entrada,Recebido) AND nfcompra_emissao <
  hoje-365d`.
- `GarantiaFornecedorExpirandoEm30Dias`: mesma base, janela `[hoje-365d, hoje-336d]`
  (dias restantes = `365 - diasDecorridos`, exibido para o usuário).
- `PrazoDestinatarioEstourado`: `status=Encaminhado AND encaminhado_em < hoje-30d`.
- `PrioridadeAltaSemEncaminhar`: `status IN (Entrada,Recebido) AND prioridade=Alta`.
- `SemNotaFiscal`: `status=Recebido AND (nfcompra IS NULL OR nfcompra='') AND
  (nfvenda IS NULL OR nfvenda='')`.
- `SemNumeroDeSerie`: `status=Recebido AND (sn IS NULL OR sn='')`.

Todos os limites de data usam `>`/`<` estrito (não `>=`/`<=`) — mesmo operador
confirmado no legado (`Diferenca_de_dias(...) > 30`), evitando divergência de 1 dia na
fronteira.

## `Rma::classeDeAlerta()` (RN-11)

```php
public function classeDeAlerta(): ClasseDeAlerta
{
    return match (true) {
        $this->solucao === Solucao::SemGarantia => ClasseDeAlerta::Inconformidade,
        $this->prioridade === Prioridade::Alta => ClasseDeAlerta::Inconformidade,
        $this->origemEhTerceiroForaDoPrazo() => ClasseDeAlerta::Inconformidade,
        $this->marcarestoque === false
            && in_array($this->origem, [Origem::Cliente, Origem::Licitacao], true)
            => ClasseDeAlerta::Inconformidade,
        default => ClasseDeAlerta::Neutro,
    };
}

private function origemEhTerceiroForaDoPrazo(): bool
{
    return $this->origem === Origem::Cliente
        && $this->marcarestoque === false
        && $this->createdAt->addDays(30)->isPast();
}
```

Ordem de avaliação preserva a precedência confirmada em RN-11 (primeiro critério que
bate vence) — **sem** o critério morto `prioridade=='urgente'` (não existe mais, ver
`Prioridade` acima).

## `UrgenciaPorThreshold` (RN-12)

```php
final class UrgenciaPorThreshold
{
    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido, Status::Encaminhado])
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereIn('origem', [Origem::Cliente, Origem::Licitacao])
                       ->where('marcarestoque', false)
                       ->where('valor', '>', 75.00)
                       ->where(fn ($q3) => $q3->whereColumn('created_at', '<', now())); // prazo
                })->orWhere('prioridade', Prioridade::Alta);
            })
            ->get();
    }
}
```

`prazo` **não é coluna persistida** — calculado como `created_at->addDays(30)` (método
`Rma::prazoLegal(): CarbonImmutable`), resultado idêntico ao legado sem denormalizar.

## Testes

- 10 arquivos `tests/Unit/Rma/Alertas/*Test.php` — um por regra, cada um com: caso que
  dispara, caso que não dispara, caso limite (exatamente no limite de dias — confirma
  operador estrito `>`, não `>=`).
- `ClasseDeAlertaTest` — os 4 critérios de RN-11, na ordem certa (garante que o
  primeiro critério que bate vence, não o último).
- `UrgenciaPorThresholdTest` — valor exatamente R$75 (não dispara, é `>`, não `>=`),
  R$75,01 dispara; `prioridade=Alta` dispara independente de valor.
