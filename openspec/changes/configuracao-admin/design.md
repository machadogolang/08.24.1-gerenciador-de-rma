# Design — Configuração de admin

## Schema — decisão registrada (uma tabela genérica chave-valor)

`INV-RMA-09` §B.6 deixou como pendência: "tabela por configuração vs. tabela genérica
chave/valor". Decisão desta fase: **tabela genérica `configuracoes`**, uma linha por
publicação (não por configuração — cada `publicar()` insere uma linha nova, nunca
`UPDATE`), com a linha mais recente por `chave` sendo a "efetiva".

```
configuracoes
  id                 bigint pk
  chave              string          -- 'notificacao_conclusao' | 'threshold_urgencia' | 'cidade_consolidacao_frete'
  valor              string          -- serializado como string; cada objeto de valor decide o parse (e-mail, decimal, texto)
  publicada_em       datetime
  publicada_por_id   bigint fk -> users.id, nullable (nullable só por robustez de FK; toda publicação real tem ator autenticado)
  publicada_por_nome string          -- snapshot do nome no momento da publicação (não depende de users.id sobreviver/não mudar)
  created_at         datetime
```

**Por que genérica, não uma tabela por configuração:** os 3 candidatos reais de B.2 são
escalares simples (e-mail, decimal, texto curto) — nenhum tem uma segunda dimensão que
justifique colunas próprias (ex. `threshold_urgencia` não tem "moeda" variável,
`cidade_consolidacao_frete` não tem lista de cidades). Uma tabela por configuração
replicaria a mesma migration 3 vezes para o mesmo shape (`valor` + autoria). Se um
candidato futuro (`EVO-DOM-003`, política de garantia por fabricante) precisar de
estrutura real (fabricante × origem × janela), ele **não** entra nesta tabela — ganha
schema próprio dentro do mesmo módulo `Configuracao`, exatamente como `INV-RMA-09` §B.5
já antecipa. A tabela genérica é proporcional só para valor único escalar, não para
regra estruturada.

**Por que "publicar insere, nunca `UPDATE`":** preserva histórico de quem mudou o quê e
quando sem precisar de uma tabela de auditoria separada — mesmo padrão observado no
CONAHOM (nome no plural `ConfiguracoesDeEnvioDeEmail`, `INV-RMA-09` §B.1.2, sugere
coleção/histórico, não uma única linha sobrescrita). "Efetivo" é sempre `MAX(id)`/
`MAX(publicada_em)` por `chave`.

## Objetos de valor (`app/Configuracao/Dominio/`)

Um por configuração, `readonly`, validado no construtor:

```php
final readonly class ConfiguracaoDeNotificacaoDeRma
{
    public function __construct(
        public string $destinatario,       // e-mail válido
        public \DateTimeImmutable $publicadaEm,
        public string $publicadaPorNome,
        public ?int $publicadaPorIdentificador,
    ) {
        if (! filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException("Destinatário de notificação inválido: {$destinatario}");
        }
    }
}

final readonly class ConfiguracaoDeAlertaDeUrgencia
{
    public function __construct(
        public float $thresholdEmReais,    // > 0
        public \DateTimeImmutable $publicadaEm,
        public string $publicadaPorNome,
        public ?int $publicadaPorIdentificador,
    ) {
        if ($thresholdEmReais <= 0) {
            throw new \DomainException('Threshold de urgência deve ser positivo.');
        }
    }
}

final readonly class ConfiguracaoDeConsolidacaoDeFrete
{
    public function __construct(
        public string $cidade,             // não vazia, normalizada uppercase (mesma convenção de RN-16)
        public \DateTimeImmutable $publicadaEm,
        public string $publicadaPorNome,
        public ?int $publicadaPorIdentificador,
    ) {
        if (trim($cidade) === '') {
            throw new \DomainException('Cidade de consolidação não pode ser vazia.');
        }
    }
}
```

`app/Configuracao/Dominio/RepositorioDeConfiguracoes.php` — interface:
`publicar(string $chave, string $valor, User $ator): void`,
`ultimaPublicacao(string $chave): ?array` (linha bruta — quem monta o objeto de valor
tipado é a Aplicação, não o repositório).

## Padrão publicar/efetivo (`app/Configuracao/Aplicacao/`)

Um caso de uso de escrita e um de leitura por configuração — mesmo padrão de granularidade
já usado em `Aplicacao/Alertas/` (Fase 5): uma classe pequena por regra, não um serviço
genérico de 6 métodos.

