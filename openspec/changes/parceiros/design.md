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
  uf                 string(2) nullable
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
