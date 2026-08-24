# Design — Rma núcleo

## Schema desta fase (incremental — não a tabela `rmas` inteira)

```
rmas
  id                bigint pk
  descricao         string
  fabricante_id     bigint fk -> fabricantes, nullable
  modelo            string nullable
  sn                string nullable
  os                string nullable
  origem            string    -- enum de domínio na Fase 4/5 quando a regra existir;
                                  nesta fase é só um campo informativo
  empresa           string nullable   -- ver nota abaixo
  cliente_id        bigint fk -> clientes, nullable
  defeito            string
  observacao          text nullable
  timestamps
```

**Nota sobre `empresa`:** no legado é o embrião de multiempresa (`EVO-SAAS-001`, Trilha
B) — nesta baseline fica só como campo texto informativo, sem nenhuma regra de negócio
nem isolamento associado. Não implementar tenancy agora.

## `Dominio\Rma` (objeto puro)

```php
final class Rma
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $descricao,
        public readonly ?int $fabricanteId,
        public readonly ?string $modelo,
        public readonly ?string $sn,
        public readonly ?string $os,
        public readonly ?string $origem,
        public readonly ?string $empresa,
        public readonly ?int $clienteId,
        public readonly string $defeito,
        public readonly ?string $observacao,
    ) {}
}
```

Sem métodos de negócio ainda nesta fase (não há regra além de existir). As fases
seguintes que adicionam `status`/`solucao` também adicionam os métodos de transição
aqui — este objeto cresce incrementalmente junto com o schema.

## `Dominio\RepositorioDeRmas` (interface)

```php
interface RepositorioDeRmas
{
    public function criar(Rma $rma): Rma;         // devolve com id preenchido
    public function buscarPorId(int $id): ?Rma;
    /** @return Rma[] */
    public function buscar(CriterioDeBusca $criterio): array;
}
```

## `Dominio\CriterioDeBusca`

```php
final class CriterioDeBusca
{
    private function __construct(
        private readonly string $tipo,   // 'texto' | 'nota_fiscal' | 'serial'
        private readonly string $valor,
    ) {}

    public static function porTexto(string $valor): self { return new self('texto', $valor); }
    public static function porNotaFiscal(string $valor): self { return new self('nota_fiscal', $valor); }
    public static function porSerial(string $valor): self { return new self('serial', $valor); }

    public function tipo(): string { return $this->tipo; }
    public function valor(): string { return $this->valor; }
}
```

Substitui o padrão do legado (`$_GET['campo'] == 'TUDO'/'NF'/'SNPNSNID'` — string
comparada por igualdade, decidida na camada de apresentação) por named constructors —
mesmo princípio já fixado para `Papel`: nenhuma string mágica decidindo comportamento
fora de um tipo de domínio nomeado.

## `Infraestrutura\RmasEmBanco`

Implementa `RepositorioDeRmas` usando o Eloquent model interno `app/Models/Rma.php`
(mapeamento simples ida/volta entre `Dominio\Rma` e o Eloquent model — não expor o
Eloquent model fora desta classe).

## Testes

- `CriarRma`: cria com cliente novo (dispara `EncontrarOuCriarCliente`), cria com
  cliente já existente (reaproveita).
- `BuscarRmas`: os 3 tipos de critério, caso vazio.
- `VerDetalheDoRma`: existente, inexistente (404).
- `CriterioDeBusca` (unit, sem banco): named constructors devolvem tipo/valor corretos.
