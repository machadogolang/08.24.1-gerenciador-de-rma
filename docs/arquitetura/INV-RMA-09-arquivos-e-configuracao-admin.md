# INV-RMA-09 — Sistema de arquivos/anexos e configuração de admin (Trilha B)

Data: 2026-08-25. Investigação formal, aberta e concluída nesta sessão, mesmo padrão de
`INV-RMA-05`/`06`/`07`/`08`: investigação e parecer no mesmo documento. Numeração:
`05`/`06`/`07`/`08` já existem (confirmado por `ls docs/arquitetura/` nesta sessão). `09`
é o próximo número livre.

**Pergunta que este documento responde:** duas propostas de evolução do RMA V3,
inspiradas em módulos reais do projeto irmão CONAHOM
(`~/github/online-conahom-laravel`) — (1) um sistema de arquivos/anexos e (2) uma área
de configuração de admin — o que o CONAHOM realmente faz, onde o RMA V3 se beneficiaria,
e com que proporcionalidade (não copiar cegamente a complexidade do CONAHOM só porque
existe lá).

**Regra de ouro desta investigação:** nada aqui é implementação. Nenhuma migration,
model, view, config publicável ou upload real é criado nesta sessão. O que existe é
decisão registrada + pendência registrada, mesma disciplina de confiança do resto do
projeto: evidência de código real (CONAHOM, RMA V3 atual, legado) > desenho já fechado >
inferência justificada > pendência explícita.

## 0. Por que isto é Trilha B, não Trilha A

Confirmado nesta sessão por leitura de `docs/legado/inventario-funcional-rma-v2.md`,
`docs/legado/modelo-dominio-rma-legado.md` e `docs/legado/regras-negocio-rma-legado.md`
(`grep -in` por `upload|anexo|arquivo|imagem|foto|laudo|digitaliz` nos quatro documentos
de arqueologia do legado): **nenhuma evidência de upload de arquivo/imagem no legado.**
Os únicos achados relacionados a "documento" são os 4 blocos de nota fiscal
(`nfcompra`/`_emissao`/`_chave`, `nfvenda`/...) e o campo `chave` de 44 dígitos (NFe
brasileira) — são **campos de texto**, nunca um campo de upload de arquivo. Não há
`<input type="file">`, coluna de caminho de arquivo, nem qualquer diretório de upload
citado em `inventario-tecnico-15.9.7.md`/`inventario-banco-rma-v2.md`. Também não há
"configuração de admin editável" no legado — os parâmetros de negócio (threshold R$75,
cidade PORTO ALEGRE, destinatário de e-mail) são **hardcoded no código PHP** (`RN-12`,
achado do próprio Fase 5/Fase 7), nunca uma tela administrativa.

**Conclusão:** os dois sistemas são **evolução de produto pura**, não reconstrução que
faltou. Não há achado de "isso deveria ter sido capturado na Trilha A" — o legado nunca
ofereceu nem upload de arquivo nem configuração de admin. Mesma categoria de
`INV-RMA-07` (SaaS) e `INV-RMA-08` (Tema V3): investigado e registrado agora,
implementado só depois da baseline de paridade da Trilha A estar validada
(Fases 1-10 + QA, `INV-RMA-05` §15).

---

# PARTE A — Sistema de arquivos/anexos

## A.1. O que o CONAHOM realmente faz (evidência de código)

Lido diretamente nesta sessão em `~/github/online-conahom-laravel/app/{Armazenamento,
Arquivos}/`. O CONAHOM separa dois problemas em dois módulos distintos, deliberadamente:

### A.1.1. `App\Armazenamento` — a abstração de armazenamento físico

- **`ArmazenamentoDeArquivos`** (interface, `Dominio/ArmazenamentoDeArquivos.php`) — 5
  métodos: `guardar(conteudo, contexto): ArquivoArmazenado`, `baixar(arquivo): string`,
  `enderecoTemporario(arquivo, expiraEm): string` (URL assinada), `existe(arquivo): bool`,
  `remover(arquivo): void`. Comentário no próprio código: "o contexto vem de quem possui
  o arquivo; a identidade física nasce aqui" — o armazenamento não interpreta o
  significado do arquivo, só guarda bytes e devolve uma identidade física.
- **`ContextoDoArquivo`** (`Dominio/ContextoDoArquivo.php`) — objeto de valor que
  representa "de onde o arquivo vem, na linguagem de quem o possui", forma
  `{modulo}/{tipo-do-agregado}/{id-estavel}/{papel-do-arquivo}`. Regra explícita e
  comentada no código: **nenhum segmento pode carregar dado pessoal** (nome, documento,
  telefone, e-mail ficam de fora mesmo normalizados) — a chave precisa ser legível por
  quem inspeciona o armazenamento sem expor quem é o dono. Validação é de forma
  (caractere aceito `[a-z0-9-]`, tamanho máx. 120/segmento, sem travessia de diretório),
  não de conteúdo — quem monta o contexto (o módulo dono) é responsável por não vazar
  dado pessoal.