```php
final class PublicarConfiguracaoDeNotificacao
{
    public function __construct(private readonly RepositorioDeConfiguracoes $repositorio) {}

    public function publicar(string $destinatario, User $ator): void
    {
        $config = new ConfiguracaoDeNotificacaoDeRma(
            destinatario: $destinatario,
            publicadaEm: now()->toImmutable(),
            publicadaPorNome: $ator->name,
            publicadaPorIdentificador: $ator->id,
        );

        DB::transaction(fn () => $this->repositorio->publicar(
            'notificacao_conclusao',
            $config->destinatario,
            $ator,
        ));
    }
}

final class ObterConfiguracaoEfetivaDeNotificacao
{
    public function __construct(private readonly RepositorioDeConfiguracoes $repositorio) {}

    public function obter(): ConfiguracaoEfetivaDeNotificacao
    {
        $publicada = $this->repositorio->ultimaPublicacao('notificacao_conclusao');

        if ($publicada === null) {
            return new ConfiguracaoEfetivaDeNotificacao(
                destinatario: config('rma.notificacoes.conclusao'), // fallback atual, preservado
                publicadaAdministrativamente: false,
            );
        }

        return new ConfiguracaoEfetivaDeNotificacao(
            destinatario: $publicada['valor'],
            publicadaAdministrativamente: true,
        );
    }
}
```

`ObterConfiguracaoEfetivaDeAlertaDeUrgencia`/`ObterConfiguracaoEfetivaDeConsolidacaoDeFrete`
seguem a mesma forma, fallback para `75.00`/`'PORTO ALEGRE'` respectivamente.

## Desacoplamento — como é garantido tecnicamente

Este é o ponto central pedido pelo roteiro: **`Rma` não pode passar a exigir que
`Configuracao` exista.** Mecanismo escolhido — **binding opcional no service container,
resolvido com fallback, nunca injeção direta obrigatória de uma dependência do módulo
`Configuracao` no construtor dos 3 consumidores**:

- `UrgenciaPorThreshold`, `EnviarNotificacaoDeConclusao`, `ConsolidarFretePorCidade`
  passam a receber o **valor efetivo já resolvido** (float/string), não uma dependência
  do módulo `Configuracao` — ex.:
  ```php
  final class UrgenciaPorThreshold
  {
      public function __construct(
          private readonly ?float $thresholdEmReais = null, // injetado via Service Provider
      ) {}

      public function listar(): Collection
      {
          $threshold = $this->thresholdEmReais ?? 75.00; // fallback local, dupla rede de segurança
          // ... resto do método igual, troca `75.00` por `$threshold`
      }
  }
  ```
- Um `ConfiguracaoServiceProvider` (`app/Configuracao/Infraestrutura/
  ConfiguracaoServiceProvider.php`) faz o `bind` do valor resolvido:
  ```php
  $this->app->when(UrgenciaPorThreshold::class)
      ->needs('$thresholdEmReais')
      ->give(fn () => app(ObterConfiguracaoEfetivaDeAlertaDeUrgencia::class)->obter()->thresholdEmReais);
  ```
- **Se o módulo `Configuracao` inteiro for removido** (diretório apagado, Service
  Provider desregistrado em `bootstrap/providers.php`), o binding `when(...)->needs(...)`
  simplesmente não existe — o construtor cai no valor default do parâmetro (`= null`),
  `?? 75.00`/`?? 'PORTO ALEGRE'`/`?? config('rma.notificacoes.conclusao')` resolve o
  mesmo comportamento de hoje. **Nenhuma classe de `Rma` importa nenhuma classe de
  `Configuracao`** — o acoplamento é unidirecional (`Configuracao` conhece `Rma` só
  para autorização/consulta do ator, `Rma` não conhece `Configuracao` em nenhum
  `use`/`import`).
- Isto é literalmente diferente de "`Configuracao::efetivo('threshold_urgencia')`
  chamado de dentro de `Rma`" (mencionado como opção no roteiro) — essa forma exigiria
  `Rma` importar `App\Configuracao\...`, criando acoplamento em tempo de compilação
  mesmo com fallback em runtime. A forma escolhida (parâmetro opcional + binding externo)
  mantém `Rma` compilável e testável **sem o diretório `app/Configuracao/` existir no
  disco**.
