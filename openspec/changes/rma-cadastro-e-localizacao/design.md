# Design — Rma núcleo

## Schema desta fase (incremental — não a tabela `rmas` inteira)

```
rmas
  id                bigint pk
  descricao         string
  fabricante_id     bigint fk -> fabricantes, nullable
  fornecedor_id     bigint fk -> fornecedores, nullable   -- ajuste da revisão, ver
                                  docs/arquitetura/revisao-fases-1-2-3.md: ausente do
                                  desenho original; bd.fornecedor é campo de "Partes" do
                                  mesmo grupo de fabricante/cliente
  modelo            string nullable
  sn                string nullable
  os                string nullable
  origem            string    -- já normalizado por RN-13/RN-14 na gravação (ver abaixo);
                                  enum de domínio fica para Fase 4/5, quando o conjunto
                                  fechado de valores usado pelas regras de alerta for
                                  fixado por completo
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
        public readonly ?int $fornecedorId,
        public readonly ?string $modelo,
        public readonly ?string $sn,
        public readonly ?string $os,
        public readonly ?string $origem,
        public readonly ?string $empresa,
        public readonly ?int $clienteId,
        public readonly string $defeito,
        public readonly ?string $observacao,
    ) {}

    /**
     * RN-13 (HGST→Hitachi) + RN-14 (cascata de origem) — normalização confirmada
     * idêntica nos dois temas do legado, aplicada na criação e na edição. Método puro
     * (sem side effect), chamado por CriarRma/EditarRma antes de persistir.
     */
    public function comNormalizacaoDeGravacao(
        ?string $nomeFabricante,
        ?string $nomeFornecedor,
        ?string $nomeCliente,
        ?string $nomeEmpresa,
    ): self {
        $fabricante = $nomeFabricante === 'HGST' ? 'Hitachi' : $nomeFabricante;

        $origem = match (true) {
            $this->origem === $fabricante => 'Unknown',
            $this->origem === $nomeFornecedor => 'Unknown',
            $this->origem === $nomeCliente => 'Cliente',
            $this->origem === $nomeEmpresa => 'Cliente',
            in_array($this->origem, ['CELLSYSTEM', 'Cellsystem'], true) => 'Loja',
            in_array($this->origem, ['Leilao', 'Receita Federal', 'Receita'], true) => 'Leilão',
            default => $this->origem,
        };

        return new self(
            $this->id, $this->descricao, $this->fabricanteId, $this->fornecedorId,
            $this->modelo, $this->sn, $this->os, $origem, $this->empresa,
            $this->clienteId, $this->defeito, $this->observacao,
        );
    }
}
```

Diferença deliberada sobre o legado: a cascata é `match` sequencial e puro sobre valores
já resolvidos (nome do fabricante/fornecedor/cliente/empresa, buscados via FK — Fase 2),
não `str_replace` sobre uma string solta — corrige o bug confirmado de `$fornecedor` não
inicializado (`regras-negocio-rma-legado.md` RN-14) porque aqui os parâmetros são
explícitos, não variáveis implícitas do escopo do arquivo.

Sem métodos de negócio ainda nesta fase (não há regra além de existir). As fases
seguintes que adicionam `status`/`solucao` também adicionam os métodos de transição
aqui — este objeto cresce incrementalmente junto com o schema.

## `Dominio\RepositorioDeRmas` (interface)

```php
interface RepositorioDeRmas
{
    public function criar(Rma $rma): Rma;         // devolve com id preenchido

    // Ausente do snippet original — acrescentado durante a implementação real desta
    // fase porque EditarRma (ajuste da revisão, ver docs/arquitetura/
    // revisao-fases-1-2-3.md) precisava de um método de atualização sem furar a
    // fronteira de domínio tocando o Eloquent model diretamente. A Fase 4 já assume
    // este método existente (ver design.md de rma-ciclo-de-vida).
    public function atualizar(Rma $rma): Rma;

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
- `EditarRma`: atualiza campos do núcleo, reaplica normalização RN-13/RN-14.
- `BuscarRmas`: os 3 tipos de critério, caso vazio.
- `VerDetalheDoRma`: existente, inexistente (404).
- `CriterioDeBusca` (unit, sem banco): named constructors devolvem tipo/valor corretos.
- `RmaTest::comNormalizacaoDeGravacao` (unit, sem banco): fabricante "HGST"→"Hitachi";
  origem igual ao fabricante/fornecedor→"Unknown"; origem igual ao cliente/empresa→
  "Cliente"; "Cellsystem"→"Loja"; "Receita"/"Receita Federal"/"Leilao"→"Leilão"; valor
  fora do domínio conhecido permanece inalterado (sem bug de variável não
  inicializada — prova de que o achado RN-14 foi corrigido, não herdado).