- **`ArquivoArmazenado`** (`Dominio/ArquivoArmazenado.php`) — `readonly` com
  `fornecedor` (enum), `chave`, `impressaoDigital` (hash), `tipoDeMidia`,
  `tamanhoEmBytes`. É a identidade física devolvida por `guardar()`, persistida pelo
  módulo dono (não pelo armazenamento).
- **`FornecedorDeArmazenamento`** (enum, `Local = 'local'`, `R2 = 'r2'`) — só 2 casos.
- **`ArmazenamentoRoteado`** (`Infraestrutura/ArmazenamentoRoteado.php`) — implementa
  `ArmazenamentoDeArquivos` recebendo um array `[nome => ArmazenamentoDeArquivos]` de
  fornecedores concretos + um `$fornecedorDeEscrita` fixo; toda escrita (`guardar`) vai
  sempre para o fornecedor de escrita configurado, mas leitura/remoção (`baixar`,
  `existe`, `remover`) são roteadas pelo `fornecedor` gravado no próprio
  `ArquivoArmazenado` — **por que existe esse roteador**: permite migrar de provedor
  (ex. local→R2) sem quebrar arquivos antigos já gravados no provedor anterior — cada
  arquivo "sabe" onde mora, o roteador só direciona a chamada certa. `ArmazenamentoLocal`
  e `ArmazenamentoEmR2` são os dois adaptadores concretos (`ArmazenamentoViaFilesystem`
  é uma base comum via `Illuminate\Filesystem`). `ConfiguracaoDoR2` isola as credenciais
  Cloudflare R2 (S3-compatible) da config Laravel padrão.
- **`PoliticaDePrecosDoArmazenamento`** + `EstimadorDeCustoDoArmazenamento` — modelam o
  custo real de nuvem (franquia de armazenamento em GB/mês, operações Classe A/B,
  egresso) por fornecedor, com data de vigência e fonte documentada — usado para estimar
  custo antes de crescer o volume de arquivos. **Isso é o sinal mais forte de
  proporcionalidade do CONAHOM**: o produto tem um caso de negócio real (evitar susto de
  fatura R2) que justifica esse nível de detalhe.

### A.1.2. `App\Arquivos` — o catálogo, separado do armazenamento físico

- **Por que a separação existe** (evidência do próprio código, comentário em
  `ArquivoCatalogado.php`): "projeção comum para consulta administrativa, sem transferir
  ao catálogo a propriedade ou as regras de negócio do arquivo". `Armazenamento`
  resolve "onde o byte mora e como buscar/gravar"; `Arquivos` resolve "o que existe, de
  quem é, listagem/filtro/paginação administrativa" — **cada módulo de domínio
  (Formação, Álbuns, Filiação, Publicações) continua dono do próprio metadado de negócio
  (tabela própria, ex. `documentos_da_solicitacao`)**; o catálogo central não duplica
  esse dado, só o projeta via SQL (`FonteDoCatalogoDeArquivos::consulta()`) numa forma
  comum (`ArquivoCatalogado`) para uma tela administrativa central listar/filtrar/paginar
  arquivos de todos os módulos juntos.
- **`ArquivoCatalogado`** (`Dominio/ArquivoCatalogado.php`) — DTO `readonly` com módulo,
  identificador, nome original, categoria, vínculo (tipo/id/rótulo), provider,
  visibilidade, tipo de mídia, tamanho, impressão digital, data de envio, chave lógica,
  lado (frente/verso, para documentos), `EstadoDaVersaoDoArquivo`.
- **`FiltrosDoCatalogo`/`PaginacaoDoCatalogo`/`OrdenacaoDoCatalogo`** — parâmetros de
  consulta administrativa (não vistos em detalhe nesta sessão além da assinatura, mas
  claramente parte do mesmo padrão de projeção agregada, não de regra de negócio).
- **`FamiliaDeMidia`** (enum: `Imagem`, `Pdf`, `Outro`) — classificação grosseira para
  UI (ex. mostrar preview de imagem vs. ícone genérico).
- **`EstadoDaVersaoDoArquivo`** (enum: `Atual`, `Historico`) — resolve o caso de um
  mesmo "papel" de documento (ex. "comprovante de pagamento") ser reenviado; a versão
  mais recente é `Atual`, as anteriores viram `Historico` — implementado via subquery
  correlacionada em `FonteDoCatalogoDeArquivosDaFiliacao::consulta()` (compara
  `enviado_em`/`id` com registros posteriores do mesmo tipo/lado).
- **`SituacaoFisicaDoArquivo`** (enum: `Disponivel`, `Ausente`, `ProviderIndisponivel`)
  — **três respostas, nunca duas**, resultado de `VerificarSituacaoFisicaDoArquivo`.

### A.1.3. `VerificarSituacaoFisicaDoArquivo` — por que é necessária

Lida integralmente (`app/Arquivos/Aplicacao/VerificarSituacaoFisicaDoArquivo.php`,
41 linhas). Comentário do próprio código explica o problema real: "o registro existe no
banco; e o objeto, existe no provider?" — **arquivo catalogado ≠ arquivo fisicamente
presente**. Drift acontece na prática (objeto removido manualmente do bucket, falha de
upload que gravou metadado mas não o byte, migração de provedor incompleta). A
verificação:

