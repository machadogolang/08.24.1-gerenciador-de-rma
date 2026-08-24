# Design — Parceiros

## Schema (campos herdados do legado, ver `inventario-banco-rma-v2.md`; sem os campos
mortos/redundantes do legado — `observacaoFR`/`observacaoSGV` viram só `observacao`)

```
clientes
  id                 bigint pk
  nome               string
  representante      string nullable
  cpf_cnpj           string nullable
  email              string nullable
  telefone           string nullable
  telefone2          string nullable
  cep                string nullable
  logradouro         string nullable
  numero             string nullable
  complemento        string nullable
  bairro             string nullable
  cidade             string nullable
  uf                 string(2) nullable   -- cast Eloquent para App\Compartilhado\Uf
  observacao         text nullable
  timestamps

fabricantes / fornecedores / assistencias_tecnicas   (schema idêntico entre os 3)
  id                          bigint pk
  nome                        string
  representante               string nullable
  cpf_cnpj                    string nullable
  email                       string nullable
  email_secundario            string nullable
  telefone                    string nullable
  telefone2                   string nullable
  cep, logradouro, numero, complemento, bairro, cidade, uf   (idem cliente)
  www                         string nullable
  frete                       string nullable   -- valor livre no legado, preservado
  cfop                        string nullable
  observacao                  text nullable
  politica_de_garantia        text nullable    -- texto livre, nunca parseado (igual legado)
  timestamps
```

### `App\Compartilhado\Uf` — ajuste da revisão (ver `docs/arquitetura/revisao-fases-1-2-3.md`)

O `INV-RMA-05` §3 já registrava `Compartilhado` como o lugar para "value objects sem
dono único (ex.: enum de UF...)", mas o desenho original desta fase usava `uf
string(2) nullable` solto nos 4 models — o mesmo padrão de primitiva-representando-
conceito-fechado que o princípio "sem número mágico" (`INV-RMA-05` §1.1) proíbe (UF é
um conjunto fechado de 27 valores, não texto livre).

```php
enum Uf: string
{
    case AC = 'AC'; case AL = 'AL'; case AP = 'AP'; case AM = 'AM'; case BA = 'BA';
    case CE = 'CE'; case DF = 'DF'; case ES = 'ES'; case GO = 'GO'; case MA = 'MA';
    case MT = 'MT'; case MS = 'MS'; case MG = 'MG'; case PA = 'PA'; case PB = 'PB';
    case PR = 'PR'; case PE = 'PE'; case PI = 'PI'; case RJ = 'RJ'; case RN = 'RN';
    case RS = 'RS'; case RO = 'RO'; case RR = 'RR'; case SC = 'SC'; case SP = 'SP';
    case SE = 'SE'; case TO = 'TO';
}
```

Backing `string` é aceitável aqui pelo mesmo motivo de `TemaPreferido` (Fase 1): sem
ordem/precedência a esconder, só um conjunto fechado de siglas que precisa aparecer
como texto em formulário/URL. Os 4 models (`Cliente`, `Fabricante`, `Fornecedor`,
`AssistenciaTecnica`) usam `casts(): array { return ['uf' => Uf::class]; }` — cast nativo
de enum puro do Eloquent, sem `EmBanco`/repositório (mesmo caso do `TemaPreferido`).
Campo continua nullable — nem todo cadastro do legado tinha UF preenchida.

Não migrar `rgie` (Registro de Inscrição Estadual — achado do legado, baixíssimo uso,
sem nenhuma regra de negócio associada) nem `cfop` como algo mais que texto livre — o
legado nunca valida/usa esses campos além de exibir. Se alguma regra real depender
deles no futuro, adicionar então (não adicionar campo "pra garantir").

## `EncontrarOuCriarCliente`

```php
final class EncontrarOuCriarCliente
{
    public function __construct(private readonly ClienteRepository $clientes) {}
    // Nota: ClienteRepository aqui é literal o model Eloquent Cliente (Fase 2 não usa
    // interface de repositório, ver decisão em INV-RMA-05 §7) — nome ilustrativo,
    // a assinatura real recebe Cliente::query() ou similar diretamente.

    public function encontrarOuCriar(string $nomeDigitado): Cliente
    {
        $nomeNormalizado = trim(preg_replace('/\s+/', ' ', $nomeDigitado));

        return Cliente::query()
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeNormalizado)])
            ->first()
            ?? Cliente::create(['nome' => $nomeNormalizado]);
    }
}
```

Corrige precisamente o achado do legado (`WHERE nome = ?` exato, sem trim/normalização
de espaço, sem case-insensitive) — comportamento percebido pelo usuário não muda (ele
digita um nome, o sistema acha ou cria), só para de duplicar por variação de digitação.

## Testes

- CRUD de cada um dos 4 tipos (criar, editar, listar, apagar — respeitando Policy).
- `EncontrarOuCriarCliente`: nome novo cria; nome exatamente igual reaproveita; nome com
  espaço duplo/maiúscula diferente reaproveita (prova da correção); nome de outro
  cliente não colide.