- Consequência para os testes das Fases 5/7 já existentes (`UrgenciaPorThresholdTest.php`
  etc.): continuam passando sem alteração — instanciar `new UrgenciaPorThreshold()` (sem
  argumento) usa o fallback `75.00`, comportamento idêntico ao pré-existente. Novos
  testes desta fase (`tests/Feature/Configuracao/...`) cobrem o caminho "publicado
  sobrepõe fallback".

## Autorização — decisão registrada

`Papel::podeGerenciarUsuarios()` (já existe desde a Fase 1, hoje usado por
`UserPolicy::gerenciar` para gestão de usuários — `Supervisor`/`SuperAdministrador`),
não um novo método `podeGerenciarConfiguracao()`. Justificativa: os 3 candidatos reais
(destinatário de e-mail, threshold financeiro, cidade de consolidação) são parâmetros
operacionais de mesma sensibilidade que "quem pode editar outro usuário" — nenhum é tão
sensível quanto segredo de credencial (que exigiria `SuperAdministrador` isolado, como
o CONAHOM faz para `SegredoDoEnvioDeEmail`, fora de escopo aqui por não haver segredo
nos 3 candidatos, `INV-RMA-09` §B.3). Criar um método novo no enum `Papel` para uma
distinção que não existe em nenhum candidato real seria antecipar uma granularidade sem
evidência — mesmo princípio de "sem número mágico, sem invenção sem evidência" já
aplicado a `Papel::podeReverterAlemDoMesmoDia()` (Fase 4), que só nasceu quando uma
regra real (`LEG-RMA-015`) precisou dele.

`app/Policies/ConfiguracaoPolicy.php` — `gerenciar(User $ator): bool` delega a
`$ator->papel->podeGerenciarUsuarios()`, mesma forma de `UserPolicy`.

## Rotas e Controller

```
GET  /admin/configuracoes            -> ConfiguracaoController@edit    (name: configuracao.edit)
PUT  /admin/configuracoes            -> ConfiguracaoController@update  (name: configuracao.update)
```

`app/Http/Controllers/Configuracao/ConfiguracaoController.php` — `edit()` monta os 3
valores efetivos (via os 3 `Obter...` de Aplicacao) para exibir no formulário; `update()`
recebe os 3 campos, chama os 3 `Publicar...` (cada um valida o próprio campo — se um
falhar, `DomainException` vira erro de validação no formulário, os outros 2 não são
publicados nessa submissão — decisão simples: tudo-ou-nada por campo individual, não
transação cruzando os 3 objetos, já que são publicações independentes).

## UI

`resources/views/configuracao/edit.blade.php` — 1 tela, 3 campos (`destinatario`
email, `threshold_urgencia` number step 0.01, `cidade_consolidacao_frete` text), scaffold
Tailwind padrão do Laravel já presente no projeto (`@tailwindcss/vite`, confirmado por
`INV-RMA-08`), sem herdar de nenhum layout de tema V1/V2/V3 — layout próprio mínimo
(`resources/views/layouts/admin.blade.php`, novo, só para esta tela e futuras telas
administrativas transversais). Mostra, abaixo de cada campo, "última publicação por
{nome} em {data}" ou "valor padrão do sistema" quando `publicadaAdministrativamente`
é `false`.

## Testes de regressão exigidos

Antes de considerar a fase pronta: `sail test` completo (não só os testes novos) —
nenhum teste das Fases 5 (`UrgenciaPorThresholdTest`) ou 7 (teste de
`EnviarNotificacaoDeConclusao`, se existir) pode falhar. Isso é a prova concreta de
"módulo desacoplável": os testes das Fases 5/7 não são alterados por esta fase, só os
consumidores ganham um parâmetro opcional com default idêntico ao valor fixo anterior.

## Rejeitado — alternativas consideradas

- **`Configuracao::efetivo('chave')` chamado estaticamente de dentro de `Rma`** —
  rejeitado por criar import/acoplamento em tempo de compilação (ver §Desacoplamento).
- **Feature flag booleana ligando/desligando o módulo inteiro** — desnecessária: o
  binding opcional já é a "flag" (presença/ausência do Service Provider), sem precisar
  de uma segunda camada de configuração para configurar a configuração.
- **Tabela por configuração** — rejeitada por §Schema (replicaria o mesmo shape 3x sem
  ganho, dado que os 3 candidatos são escalares simples).