```php
try {
    $existe = $this->armazenamento->existe($arquivo);
} catch (ArmazenamentoIndisponivel) {
    return SituacaoFisicaDoArquivo::ProviderIndisponivel;
}
return $existe ? SituacaoFisicaDoArquivo::Disponivel : SituacaoFisicaDoArquivo::Ausente;
```

O ponto de design deliberado (comentado no código): **não tratar indisponibilidade
temporária do provider como perda definitiva** — se o provider não responde (rede,
outage), isso é uma resposta diferente de "o objeto não existe mais". Confundir as duas
faria uma queda temporária do R2 parecer "todos os arquivos sumiram", o que dispararia
alarme falso ou pior, exclusão incorreta de registro de catálogo. Existe também
`VerificarSituacaoFisicaDosArquivos` (plural) para checagem em lote — usado
provavelmente por um job periódico/tela de auditoria de integridade, não visto em
detalhe nesta sessão.

### A.1.4. Como outros módulos se conectam — o contrato `FonteDoCatalogoDeArquivos`

Interface (`app/Arquivos/Dominio/FonteDoCatalogoDeArquivos.php`): `modulo()`,
`rotulo()`, `consulta(): object` (query builder compatível), `categorias(): array`,
`origem(ArquivoCatalogado): string` (URL de volta ao registro de negócio),
`arquivo(ArquivoCatalogado): ?ArquivoArmazenado` (reconstrói a identidade física a
partir da projeção), `visualizar(ArquivoCatalogado): ?string` (URL de preview, só
imagens), `baixar(ArquivoCatalogado): string` (URL de download).

Lida a implementação completa de `FonteDoCatalogoDeArquivosDaFiliacao` (71 linhas): o
módulo dono (`Filiacao`) escreve uma **query SQL própria** (`DB::table(...)`) que
projeta a tabela de negócio real (`documentos_da_solicitacao`) para o formato comum
(`ArquivoCatalogado`), incluindo um `CASE` para rótulos amigáveis por categoria e a
subquery de `EstadoDaVersaoDoArquivo`. **Nenhum dado é duplicado/migrado para uma
tabela central** — o catálogo central (`ConsultaAoCatalogoDeArquivosComposta`,
`Arquivos/Infraestrutura/`) provavelmente faz `UNION` das consultas de todos os módulos
registrados (não lido em detalhe, mas coerente com a assinatura `consulta(): object`
usada por cada `FonteDoCatalogoDeArquivosDa*`). O mesmo padrão se repete em `Formacao`,
`Albuns`, `Filiacao`, `Publicacoes`, `Biblioteca` (5 módulos, confirmado por `find`) —
**é um sistema central reaproveitado, não duplicado**, com cada módulo mantendo posse
total do próprio schema/regra de negócio e só "publicando" uma view SQL para o catálogo
agregado.

## A.2. Onde o RMA V3 precisaria de arquivos/anexos de verdade

Sem evidência de upload no legado (§0) — isto é evolução nova, não reconstrução
faltante. Candidatos reais de valor de produto (não especificados em detalhe aqui, é
produto, não arquitetura): foto do produto com defeito (evidência visual do problema
relatado), NF digitalizada (hoje só o texto/chave de 44 dígitos é capturado — a imagem
do documento em si nunca foi um campo, nem no legado nem nas Fases 1-9 do V3), laudo
técnico da assistência ao concluir o RMA. Nenhum desses é um achado de arqueologia —
são hipóteses de produto razoáveis dado o domínio (assistência técnica realmente lida
com fotos/documentos no dia a dia), mas não uma funcionalidade que a Trilha A
"deveria" ter capturado.

## A.3. Proporcionalidade — o RMA V3 precisa da mesma complexidade do CONAHOM?

**Não, e a resposta tem evidência própria, não é intuição.** Aplicando o mesmo
princípio de proporcionalidade já usado em toda a arquitetura do projeto
(`INV-RMA-05` §2 — módulo ganha fronteira `Dominio/Aplicacao/Infraestrutura` completa só
quando a complexidade justifica, senão usa Eloquent direto):

