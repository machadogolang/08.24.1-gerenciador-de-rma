# Design — Identidade

## Schema

```
users (estende a migration padrão do Laravel)
  ...campos padrão (name, email, password, etc.)
  papel                string   -- nome do case do enum Papel (Bloqueado/Leitura/
                                    Operador/Supervisor/SuperAdministrador)
  tema_preferido        string   -- nome do case do enum TemaPreferido (V1/V2)

tentativas_de_acesso
  id                    bigint pk
  user_id               bigint nullable fk -> users (nulo se e-mail não existe)
  email_informado        string
  ip                     string
  user_agent             string nullable
  resultado              string   -- nome do case de um enum ResultadoDeAcesso
                                     (Permitido/Negado/Bloqueado)
  timestamps
```

Nenhuma coluna usa `int` para representar o papel ou o resultado — sempre o nome do
case do enum (Eloquent faz o cast automático de enum puro por `->name` quando declarado
em `casts()`). Ver `INV-RMA-05-arquitetura-proposta.md` §1.1 para o porquê.

## `App\Identidade\Dominio\Papel`

```php
enum Papel
{
    case Bloqueado;
    case Leitura;
    case Operador;
    case Supervisor;
    case SuperAdministrador;

    public function podeAutenticar(): bool
    {
        return $this !== self::Bloqueado;
    }

    public function podeGravar(): bool
    {
        return match ($this) {
            self::Bloqueado, self::Leitura => false,
            default => true,
        };
    }

    public function podeGerenciarUsuarios(): bool
    {
        return match ($this) {
            self::Supervisor, self::SuperAdministrador => true,
            default => false,
        };
    }

    public function ocultoDaListagemDeUsuarios(): bool
    {
        return $this === self::SuperAdministrador;
    }
}
```

Justificativa campo a campo em `regras-negocio-rma-legado.md` §6 (permissões) — os 5
papéis e as 4 regras acima (`podeAutenticar`/`podeGravar`/`podeGerenciarUsuarios`/
`ocultoDaListagemDeUsuarios`) são exatamente as 4 guardas confirmadas idênticas nos
dois temas do legado. Não implementar mais nenhuma regra de permissão além destas 4 —
não há evidência de mais nenhuma no legado.

## `App\Identidade\Dominio\TemaPreferido`

```php
enum TemaPreferido: string
{
    case V1 = 'v1';
    case V2 = 'v2';

    public function alternar(): self
    {
        return match ($this) {
            self::V1 => self::V2,
            self::V2 => self::V1,
        };
    }
}
```

Backing `string` aqui é aceitável (não é "número mágico" — são só 2 valores, sem
ordem/precedência a esconder, e o valor precisa aparecer em rota/URL eventualmente na
Fase 8). Diferente de `Papel`, que tem semântica de ordem que não deve vazar.

## `AutenticarUsuario` (caso de uso)

Fluxo, espelhando a regra confirmada do legado (`inc/signin.php`/`pp/senha.php`, ambos
os temas):

1. Busca `User` por e-mail.
2. Se não existe → falha genérica (não revelar se e-mail existe — **correção de
   segurança sobre o legado**, que tinha enumeração de usuário confirmada; não é
   comportamento a preservar, é bug de segurança documentado em
   `regras-negocio-rma-legado.md` a não copiar).
3. Se existe e `papel->podeAutenticar() === false` → nega, registra
   `TentativaDeAcesso` com resultado `Bloqueado`, **antes** de checar a senha (ordem
   confirmada no legado: bloqueio é checado antes da senha).
4. Verifica senha via `Hash::check` (bcrypt/argon2 — nunca SHA1).
5. Senha errada → nega, registra `TentativaDeAcesso` com resultado `Negado`.
6. Sucesso → `Auth::login($user)`, registra `TentativaDeAcesso` com resultado
   `Permitido`, devolve `$user->tema_preferido` para o controller decidir o redirect.

## `AlternarTemaPreferido`

```php
final class AlternarTemaPreferido
{
    public function alternar(User $usuario): TemaPreferido
    {
        $novo = $usuario->tema_preferido->alternar();
        $usuario->update(['tema_preferido' => $novo]);
        return $novo;
    }
}
```

Sem interface de repositório — é um único `update()` em cima do Eloquent model já
carregado; não há regra de negócio adicional a esconder atrás de uma porta.

## Testes (ver `proposal.md` para lista completa dos arquivos)

- Login válido → autentica, registra `Permitido`.
- Login com papel `Bloqueado` → nega antes de checar senha, registra `Bloqueado`.
- Senha errada → nega, registra `Negado`.
- E-mail inexistente → nega, mensagem genérica (não enumera).
- Cada um dos 4 métodos do enum `Papel` testado isoladamente (unit test puro, sem
  banco).
- `AlternarTemaPreferido` — alterna e persiste; login subsequente usa o novo valor.
