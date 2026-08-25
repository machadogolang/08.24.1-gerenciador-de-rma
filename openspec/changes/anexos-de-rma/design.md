# Design — Anexos de arquivo no RMA

## Schema

```
anexos_de_rma
  id                bigint pk
  rma_id            bigint fk -> rmas.id, cascade on delete
  caminho           string     -- chave física, convenção de A.3 (sem dado pessoal)
  nome_original     string     -- nome do arquivo como o usuário enviou
  tipo_mime         string
  tamanho_em_bytes  unsignedInteger
  enviado_por_id    bigint fk -> users.id, nullable (robustez de FK)
  enviado_por_nome  string     -- snapshot, mesma razão de `configuracoes.publicada_por_nome`
  created_at        datetime  -- "enviado em"
```

Nome da tabela: `anexos_de_rma` (não `arquivos` genérico) — reforça que o anexo é uma
entidade de suporte do agregado `Rma`, não uma tabela de propósito geral; coerente com
a decisão de A.4 de não construir um catálogo central.

**Sem coluna de categoria/papel (foto/laudo/NF) no dia 1** — `INV-RMA-09` §A.4 registra
isso como decisão de produto adiada. Se necessário depois, é uma migration aditiva
(`add_categoria_to_anexos_de_rma_table`) mais um `enum CategoriaDoAnexo`, sem
redesenho do resto.

## Interface de storage (`app/Rma/Dominio/ArmazenamentoDeArquivos.php`)

```php
interface ArmazenamentoDeArquivos
{
    public function guardar(string $conteudo, string $caminho): void;
    public function baixar(string $caminho): string;
    public function existe(string $caminho): bool;
    public function remover(string $caminho): void;
}
```

Forma mínima do CONAHOM (`guardar`/`baixar`/`existe`/`remover`), **sem**
`enderecoTemporario` (URL assinada) — só se justifica com um provider que exija URL
temporária (S3-compatible); local não precisa, arquivo é servido via rota autenticada
do próprio Laravel (`§Download`). Vive em `app/Rma/Dominio/` (não
`App\Compartilhado\Armazenamento`) porque hoje só `Rma` consome — extrair para
`Compartilhado` é a evolução natural **quando** um segundo módulo precisar, não antes
(`INV-RMA-09` §A.4).

`app/Rma/Infraestrutura/ArmazenamentoLocal.php` — implementação via
`Illuminate\Filesystem\Filesystem` sobre o disco `local` do Laravel (não `public` —
anexos de RMA não são publicamente acessíveis por URL direta, só via rota autenticada
com `Policy`, ver §Download).

## Convenção de caminho (inspirada em `ContextoDoArquivo`, sem portar a classe)

`rma/{rma_id}/anexo/{uuid}.{extensao}` — sem nome do cliente, sem descrição do
defeito, sem qualquer dado pessoal no caminho (mesma regra de higiene do CONAHOM,
barata de aplicar sem portar `ContextoDoArquivo` inteiro). `{rma_id}` é o identificador
interno do agregado (não um dado pessoal), `{uuid}` evita colisão/enumeração
sequencial de arquivo por adivinhação de número.

## Validação de upload

| Regra | Valor | Justificativa |
|---|---|---|
| Tipos permitidos | `image/jpeg`, `image/png`, `image/webp`, `application/pdf` | Cobre os 3 candidatos de produto (`INV-RMA-09` §A.2): foto do defeito (imagem), laudo técnico e NF digitalizada (tipicamente PDF ou foto/scan) — sem abrir para tipo executável/script, único vetor de risco real de upload |
| Tamanho máximo por arquivo | 10 MB | Foto de celular moderno tipicamente 2-8 MB (comprimida); PDF de laudo/NF de poucas páginas raramente passa de poucos MB; 10 MB dá folga sem permitir vídeo/arquivo grande por engano. Valor de produto razoável, não uma medição real de amostra — revisar se usuários relatarem rejeição de foto legítima |
| Quantidade de anexos por RMA | Sem limite rígido no dia 1 | Nenhuma evidência de que anexos em excesso é um problema real; se volume virar operacional, é ajuste futuro |
| Nome de arquivo | Sanitizado, extensão derivada do `tipo_mime` real (não da extensão informada pelo cliente) | Evita path traversal/spoofing de extensão — validação de forma, mesmo espírito de `ContextoDoArquivo` (validação de forma, não de conteúdo) |

Implementado como `app/Rma/Aplicacao/AnexarArquivoAoRma.php` chamando
`Illuminate\Validation\Rules\File` (`mimes:jpg,png,webp,pdf`, `max:10240` KB) antes de
gravar — validação de framework, não reinventada.

## Onde entra na UI — sem tocar `VerDetalheDoRma`

`VerDetalheDoRma.php` (Fase 3) **não é alterado**. A seção de anexos em
`resources/views/rma/show.blade.php` é aditiva:

```blade
@include('rma._anexos', ['rma' => $rma])
```

`_anexos.blade.php` é renderizada por dados que o próprio Controller novo injeta na
view (não pelo objeto de domínio `Rma` — `AnexoDoRma` não precisa ser propriedade do
agregado puro de `Dominio/Rma.php`, evita reabrir esse arquivo e seus consumidores
existentes). Rotas próprias, fora do resource `rmas.*` já existente:

```
GET  /rmas/{rma}/anexos              -> AnexoDoRmaController@index    (name: rmas.anexos.index)
POST /rmas/{rma}/anexos              -> AnexoDoRmaController@store    (name: rmas.anexos.store)
GET  /rmas/{rma}/anexos/{anexo}      -> AnexoDoRmaController@show     (name: rmas.anexos.show, download)
DELETE /rmas/{rma}/anexos/{anexo}    -> AnexoDoRmaController@destroy  (name: rmas.anexos.destroy)
```

`app/Http/Controllers/Rma/AnexoDoRmaController.php` — Controller novo, próprio,
delega a `AnexarArquivoAoRma`/`RemoverAnexoDoRma`/`BaixarAnexoDoRma` (Aplicacao). Nunca
adiciona método a `RmaController` (Fase 3) nem a `CicloDeVidaController` (Fase 4) —
responsabilidade HTTP separada, mesmo raciocínio já usado para não reaproveitar
`RmaController` nas transições de ciclo de vida.

## Autorização

`app/Policies/AnexoDoRmaPolicy.php` — `criar`/`remover` delegam a
`$ator->papel->podeGravar()` (já existe desde a Fase 1, mesma regra de "quem pode
editar o RMA pode anexar/remover anexo" — anexo é parte do mesmo agregado, não uma
permissão nova). `visualizar`/`baixar` exigem só autenticação (mesma regra de leitura
do RMA em si).

## Desacoplamento — como é garantido tecnicamente

Diferente de `Configuracao` (módulo transversal separado), `AnexoDoRma` **vive dentro**
do módulo `Rma` — a pergunta de desacoplamento aqui não é "Rma funciona sem
`AnexoDoRma`", é **"o resto do RMA V3 (telas, testes, fluxos já existentes das Fases
1-9) continua funcionando exatamente igual se a feature de anexos nunca for usada ou
for desligada"**:

- **Nenhuma tabela/coluna existente é alterada** — `anexos_de_rma` é uma tabela nova,
  `rmas` não ganha coluna nova nesta fase. Migração é 100% aditiva.
- **Nenhum caso de uso existente (`CriarRma`, `EditarRma`, `ReceberRma`,
  `EncaminharRma`, `ConcluirRma`, as 10 regras de alerta, `Publicar...` da Fase A) lê ou
  escreve `AnexoDoRma`** — é uma entidade de suporte inerte para o resto do domínio, o
  mesmo padrão que `ModificacaoDeRma` (histórico, Fase 7) já usa dentro do módulo.
- **A seção de anexos na view é aditiva (`@include`), não uma alteração de
  `show.blade.php` que reescreve estrutura existente** — se a rota
  `rmas.anexos.index` não existir (Controller removido), o `@include` de uma parcial
  que não existe seria o único ponto de falha; mitigado registrando a task explícita
  de que a `@include` deve checar `@if(Route::has('rmas.anexos.index'))` antes de
  renderizar, para que remover o módulo (deletar o Controller/rotas) não quebre
  `show.blade.php` — mecanismo de desligamento é literalmente apagar os arquivos do
  Controller/rotas/Policy, sem precisar de feature flag adicional, porque nada mais
  depende deles.
- **Storage físico isolado por convenção de caminho (`rma/{id}/anexo/`)** — remover o
  diretório `storage/app/rma/` não afeta nenhuma outra funcionalidade, já que nenhum
  outro módulo grava nesse prefixo.

Mecanismo escolhido, portanto: **isolamento por composição aditiva** (tabela nova,
Controller novo, rota nova, `@include` defensivo), não um binding de service container
como na Fase A — proporcional, porque aqui o "desligamento" é literal (apagar arquivos
específicos), não uma troca de comportamento em runtime como o threshold/cidade da
Fase A precisa.

## Rejeitado — alternativas consideradas

- **Módulo `Arquivos`/`Armazenamento` central com `FonteDoCatalogoDeArquivos`** —
  rejeitado por `INV-RMA-09` §A.3 (um único consumidor real hoje).
- **Storage em nuvem (S3-compatible) no dia 1** — rejeitado por `INV-RMA-09` §A.5 (sem
  evidência de volume que justifique; interface já deixa a porta aberta).
- **Coluna de categoria de anexo no schema inicial** — adiado, decisão de produto sem
  evidência ainda (`INV-RMA-09` §A "Pendências reais" item 2).