| Dimensão do CONAHOM | Por que existe lá | Existe evidência equivalente no RMA V3? |
|---|---|---|
| 2 fornecedores de storage (Local/R2) + roteador | Produto multiplataforma com múltiplos módulos (Formação, Álbuns, Filiação, Publicações, Biblioteca) gerando volume real de arquivo, cenário de custo de nuvem relevante o bastante para ter `PoliticaDePrecosDoArmazenamento` dedicada | Não. RMA V3 é single-tenant por enquanto (`INV-RMA-07` — SaaS ainda não implementado), volume esperado de anexos por RMA é baixo (poucas fotos/PDFs por registro, não uma biblioteca de mídia) |
| Catálogo central agregando 5 módulos via `FonteDoCatalogoDeArquivos` | Múltiplos módulos de domínio distintos, cada um com seus próprios arquivos, mais uma necessidade real de tela administrativa única que lista arquivos de todos juntos | Não — hoje só existiria **um** módulo consumidor (`Rma`). Não há um segundo módulo do RMA V3 com necessidade de anexos (Identidade/Parceiros não têm evidência de upload) — construir a abstração `FonteDoCatalogoDeArquivos` para um único consumidor é complexidade sem benefício até um segundo caso de uso aparecer |
| `EstadoDaVersaoDoArquivo` (versão atual vs. histórico do mesmo "papel" de documento) | Fluxo de reenvio de documento (ex. associado reenvia comprovante rejeitado) é um caso real do domínio de filiação | Sem evidência de que reenvio de anexo é um requisito do RMA — se aparecer (ex. cliente reenvia foto melhor do defeito), é extensão pequena, não motivo para adiantar agora |
| `VerificarSituacaoFisicaDoArquivo` (drift catálogo × storage) | Necessário quando há múltiplos provedores/migração entre eles e volume que torna drift provável | Proporcional só se o RMA V3 adotar storage em nuvem com risco real de objeto sumir — local puro (ver A.4) tem superfície de drift muito menor (mesmo disco do banco) |

**Recomendação: overengineering copiar a arquitetura do CONAHOM inteira agora.** O RMA
V3 precisa de uma fração pequena e proporcional do desenho: **um único adaptador de
storage (local, com caminho aberto para trocar por S3-compatible depois via a mesma
interface)**, **anexos como parte do agregado `Rma`** (não um catálogo central
separado, porque não há um segundo módulo consumidor hoje), e **sem** política de preço
de nuvem, sem `EstadoDaVersaoDoArquivo`, sem verificação de drift dedicada no dia 1.

## A.4. Onde entra na estrutura de módulos existente

**Decisão recomendada: dentro de `app/Rma/`, não um módulo novo `Arquivos`/
`Armazenamento`.** Justificativa: o módulo `Rma` já é o único do projeto com fronteira
completa `Dominio/Aplicacao/Infraestrutura` (`INV-RMA-05`, confirmado em `INV-RMA-07`
§2) — anexo de RMA é um conceito que só faz sentido vinculado a um `Rma` específico
(não existe "anexo solto"), então cabe como uma entidade de suporte dentro da mesma
fronteira, análogo a como `ModificacaoDeRma` (histórico, Fase 7) já vive dentro do
módulo. Um módulo `Arquivos`/`Armazenamento` central só se justifica quando um segundo
módulo de domínio do RMA V3 (não há candidato hoje — Identidade e Parceiros não têm
evidência de necessidade de upload) precisar de anexos — nesse momento, a extração da
abstração de storage para `App\Compartilhado\Armazenamento` (paralelo a
`App\Compartilhado\Uf`, já existente) seria a evolução natural, não um catálogo central
estilo CONAHOM (que resolve N módulos, o RMA V3 hoje tem 1 candidato real).

Desenho mínimo proposto (não implementado, registrado como direção):

- Interface `App\Compartilhado\Armazenamento\ArmazenamentoDeArquivos` (ou dentro de
  `App\Rma\Dominio` mesmo, se ficar restrito ao módulo — decisão de implementação, não
  de arquitetura), com a mesma forma mínima do CONAHOM (`guardar`/`baixar`/`existe`/
  `remover`), sem o `enderecoTemporario` de URL assinada até haver um provider que exija
  isso (local não precisa).
- `AnexoDoRma` (entidade dentro do agregado `Rma`, não uma tabela desacoplada tipo
  catálogo) — `rma_id`, caminho/chave física, nome original, tipo de mídia, tamanho,
  enviado por, enviado em. Sem `papel`/`categoria` estruturado no dia 1 (foto vs. laudo
  vs. NF) a menos que o produto já saiba diferenciar isso na tela — se sim, um enum
  simples resolve, sem precisar do `FonteDoCatalogoDeArquivos` genérico.
- Reaproveitar o princípio de `ContextoDoArquivo` do CONAHOM **como convenção de nome de
  caminho** (`rma/{numero-ou-id-estavel}/anexo/{uuid}`), sem necessariamente portar a
  classe inteira — é uma ideia de higiene (sem dado pessoal na chave, sem travessia de
  diretório) barata de aplicar mesmo numa implementação simples.

## A.5. Storage local vs. cloud

**Sem evidência de que o RMA V3 precise de multi-provider agora.** Diferente do
CONAHOM (produto com módulos de mídia pesada — álbuns de fotos, publicações), o RMA V3
lida com poucos anexos por registro (fotos/PDFs de um RMA específico), volume baixo,
sem indício de necessidade de CDN/egress otimizado. **Recomendação: um adaptador local
(`Illuminate\Filesystem`, disco `local`/`public` do Laravel) atrás da interface mínima
de A.4**, para que trocar por S3-compatible (incluindo Cloudflare R2, se a organização
já usa no CONAHOM e quiser padronizar depois) seja uma implementação nova da mesma
interface, não uma reescrita. Não construir `ArmazenamentoRoteado` nem
`FornecedorDeArmazenamento` (enum de múltiplos provedores) até existir um segundo
provider real em produção — a interface já deixa essa porta aberta sem custo de
construir os dois adaptadores hoje.

