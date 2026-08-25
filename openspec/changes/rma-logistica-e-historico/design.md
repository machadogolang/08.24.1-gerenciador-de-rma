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

Listener (não chamado diretamente por Controllers) — assina eventos de domínio.
**Ajuste de revisão (2026-08-25):** só `RmaConcluido` existe de verdade (dispatado por
`ConcluirRma`, Fase 4). Os outros 7 eventos (`RmaCriado`, `RmaEditado`, `RmaRecebido`,
`RmaEncaminhado`, `RmaArquivado`, `RmaRevertido`, `SolucaoRegistrada`) **precisam ser
criados nesta fase** em `app/Rma/Dominio/Eventos/` e dispatados a partir dos casos de
uso já existentes das Fases 3/4 (`CriarRma`, `EditarRma`, `ReceberRma`,
`EncaminharRma`, `ArquivarRma`, `ReverterRmaParaEntrada`, `RegistrarSolucao`) — mesmo
padrão de `ConcluirRma::dispatch()`. É uma extensão aditiva (adicionar uma linha de
`::dispatch()` ao final de cada caso de uso já implementado), não uma mudança de
comportamento — os testes de Feature das Fases 3/4 continuam válidos sem alteração.
Centraliza o que no legado é chamado manualmente em cada arquivo
(`registra_modificacao()`) — um único ponto de verdade, mesma cobertura.

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

## `EnviarNotificacaoDeTentativaNaoPermitida` (`LEG-RMA-045`, `naopermitido()`)

**Detalhe ausente do desenho original, acrescentado nesta revisão.** Dispara quando um
usuário sem `Papel::podeGravar()` tenta editar/gravar um RMA — equivalente a
`naopermitido()` do legado. Mecanismo: `RmaPolicy::update()` (Fase 3) já decide
`false`/`true`; em vez de criar um evento novo disparado de dentro da Policy (política
não deveria ter efeito colateral de notificação — mistura autorização com
side-effect), o listener assina o evento nativo do Laravel
`Illuminate\Auth\Access\AuthorizationException` não é prático de ouvir globalmente sem
acoplar a um middleware — a abordagem mais simples e testável é o próprio
`RmaPolicy::update()` disparar um evento de domínio novo
(`App\Rma\Dominio\Eventos\TentativaDeGravacaoNaoPermitida`) explicitamente antes de
devolver `false`, com `$ator`/`$rma` capturados. Autorização continua decidindo só
`true`/`false`; o evento é responsabilidade explícita e testável, não um side-effect
escondido — `RmaPolicy::update()` já é claramente documentado como o ponto de auditoria
de acesso negado, não uma classe pura sem I/O.

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
