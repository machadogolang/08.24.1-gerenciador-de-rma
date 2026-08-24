# Design — Auditoria

## Schema

```
modificacoes_de_rma
  id            bigint pk
  rma_id        bigint fk -> rmas          -- legado: numero, sem constraint
  user_id       bigint fk -> users          -- legado: email, sem constraint
  acao          string     -- cast AcaoDeModificacao
  ip            string
  user_agent    string nullable
  estado_apos   json       -- campos-chave após a modificação (equivalente ao
                               snapshot do legado: fabricante, modelo, sn, descricao...)
  timestamps
```

## `App\Rma\Dominio\AcaoDeModificacao`

```php
enum AcaoDeModificacao
{
    case Criacao;
    case Edicao;
    case Receber;
    case Encaminhar;
    case Concluir;
    case Arquivar;
    case Reverter;
    case RegistrarSolucao;
}
```

## `RegistrarModificacaoDeRma`

Listener (não chamado diretamente por Controllers) — assina os eventos de domínio já
disparados pelas Fases 3/4 (`RmaCriado`, `RmaEditado`, `RmaRecebido`, `RmaEncaminhado`,
`RmaConcluido`, `RmaArquivado`, `RmaRevertido`, `SolucaoRegistrada`). Centraliza o que
no legado é chamado manualmente em cada arquivo (`registra_modificacao()`) — um único
ponto de verdade, mesma cobertura.

```php
final class RegistrarModificacaoDeRma
{
    public function handle(object $evento): void
    {
        ModificacaoDeRma::create([
            'rma_id' => $evento->rma->id,
            'user_id' => $evento->ator->id,
            'acao' => $this->acaoParaEvento($evento),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'estado_apos' => $evento->rma->paraSnapshot(),
        ]);
    }
}
```

## `EnviarNotificacaoDeConclusao`

```php
final class EnviarNotificacaoDeConclusao
{
    public function handle(RmaConcluido $evento): void
    {
        Mail::to(config('rma.notificacoes.conclusao'))
            ->send(new RmaConcluidoMailable($evento->rma));
    }
}
```

Destinatário via `config('rma.notificacoes.conclusao')` → `.env` — **não** hardcoded
como `ezequiel()` do legado (correção de manutenibilidade invisível ao comportamento
percebido, coerente com os princípios fixos desde o início do projeto).

## `ConsolidarFretePorCidade` (RN-16)

```php
final class ConsolidarFretePorCidade
{
    private const CIDADE = 'PORTO ALEGRE';

    public function listar(): Collection
    {
        return Rma::query()
            ->whereIn('status', [Status::Entrada, Status::Recebido])
            ->where(function ($q) {
                $q->whereHas('fornecedor', fn ($q2) => $q2->where('cidade', self::CIDADE))
                  ->orWhereHas('fabricante', fn ($q2) => $q2->where('cidade', self::CIDADE))
                  ->orWhereHasMorph('destinatario', [AssistenciaTecnica::class],
                      fn ($q2) => $q2->where('cidade', self::CIDADE));
            })
            ->get();
    }
}
```

JOINs reais via relação Eloquent (FK desde a Fase 2/3/4) — sem os aliases mortos
`FOD`/`FAD` do legado (achado de refatoração incompleta, não reproduzido).

## `BoletinsRelacionados`

```php
final class BoletinsRelacionados
{
    public function listar(Rma $rma, int $porPagina = 20): LengthAwarePaginator
    {
        return Rma::query()
            ->where('id', '!=', $rma->id)
            ->where(function ($q) use ($rma) {
                $q->where('destinatario_id', $rma->destinatarioId)
                  ->orWhere('fabricante_id', $rma->fabricanteId)
                  ->orWhere('fornecedor_id', $rma->fornecedorId);
            })
            ->paginate($porPagina);
    }
}
```

Paginado (o legado não tem `LIMIT`, achado de risco de performance já registrado) —
resultado percebido pelo usuário é o mesmo conjunto de dados, só a forma de consumir
muda.

## Testes

- `RegistrarModificacaoDeRmaTest` — dispara em cada um dos 8 valores de
  `AcaoDeModificacao`, `estado_apos` reflete os campos-chave corretos.
- `EnviarNotificacaoDeConclusaoTest` — `Mail::fake()`, destinatário lido de config, não
  hardcoded no código.
- `EnviarNotificacaoDeTentativaNaoPermitidaTest`.
- `HistoricoDeModificacaoTest` — exige `Papel::podeGerenciarUsuarios()` (mesma regra
  confirmada de `subp/logs_de_modificacao.php`).
- `ConsolidarFretePorCidadeTest` — JOINs corretos, sem os aliases mortos do legado.
- `BoletinsRelacionadosTest` — paginação, exclui o próprio RMA da lista.