## A.6. Relação com `EVO-SAAS-001`

`INV-RMA-07` (linha da tabela §4, após a linha de `TentativaDeAcesso`) já registra:
"Arquivos/anexos futuros | Pertence ao tenant | Isolamento de storage por tenant é
parte da mesma fronteira — ver §9." — **achado de inconsistência nesta sessão**: essa
referência a "§9" está incorreta no documento original — `INV-RMA-07` §9 é sobre
Superadmin (plataforma × tenant), não sobre storage; não existe uma seção dedicada a
isolamento de storage por tenant em `INV-RMA-07`. Não é corrigido aqui (fora do escopo
desta investigação editar `INV-RMA-07`), mas registrado para quem for implementar
`EVO-SAAS-001` não se surpreender com a referência quebrada.

O que importa para esta investigação: a classificação já está correta — **anexo de RMA
pertence ao tenant**, mesma fronteira de qualquer outra entidade do domínio (§4 de
`INV-RMA-07`). Consequência prática para o desenho de A.4: se/quando `EVO-SAAS-001`
for implementado, o caminho físico do anexo (`ContextoDoArquivo`-like) precisa
incorporar o tenant no prefixo (`rma/{tenant_id}/{numero-do-rma}/anexo/{uuid}`), e o
Global Scope/`TenantContext` de `INV-RMA-07` §6 precisa cobrir `AnexoDoRma` como
qualquer outro model tenant-scoped — nenhuma novidade arquitetural, só mais uma
entidade na lista já existente. Não implementar agora (mesma regra de momento do §B.5).

---

# PARTE B — Configuração de admin

## B.1. O que o CONAHOM realmente faz (evidência de código)

### B.1.1. Tipos de configuração (`resources/views/admin/configuracoes/`)

Seis telas administrativas confirmadas por `ls`: `index.blade.php` (hub — grade de
cards, cada um com status `disponivel`/`em_breve`, link "Acessar"),
`identidade-institucional.blade.php`, `email.blade.php`, `comunicacao.blade.php`,
`carteira.blade.php`, `aviso-de-vencimento.blade.php`. Lidos `index.blade.php`
(estrutura do hub) e `aviso-de-vencimento.blade.php` (exemplo de regra de negócio
configurável — ver B.1.3). Categorias reais: **identidade visual** (institucional),
**e-mail/SMTP** (transporte de envio), **comunicação** (canais — WhatsApp, e-mail
institucional, reply-to), **carteira** (provavelmente pagamento/financeiro, não lido em
detalhe), **regra de negócio com prazo** (aviso de vencimento — horizonte em dias).

### B.1.2. Modelagem como objeto de domínio + padrão "publicar"

Lidos `ConfiguracaoDeEnvioDeEmail`, `ConfiguracaoDeComunicacao`,
`PublicarConfiguracaoDeEnvioDeEmail`, `ConfiguracaoEfetivaDeEnvioDeEmail` na íntegra.

- **Cada configuração é um objeto de valor `readonly` imutável com validação no
  construtor** (ex. `ConfiguracaoDeEnvioDeEmail`: transporte só `ambiente`/`smtp`,
  porta 1-65535 se SMTP, scheme só `smtp`/`smtps`/ausente — `DomainException` se
  inválido). Nunca um array solto ou uma linha de banco genérica `chave => valor`.
- **Cada configuração carrega sua própria autoria e data de publicação como campo do
  próprio objeto** (`publicadaEm: DateTimeImmutable`, `publicadaPorNome: string`,
  `publicadaPorIdentificador: ?int`) — não é um "audit log" genérico separado, é parte
  da identidade do valor publicado.
- **Padrão "publicar" (não é git — não versiona histórico completo, mas também não é
  "só sobrescreve" sem rastro):** `PublicarConfiguracaoDeEnvioDeEmail::publicar(...)`
  monta um novo `ConfiguracaoDeEnvioDeEmail` (validado) e chama
  `$this->configuracoes->publicar($novaConfig)` dentro de uma transação — o objeto
  `ConfiguracoesDeEnvioDeEmail` (repositório, não lido em detalhe nesta sessão, mas
  nomeado no plural = coleção/histórico) é quem decide se grava um novo registro
  versionado ou substitui — a assinatura sugere que cada "publicar" é um evento
  registrado com quem/quando, não uma linha `UPDATE` sem rastro. Segredos (senha SMTP)
  são tratados à parte (`SegredoDoEnvioDeEmail::substituir`), fora do objeto de
  configuração público — separação deliberada entre dado configurável exibível e
  segredo.
- **"Efetivo" separado de "publicado" — confirmado pelo nome e campos de
  `ConfiguracaoEfetivaDeEnvioDeEmail`** (`nomeDoMailer`, `configuracaoDoMailer`,
  `publicadaAdministrativamente: bool`): existe uma camada de **resolução** que decide o
  valor efetivo em runtime — se existe configuração publicada pelo admin, usa ela
  (`publicadaAdministrativamente = true`); senão, cai para o `.env`/config estático do
  Laravel (`publicadaAdministrativamente = false`). Isso é exatamente o padrão "config
  de admin como override opcional sobre o `.env`, nunca uma substituição obrigatória" —
  `ConfiguracaoDeComunicacao::padrao(?string $whatsapp)` confirma o mesmo padrão:
  "Configuração estática antiga é fallback; se inválida, o canal fica ausente" (comentário
  literal no código).

