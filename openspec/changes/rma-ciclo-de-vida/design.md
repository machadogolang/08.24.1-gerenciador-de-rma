# Design — Ciclo de vida do RMA

## Schema (incremental sobre `rmas`, criado na Fase 3)

```
rmas (colunas novas desta fase)
  status               string    -- cast Status (sem backing numérico)
  recebido_em          datetime nullable
  encaminhado_em       datetime nullable
  concluido_em         datetime nullable
  arquivado_em         datetime nullable
  protocolo            string nullable
  solucao              string nullable   -- cast Solucao (backed string)
  snretorno            string nullable
  destinatario_type    string nullable   -- relação polimórfica Eloquent
  destinatario_id      bigint nullable
```

`entrada` do legado não vira coluna — é `created_at` (o RMA já nasce em
`status=Entrada`, Fase 3).

## `App\Rma\Dominio\Status`

```php
enum Status
{
    case Entrada;
    case Recebido;
    case Encaminhado;
    case Concluido;
    case Arquivado;

    public function podeReceber(): bool { return $this === self::Entrada; }
    public function podeEncaminhar(): bool { return $this === self::Recebido; }
    public function podeConcluir(): bool { return $this === self::Encaminhado; }

    public function podeArquivar(): bool
    {
        return match ($this) {
            self::Entrada, self::Recebido, self::Encaminhado => true,
            default => false,
        };
    }

    public function podeReverterParaEntrada(): bool
    {
        return match ($this) {
            self::Recebido, self::Encaminhado => true,
            default => false,
        };
    }
}
```

Sem case `Retornou` (`LEG-RMA-016`, código morto em ambos os temas — rota existe no
`.htaccess`, nenhuma transição jamais grava esse valor).

## `App\Rma\Dominio\Solucao`

16 valores confirmados por leitura direta do `<select name="solucao">` real de
`15.8.1/page/rma.php:578-595` (arquivo ISO-8859-1, decodificado para conferência nesta
revisão — não copiado de documentação secundária):

```php
enum Solucao: string
{
    case Reparo = 'REPARO';
    case TrocaDoProduto = 'TROCA DO PRODUTO';
    case TrocaDePecaInterna = 'TROCA DE PECA INTERNA';
    case PendenteCredito = 'PENDENTE CREDITO';
    case GeradoCredito = 'GERADO CREDITO';
    case DevolucaoDoProduto = 'DEVOLUCAO DO PRODUTO';
    case ReembolsoDoDinheiro = 'REEMBOLSO DO DINHEIRO';
    case OrcamentoPago = 'ORCAMENTO PAGO';
    case OrcamentoPendente = 'ORCAMENTO PENDENTE';
    case OrcamentoNegado = 'ORCAMENTO NEGADO';
    case ReparoPeloRma = 'REPARO PELO RMA';
    case CasoSolucionado = 'CASO SOLUCIONADO';
    case TestadoTudoOk = 'TESTADO TUDO OK';
    case Procon = 'PROCON';
    case DescritoNaObservacao = 'DESCRITO NA OBSERVACAO';
    case SemGarantia = 'SEM GARANTIA';

    public function implicaMesmoAparelhoDeRetorno(): bool
    {
        return match ($this) {
            self::TrocaDePecaInterna, self::Reparo, self::OrcamentoPago,
            self::OrcamentoNegado, self::ReparoPeloRma, self::TestadoTudoOk => true,
            default => false,
        };
    }
}
```

**Nota de rastreabilidade:** os documentos anteriores citavam "17 valores"
(`inventario-banco-rma-v2.md`); a leitura direta do form encontrou 16 valores nomeados
mais uma opção vazia inicial — a diferença provavelmente é a opção vazia sendo contada
como um 17º "estado". Se aparecer evidência de um 17º valor nomeado real durante a
implementação, adicionar então — não foi inventado aqui.

## `App\Rma\Dominio\Rma` (estendido — Fase 3 → aqui)

Novas propriedades readonly: `status`, `recebidoEm`, `encaminhadoEm`, `concluidoEm`,
`arquivadoEm`, `protocolo`, `solucao`, `snretorno`, `destinatario` (objeto polimórfico).
Novo método:

```php
public function comSnretornoAutoPreenchido(): self
{
    if ($this->snretorno !== null && $this->snretorno !== '') {
        return $this; // já preenchido, não sobrescreve
    }
    if ($this->solucao?->implicaMesmoAparelhoDeRetorno() !== true) {
        return $this; // classe "troca", fica em branco para digitação manual
    }
    return new self(..., snretorno: $this->sn, ...); // demais campos inalterados
}
```

RN-15: só copia `sn`→`snretorno` se estiver vazio E a solução implicar mesmo aparelho —
ausente em TEMA V1 (regra nova nesta fase, sem regressão a corrigir).

## Casos de uso (`app/Rma/Aplicacao/`)

```php
final class ReceberRma
{
    public function receber(User $ator, Rma $rma): Rma
    {
        abort_unless($ator->papel->podeGravar(), 403);
        abort_unless($rma->status->podeReceber(), 422);
        // grava recebido_em = now(), status = Recebido
    }
}
```

`EncaminharRma` exige `destinatario` preenchido antes de aceitar (regra que no legado é
só validação JS — vira validação de domínio real). `ConcluirRma` exige `solucao`
preenchida, chama `comSnretornoAutoPreenchido()`, dispara evento `RmaConcluido`.
`ArquivarRma` reproduz `15.8.1/banco.php::arquivar()` (TEMA V2 — ver `proposal.md`),
exige `Papel::podeGerenciarUsuarios()` (**[INFERIDO]**, mesma incerteza de
`inventario-funcional-rma-v2.md`, não resolvida por falta de evidência adicional).

```php
final class ReverterRmaParaEntrada
{
    public function reverter(User $ator, Rma $rma): Rma
    {
        abort_unless($rma->status->podeReverterParaEntrada(), 422);
        $mesmoDia = $rma->encaminhadoEm?->isToday() ?? true;
        abort_unless($mesmoDia || $ator->papel->podeReverterAlemDoMesmoDia(), 403);
        // status = Entrada, recebido_em = null, encaminhado_em = null
    }
}
```

`RegistrarSolucao` atualiza `solucao` independente de transição de status (o legado
permite editar via `salvar_rma.php` a qualquer momento) e também aplica
`comSnretornoAutoPreenchido()`.

## `App\Identidade\Dominio\Papel` (estendido)

```php
public function podeReverterAlemDoMesmoDia(): bool
{
    return $this === self::SuperAdministrador;
}
```

Equivalente a `permissao==4` do legado (LEG-RMA-015) — único nível que reverte fora da
janela de "mesmo dia".

## Testes

- `ReceberRmaTest`, `EncaminharRmaTest` (com/sem destinatário),
  `ConcluirRmaTest` (com/sem solução; `snretorno` auto-preenchido nos 6 valores de
  `implicaMesmoAparelhoDeRetorno()`, em branco nos demais 10).
- `ArquivarRmaTest` — **prova de que segue TEMA V2**: o teste cobre exatamente o
  cenário que causaria `Fatal Error` em TEMA V1 (arquivar um RMA `Recebido`) e espera
  sucesso, não exceção.
- `ReverterRmaParaEntradaTest` — mesmo dia permite para qualquer papel com
  `podeGravar()`; dia seguinte nega, exceto `SuperAdministrador`.
- `RegistrarSolucaoTest`.
- `StatusTest`, `SolucaoTest` (unit, sem banco) — os métodos dos dois enums.