### B.1.3. Exemplo de regra de negócio configurável: aviso de vencimento

`aviso-de-vencimento.blade.php` (lido) é o paralelo mais direto ao caso do RMA V3: um
único campo (`horizonte_em_dias`, `min=1 max=180`) que decide "com quanta antecedência a
fila administrativa destaca anuidades a vencer" — **exatamente a mesma forma de
problema que RN-12 (threshold R$75) e o horizonte de alerta de garantia do RMA V3
representam**: uma constante de regra de negócio, hoje só editável mexendo em código,
que o CONAHOM já resolveu como formulário simples + `publicar`.

## B.2. Onde o RMA V3 precisaria disso — candidatos reais encontrados no código

Todos os três candidatos abaixo foram confirmados por leitura direta do código já
implementado (Fases 1-9), não inventados:

1. **Destinatário de notificação de conclusão**
   (`app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php:17`,
   `config('rma.notificacoes.conclusao')`, lido de `config/rma.php` →
   `env('RMA_NOTIFICACAO_CONCLUSAO')`, Fase 7). Hoje só `.env` — trocar exige acesso ao
   servidor/redeploy, não uma tela. Comentário no próprio código já reconhece a
   diferença do legado (`LEG-RMA-045`, `ezequiel()` hardcoded) mas para no `.env`, não
   avança para configuração de admin.
2. **Threshold de urgência R$75** (`RN-12`,
   `app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php:41`, `->where('valor', '>',
   75.00)`) — literal `75.00` direto na query, comentado como fiel ao legado
   (`15.8.1/banco.php:777`), mas hoje é constante de código — mudar o valor exige
   deploy.
3. **Cidade "PORTO ALEGRE" hardcoded**
   (`app/Rma/Aplicacao/ConsolidarFretePorCidade.php:21`, `private const CIDADE =
   'PORTO ALEGRE'`, Fase 7, `RN-16`) — o próprio comentário do código já registra a
   decisão de não reconstruir como configurável ("comportamento documentado do legado,
   sem política configurável a reconstruir aqui") — ou seja, a Fase 7 já identificou
   esse ponto como candidato a virar configuração futura, sem agir sobre isso (correto:
   não é escopo da Trilha A).
4. **Política de garantia por fabricante** — já registrada como `EVO-DOM-003` em
   `docs/produto/backlog-evolutivo.md` (origem: RN-02, regra MARKVISION hardcoded,
   `regras-negocio-rma-legado.md`). Não é duplicada aqui — ver §B.5 sobre como este
   documento se conecta com aquele.

Não foram encontrados outros candidatos hardcoded relevantes nas Fases 1-9 além destes
quatro (busca dirigida por `config(`/`private const`/literais de negócio nos módulos
`Rma`/`Identidade`/`Parceiros` não revelou mais nada do mesmo padrão nesta sessão).

## B.3. Proporcionalidade — o RMA V3 precisa do mesmo desenho do CONAHOM?

**Parcialmente sim, com escopo bem menor.** O padrão central do CONAHOM — objeto de
valor imutável e validado + autoria/data de publicação como campo do próprio objeto +
resolução "efetivo = publicado ?? fallback estático" — é **exatamente o problema que o
RMA V3 tem** (RN-12, cidade, destinatário de e-mail são todos "constante que devia ser
publicável, com um `.env`/hardcode como fallback razoável"), então **reaproveitar a
metodologia é proporcional**, não overengineering. O que **não** é proporcional:

- **6 telas administrativas separadas** (identidade institucional, e-mail, comunicação,
  carteira, aviso de vencimento) — o RMA V3 tem hoje só 4 candidatos concretos (§B.2),
  não uma superfície de configuração institucional completa (o CONAHOM é uma
  organização com identidade visual própria, carteira de associados, múltiplos canais
  de comunicação — o RMA V3 é uma ferramenta operacional interna de assistência
  técnica, sem esse tipo de necessidade de identidade institucional). Uma única tela
  "Configurações do sistema" com os 3-4 campos reais (destinatário de e-mail, threshold
  R$, cidade de consolidação de frete, e depois política de garantia quando
  `EVO-DOM-003` for especificado) é proporcional; replicar a arquitetura de hub com
  cards "em breve" para seções que não existem ainda é estrutura vazia.
- **Segredo separado do valor configurável (`SegredoDoEnvioDeEmail`)** — só é
  proporcional se o RMA V3 realmente ganhar SMTP configurável por admin; nenhum dos 4
  candidatos de B.2 é um segredo (são um e-mail, um valor monetário, um nome de
  cidade) — não replicar essa separação até haver um candidato que seja de fato
  sensível.

## B.4. Modelagem recomendada (não implementada)

Direção de arquitetura, proporcional aos candidatos reais de B.2:

- Um objeto de valor por configuração publicável (ex. `ConfiguracaoDeNotificacaoDeRma`,
  `ConfiguracaoDeAlertaDeUrgencia`, `ConfiguracaoDeConsolidacaoDeFrete`), `readonly`,
  validado no construtor, com `publicadaEm`/`publicadaPorNome`/`publicadaPorIdentificador`
  como campos do próprio objeto — mesmo padrão do CONAHOM, proporcional porque o
  problema é idêntico (constante de negócio que devia ser editável com rastro de quem
  mudou).
- Um caso de uso `Publicar{Nome}` por configuração (ou um caso de uso genérico
  parametrizado, se as validações forem simples o bastante — decisão de implementação),
  seguindo o padrão `publicar()` dentro de transação.
- Um objeto "efetivo" (`ConfiguracaoEfetivaDe{Nome}`) que resolve **publicado (se
  existir) ?? valor do `.env`/constante atual (fallback)** — preserva o comportamento
  atual como default seguro se ninguém nunca configurar nada pela tela, mesmo padrão do
  CONAHOM (`ConfiguracaoDeComunicacao::padrao()`).
- Persistência: uma tabela por configuração ou uma tabela genérica
  `configuracoes_do_sistema` (chave + valor JSON + autoria) — **decisão de
  implementação, não de arquitetura**; o objeto de domínio validado é o que garante
  integridade, não o schema da tabela. Registrado como pendência (§B.6).
- Onde entra na estrutura de módulos: **não dentro de `app/Rma/`** (as configurações
  não são posse exclusiva do domínio RMA — destinatário de notificação e threshold de
  urgência são regras do RMA, mas a ideia de "configuração de admin publicável" é uma
  capacidade transversal, mesmo raciocínio do CONAHOM ter `Comunicacao` como módulo
  próprio, não dentro de `Filiacao`). **Recomendação: um módulo novo
  `App\Configuracao` (ou `App\Sistema`)** com fronteira própria
  (`Dominio/Aplicacao/Infraestrutura`), pequeno, hospedando os objetos de valor e casos
  de uso de publicação — os módulos consumidores (`Rma`) leem a configuração efetiva via
  esse módulo, análogo a como `EnviarNotificacaoDeConclusao` hoje lê `config()`
  diretamente e passaria a ler `ConfiguracaoEfetivaDeNotificacaoDeRma` injetada.

## B.5. Relação com `EVO-DOM-003`

`EVO-DOM-003` (`docs/produto/backlog-evolutivo.md`) já registra a necessidade de uma
"tabela de regras de garantia configurável por (fabricante × origem × janela de tempo)"
com o campo `politicadegarantia` (hoje texto morto) alimentando a regra de verdade. Este
documento **não duplica** `EVO-DOM-003` — a conexão é: `EVO-DOM-003` é um caso
específico e mais estruturado (regra por fabricante, não um valor único global) do
mesmo problema geral que B.4 endereça (regra de negócio hoje hardcoded/estática
virando editável por admin). Quando `EVO-DOM-003` for implementado, ele deveria nascer
**dentro do mesmo módulo `App\Configuracao`** proposto em B.4 (ou ao menos seguir o
mesmo padrão de objeto de valor + publicação com autoria), em vez de reinventar um
mecanismo de configuração próprio — é o tipo de reaproveitamento que este documento
recomenda registrar agora para não ser esquecido quando `EVO-DOM-003` for especificado
em detalhe.

## B.6. Pendências reais — não decididas por inferência

1. **Persistência exata de "configuração de admin"** (tabela por configuração vs. tabela
   genérica chave/valor) — decisão de implementação, adiada até `App\Configuracao`
   começar a ser codificado.
2. **Autorização de quem pode publicar configuração** — o RMA V3 já tem `Papel` (Fase
   1, 5 casos) e `Policy` como padrão (`UserPolicy`); qual(is) papel(is) podem publicar
   configuração de sistema não foi decidido aqui — é decisão de produto/segurança, não
   de arquitetura de dados.
3. **Se a UI é um hub com múltiplos cards (estilo CONAHOM `index.blade.php`) ou uma
   única tela com todos os campos** — dado o volume pequeno de candidatos reais (§B.2,
   4 itens), uma tela única é provavelmente proporcional, mas é decisão de UX, não
   fechada aqui.
4. **Se `EVO-DOM-003` (garantia por fabricante) nasce como parte do módulo
   `App\Configuracao` ou continua como extensão do domínio `Rma`/`Fabricante`** — B.5
   recomenda o primeiro, mas não é uma decisão fechada até `EVO-DOM-003` ser
   especificado em detalhe.

---

## Pendências reais — Parte A (arquivos)

1. **Se/quando um segundo módulo do RMA V3 precisar de anexos** — só nesse momento a
   extração para um catálogo central estilo CONAHOM (`FonteDoCatalogoDeArquivos`)
   se justificaria; não há candidato hoje (§A.3).
2. **Categorização de anexo por papel** (foto/laudo/NF digitalizada) — decisão de
   produto, não de arquitetura; um enum simples resolve se necessário (§A.4).
3. **Storage local vs. S3-compatible em produção real** — recomendado local no dia 1,
   interface aberta para trocar depois (§A.5); sem evidência de necessidade imediata de
   cloud.
4. **Isolamento de storage por tenant, quando `EVO-SAAS-001` avançar** — direção já
   registrada (§A.6), não implementado.

## Momento de implementação (ambas as partes)

Mesmo raciocínio de `INV-RMA-07` §13 e `INV-RMA-08` §8: nenhuma linha de código de
produto (migration, model, view, config publicável, upload real) antes da baseline de
paridade da Trilha A estar validada (Fases 1-10 + QA, `INV-RMA-05` §15). A investigação
(classificação de onde cada sistema se encaixa, proporcionalidade, modelagem
recomendada) está feita agora e não precisa ser reaberta quando a Trilha B começar de
verdade.

## Riscos

- **Produto:** overengineering — copiar a arquitetura completa do CONAHOM (multi-provider
  de storage, catálogo central, versão histórica de arquivo, hub de 6 telas de
  configuração) sem volume/caso de uso real que justifique. Mitigado por §A.3/§B.3
  (proporcionalidade avaliada explicitamente, não copiada às cegas).
- **Produto:** risco oposto — não aproveitar um padrão já validado em produção
  (objeto de valor + publicar + efetivo com fallback) e reinventar algo pior (ex.
  configuração como array solto sem validação, sem autoria) quando a Trilha B chegar.
  Mitigado por este documento registrar a metodologia agora (§B.4).
- **Arquitetural:** anexos vivendo dentro de `app/Rma/` (não um módulo central) pode
  precisar de refatoração se um segundo módulo aparecer depois — risco aceito
  deliberadamente (§A.3/A.4), custo de extração é baixo dado que a interface de
  storage já nasce desacoplada.

## Decisões recomendadas (podem ser tratadas como vigentes a partir de agora)

- **Parte A:** anexos como entidade (`AnexoDoRma`) dentro do módulo `Rma` existente, não
  um módulo `Arquivos`/`Armazenamento` novo; storage local via interface mínima
  (`guardar`/`baixar`/`existe`/`remover`), sem multi-provider nem catálogo central no
  dia 1; convenção de caminho sem dado pessoal inspirada em `ContextoDoArquivo`, sem
  portar a classe inteira; isolamento de tenant (quando `EVO-SAAS-001` avançar) via o
  mesmo Global Scope/`TenantContext` já decidido, sem mecanismo próprio.
- **Parte B:** novo módulo `App\Configuracao` com fronteira própria, hospedando objetos
  de valor imutáveis e validados por configuração (destinatário de notificação,
  threshold de urgência, cidade de consolidação de frete, e futuramente `EVO-DOM-003`),
  padrão "publicar" com autoria/data como campo do próprio objeto, resolução "efetivo =
  publicado ?? fallback do `.env`/constante atual". UI: uma tela única inicialmente
  (não um hub de múltiplas seções), dado o volume pequeno de candidatos reais.
- **Ambas:** nenhum código de produto antes da baseline de paridade da Trilha A validada.

## Backlog evolutivo — itens criados

Ver `docs/produto/backlog-evolutivo.md`: categoria nova `EVO-ARQUIVOS` (`EVO-ARQ-001`)
para a Parte A, item novo `EVO-CONF-001` adicionado à categoria já existente
`EVO-DOMINIO` (não uma categoria nova — ver justificativa no próprio backlog) para a
Parte B. Justificativa da separação: são capacidades genuinamente diferentes (anexo de
arquivo é armazenamento binário; configuração de admin é parâmetro de negócio
estruturado) — mesmo raciocínio já aplicado em `INV-RMA-07` §19 (categorias distintas
por natureza do item, não um contador único genérico).

## Referências

`~/github/online-conahom-laravel/app/Armazenamento/` (Dominio + Infraestrutura, código
real lido nesta sessão), `~/github/online-conahom-laravel/app/Arquivos/` (Dominio +
Aplicacao, código real lido nesta sessão), `~/github/online-conahom-laravel/app/Filiacao/
Infraestrutura/FonteDoCatalogoDeArquivosDaFiliacao.php` (exemplo real de módulo
consumidor do catálogo), `~/github/online-conahom-laravel/resources/views/admin/
configuracoes/` (`index.blade.php`, `aviso-de-vencimento.blade.php`, lidos nesta
sessão), `~/github/online-conahom-laravel/app/Comunicacao/` (Dominio + Aplicacao, código
real lido nesta sessão), `docs/legado/inventario-funcional-rma-v2.md`,
`docs/legado/modelo-dominio-rma-legado.md`, `docs/legado/regras-negocio-rma-legado.md`
(confirmação de ausência de upload/configuração de admin no legado, §0),
`app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php`,
`app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php`,
`app/Rma/Aplicacao/ConsolidarFretePorCidade.php`, `config/rma.php` (candidatos reais de
configuração, §B.2), `docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md`
(fronteira de tenant, §A.6), `docs/produto/backlog-evolutivo.md` (`EVO-DOM-003`,
`EVO-SAAS-001`, novos `EVO-ARQ-001`/`EVO-CONF-001`).
