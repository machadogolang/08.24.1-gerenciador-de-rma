# Backlog evolutivo — CellSystem RMA (pós-reconstrução)

Data: 2026-08-24. **Trilha B.** Nada aqui é implementado durante a Trilha A
(reconstrução fiel). Cada item nasce de uma evidência concreta da arqueologia — nunca de
uma ideia solta. Formato: ID · Título · Origem · Problema · Legado · Evolução ·
Benefício · Impacto · Complexidade · Risco · Dependências · Prioridade · Fase.

## EVO-SAAS

### EVO-SAAS-001 — Plataforma multiempresa (multi-tenant) real

- **Origem:** achado central de `docs/legado/modelo-dominio-rma-legado.md` §Empresa —
  `bd.empresa` já é um embrião de tenant, sem isolamento nenhum.
- **Problema observado:** um único banco já opera múltiplas empresas do grupo
  (Cellsystem, Expert, Registros Ativos, Informática) sem qualquer segmentação de
  acesso, dado ou numeração.
- **Legado:** campo texto livre, normalizado por `str_replace` ad-hoc (`RA`→`R A`),
  nunca usado para filtrar nenhuma query, relatório ou alerta.
- **Evolução:** plataforma SaaS multiempresa. Desenho concreto fechado em
  `docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md` (2026-08-25): banco
  compartilhado + `tenant_id` (Modelo A, escolhido sobre banco-por-empresa e híbrido —
  `INV-RMA-07` §5); isolamento por construção via Global Scope + `TenantContext` +
  route model binding customizado + Model Observer, nunca só disciplina de código
  (`INV-RMA-07` §6); `User`↔`Company` many-to-many via `company_user`, com `Papel`
  migrando de `users` para o vínculo (`INV-RMA-07` §7/§8 — evidência real do próprio
  legado, grupo econômico com múltiplas empresas sob um banco só); superadmin de
  plataforma tratado como autorização ortogonal ao `Papel` de tenant, nunca sobrecarga
  dele (`INV-RMA-07` §9); numeração de RMA por empresa via contador transacional
  dedicado, nunca `MAX+1` (`INV-RMA-07` §10); primeiro tenant = empresa "CellSystem",
  migrador V2→V3 (`INV-RMA-06`) ganha um passo trivial de carimbo de tenant, sem
  redesenho (`INV-RMA-07` §11); testes arquiteturais de isolamento como suíte própria e
  obrigatória, parametrizada por model tenant-scoped (`INV-RMA-07` §14). Pendências reais
  não decididas: catálogo compartilhável de `Fabricante` entre tenants; agregação de
  segurança cross-tenant; formato exato do flag de administrador de plataforma —
  `INV-RMA-07` §12/§17.
- **Benefício:** transforma um sistema interno em produto comercializável para N
  empresas.
- **Impacto:** altíssimo — é uma segunda geração do produto.
- **Complexidade:** alta.
- **Risco:** alto se implementado antes da reconstrução fiel — mistura escopo.
- **Dependências:** reconstrução fiel completa e validada (Trilha A — Fases 1-10 + QA de
  paridade, `INV-RMA-05` §15).
- **Momento recomendado:** nenhuma linha de código de tenancy antes da baseline de
  paridade estar validada (`INV-RMA-07` §13 — Estratégia C: fronteiras já investigadas
  e decididas agora, implementação só depois). A modularidade já existente (fronteira
  própria do módulo `Rma`, Policies centralizadas desde a Fase 1) foi desenhada de um
  jeito que absorve tenancy sem reescrita ampla — não há vantagem em adiantar código.
- **Prioridade sugerida:** alta, mas só depois da Trilha A.
- **Fase:** pós-reconstrução (Trilha B), primeira grande iniciativa.

### EVO-SAAS-002 — Catálogo de referência da plataforma com importação seletiva

- **Origem:** direção dada pelo usuário em 2026-08-25, registrada em
  `docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md` §4.1 — resolve a pendência
  original sobre `Fabricante`/`Fornecedor`/`AssistenciaTecnica` virarem "compartilháveis
  entre tenants".
- **Problema observado:** sem catálogo de referência, cada tenant digita "Samsung",
  "LG" etc. do zero — repetição de esforço entre clientes do SaaS, sem ganho nenhum de
  isolamento (o cadastro final continua pertencendo só ao tenant que digitou).
- **Evolução:** uma "wiki" — catálogo de referência mantido em nível de **plataforma**
  (não pertence a nenhum tenant) — com uma tela de importação onde o usuário do tenant
  **seleciona explicitamente** quais registros quer trazer (nunca um "importar tudo"
  automático) e confirma com um clique. A importação **copia** o registro para dentro
  do tenant — vira um `Fabricante`/`Fornecedor`/`AssistenciaTecnica` normal, pertencente
  ao tenant, indistinguível de um cadastrado manualmente. Não é referência viva: uma
  edição posterior na wiki não propaga para quem já importou — preserva o isolamento de
  dado do tenant como propriedade absoluta.
- **Benefício:** reduz atrito de onboarding/cadastro sem violar isolamento de tenant.
- **Impacto:** médio — é conveniência de cadastro, não mudança de fronteira de domínio.
- **Complexidade:** média (nova entidade de plataforma "wiki" + fluxo de importação +
  seleção de registros + decisão de curadoria de conteúdo, ainda em aberto).
- **Risco:** baixo — não implementado antes da Trilha B (mesma regra de `EVO-SAAS-001`).
- **Dependências:** `EVO-SAAS-001` (a fronteira de tenant precisa existir primeiro).
- **Decisão adiada:** quem/como a wiki é alimentada (curadoria da plataforma? agregação
  do que os tenants já cadastraram? catálogo de terceiros?) — sem evidência para decidir
  agora, não bloqueia o resto do desenho.
- **Fase:** pós-reconstrução (Trilha B), depois de `EVO-SAAS-001`.

### EVO-SAAS-003 — Comunidade/fórum inter-tenant

- **Origem:** direção dada pelo usuário em 2026-08-25 ("tipo um Adrenaline" — fórum onde
  usuários de empresas diferentes conversam entre si). Explicitamente marcada pelo
  próprio usuário como evolução futura, não algo a especificar em detalhe agora.
- **Evolução:** espaço de comunidade cruzando tenants deliberadamente (o único caso
  identificado até agora onde "cruzar tenant" é a intenção do produto, não um bug de
  isolamento) — usuários de diferentes empresas clientes do SaaS discutindo entre si
  (dúvidas técnicas, defeitos comuns, boas práticas de assistência técnica).
- **Ressalva arquitetural registrada (não decidida em detalhe):** um fórum cross-tenant
  não pode vazar dado de negócio do tenant (RMA, cliente, valores) para outras empresas
  — precisa de um modelo de identidade de participação separado do dado operacional
  (usuário participa "como pessoa da comunidade", não como extensão do cadastro
  operacional do tenant). Mesma disciplina de fronteira do restante desta investigação:
  não implementar sem antes especificar isolamento de conteúdo operacional × conteúdo de
  comunidade.
- **Benefício:** retenção/rede de valor entre clientes do SaaS (efeito de comunidade),
  fora do escopo central de RMA.
- **Impacto:** médio-alto como produto, mas fora do coração do produto (RMA) — avaliar
  contra o princípio "SaaS não significa ERP"/escopo focado antes de priorizar.
- **Complexidade:** alta o bastante para merecer sua própria investigação quando a hora
  chegar — não especificada aqui além do registro da ideia.
- **Risco:** baixo agora (não implementado); alto se implementado sem separar
  claramente identidade de comunidade × dado operacional do tenant.
- **Dependências:** `EVO-SAAS-001` (conceito de tenant/empresa precisa existir para
  "usuários de empresas diferentes" fazer sentido).
- **Fase:** pós-reconstrução (Trilha B), evolução distante — sem prioridade definida.

## EVO-UX

### EVO-UX-001 — Tema V3 mobile-first

- **Origem:** pedido do usuário nesta sessão (2026-08-25) — um terceiro tema visual,
  além de TEMA V1/V2 (que reproduzem fielmente o legado), pensado desde o início para
  telas pequenas mas funcionando bem também em desktop, inspirado na abordagem
  mobile-first real do projeto irmão CONAHOM (`~/github/online-conahom-laravel`).
  Investigação/decisão de arquitetura completa em
  `docs/arquitetura/INV-RMA-08-tema-v3-mobile-first.md`.
- **Problema observado:** nenhum dos dois temas legados (V1 = layout fixo 984px sem
  nenhum `@media`; V2 = breakpoints próprios não-fluidos, largura fixa por faixa) é
  mobile-first em nenhuma leitura razoável do termo — ambos foram desenhados para
  desktop, e são fielmente reproduzidos assim de propósito (V1/V2 existem para
  preservar, não para evoluir).
- **Legado:** nenhuma superfície do sistema legado (`14.6.1`/`15.8.1`) foi desenhada
  mobile-first — não há equivalente a reproduzir.
- **Evolução:** um terceiro tema (`TemaPreferido::V3`), estruturado como diretório
  irmão `resources/views/temas/v3/` (mesma convenção de `v1/`/`v2/`, mecanismo
  `ResolverTemaAtivo`/`view_do_tema()` já é N-ário e não precisa mudar de forma), mas
  com stack interna diferente: Tailwind CSS 4 (já presente no scaffold do projeto,
  `@tailwindcss/vite`, mas não usado por nenhuma view hoje — confirmado por leitura de
  `package.json`/`vite.config.js`/`resources/css/app.css`) em vez do Sass autoral/
  Bootstrap 3 de V1/V2. Metodologia mobile-first real (`INV-RMA-08` §1/§7): CSS base
  escrito para telefone, `min-width` para ampliar, nunca `max-width` para reduzir —
  breakpoints nomeados propostos (`quebra-xs/sm/md/lg/xl`, reaproveitando os valores já
  validados em produção pelo CONAHOM), alvo de toque mínimo de 44px em todo elemento
  acionável, mesmo em desktop. **Mudança de natureza registrada explicitamente
  (`INV-RMA-08` §5): V3 é design novo e moderno, não busca nenhuma fidelidade ao
  legado** — V1/V2 existem para preservar, V3 existe para evoluir; o processo de QA de
  paridade da Fase 10 não se aplica a ele. Escopo inicial recomendado: o mesmo escopo
  que a Fase 8 original cobriu para V1/V2 (RMA, parceiros, identidade) — não todas as
  rotas das Fases 1-9 de uma vez (`INV-RMA-08` §6). Consequência sobre o enum
  `TemaPreferido` (Fase 1): o método `alternar()` (toggle binário V1↔V2, `match`
  exaustivo sobre 2 casos) deixa de fazer sentido com 3 valores — vira um mecanismo de
  seleção explícita (`DefinirTemaPreferido` no lugar de `AlternarTemaPreferido`,
  `TemaPreferidoController::update` passa a receber o tema alvo do request) —
  `INV-RMA-08` §3/§10.
- **Benefício:** cobre o uso real em telefone/tablet, que nenhum dos dois temas
  fiéis ao legado atende bem; reaproveita metodologia já validada em produção pelo
  projeto irmão CONAHOM em vez de reinventar do zero.
- **Impacto:** médio-alto como experiência de uso; não altera nenhuma regra de negócio
  (RMA continua sendo RMA) nem a fronteira de tenant (`EVO-SAAS-001`) — é puramente
  camada de apresentação, mesma proporcionalidade já aplicada a V1/V2.
- **Complexidade:** média — reaproveita todo o domínio/casos de uso já implementados
  (Fases 1-9), é só uma terceira árvore de views + bundle Vite + tokens de design
  próprios; a mudança de `TemaPreferido::alternar()` para seleção explícita é pequena e
  localizada (2 arquivos + 1 Controller, `INV-RMA-08` §10).
- **Risco:** baixo se implementado depois da baseline de paridade (mesma regra de
  `EVO-SAAS-001`); alto se implementado durante a Fase 8/10 ainda em correção — risco de
  a suíte de QA de paridade V1/V2 ter que lidar com uma UI de troca de tema que já mudou
  de forma no meio do processo, sem necessidade (`INV-RMA-08` §8).
- **Dependências:** baseline de paridade completa e validada (Trilha A — Fases 1-10 +
  QA, `INV-RMA-05` §15), especificamente a Fase 8 (V1/V2) corrigida/commitada e a Fase
  10 (QA de paridade) concluída antes de qualquer código de V3.
- **Decisões adiadas:** forma exata de expressão dos tokens em Tailwind 4 (`@theme`
  puro vs. arquivo próprio); paleta de cor/tipografia específica do V3 (decisão de
  design de produto, não de arquitetura); desenho de UI do seletor de tema (3 opções);
  critério de qualidade/aceite que substitui "QA de paridade" quando não há legado para
  comparar; sequenciamento de quando alertas/crédito/relatórios/auditoria ganham view V3
  — `INV-RMA-08` §9.
- **Momento recomendado:** nenhuma linha de código de V3 (view, `v3.css`/`v3.js`,
  alteração real do enum `TemaPreferido`) antes da baseline de paridade da Trilha A
  estar validada — mesma estratégia C→A de `INV-RMA-07` §13, aplicada em
  `INV-RMA-08` §8.
- **Prioridade sugerida:** média, avaliar depois da Trilha A concluída — não é
  bloqueante de nenhuma outra evolução registrada (`EVO-SAAS-*`).
- **Fase:** pós-reconstrução (Trilha B).

## EVO-ARQUIVOS

### EVO-ARQ-001 — Anexos de arquivo no RMA (foto, laudo, NF digitalizada)

- **Origem:** investigação de evolução inspirada em módulos reais do CONAHOM
  (`~/github/online-conahom-laravel/app/{Armazenamento,Arquivos}/`), registrada em
  `docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-admin.md` (2026-08-25).
  **Confirmado nesta investigação: não há evidência de upload de arquivo/imagem em
  nenhum dos quatro documentos de arqueologia do legado** (`inventario-funcional-rma-v2.md`,
  `modelo-dominio-rma-legado.md`, `regras-negocio-rma-legado.md`,
  `inventario-banco-rma-v2.md`) — os blocos de nota fiscal (`nfcompra`/`nfvenda`/`chave`)
  são campos de texto, nunca upload. **Isto é evolução de produto pura, não
  reconstrução que faltou** — mesma categoria de `EVO-SAAS-001`/`EVO-UX-001`.
- **Problema observado:** a operação real de assistência técnica claramente se
  beneficiaria de anexar evidência visual/documental a um RMA (foto do produto com
  defeito, laudo técnico da assistência, digitalização da NF cujo texto/chave já é
  capturado hoje), mas nem o legado nem o RMA V3 atual (Fases 1-9) oferecem isso.
- **Legado:** nenhum campo de upload existe; NF é só texto/chave de 44 dígitos.
- **Evolução:** anexo de arquivo vinculado a um `Rma` — desenho recomendado em
  `INV-RMA-09` §A.4: entidade `AnexoDoRma` **dentro do módulo `app/Rma/` existente**
  (não um módulo `Arquivos`/`Armazenamento` central estilo CONAHOM — não há hoje um
  segundo módulo do RMA V3 com necessidade de upload que justifique um catálogo
  agregado), atrás de uma interface mínima de armazenamento (`guardar`/`baixar`/
  `existe`/`remover`), storage local no dia 1 com porta aberta para trocar por
  S3-compatible depois sem reescrita (`INV-RMA-09` §A.5). Convenção de caminho sem
  dado pessoal inspirada em `ContextoDoArquivo` do CONAHOM, sem portar a classe
  inteira. **Proporcionalidade avaliada explicitamente (`INV-RMA-09` §A.3): não
  copiar a arquitetura completa do CONAHOM** (2 fornecedores de storage + roteador,
  catálogo central agregando N módulos, versionamento de arquivo, verificação de
  drift dedicada) — é overengineering para o volume/caso de uso do RMA (poucos
  anexos por registro, um único módulo consumidor hoje).
- **Benefício:** evidência visual/documental do problema relatado, reduz dependência de
  descrição textual só, alinhado ao que a operação de assistência técnica já faz na
  prática fora do sistema (anexar foto/laudo por e-mail/WhatsApp, sem rastro no RMA).
- **Impacto:** médio — não muda regra de negócio nem ciclo de vida do RMA, é uma
  capacidade adicional sobre o agregado existente.
- **Complexidade:** baixa a média — interface de storage simples + uma entidade nova
  dentro do módulo já existente; cresce se/quando um segundo módulo consumidor
  aparecer (extração para módulo central, não decidida agora).
- **Risco:** baixo se implementado depois da baseline de paridade (mesma regra de
  `EVO-SAAS-001`/`EVO-UX-001`); overengineering se copiar a arquitetura do CONAHOM
  inteira sem evidência de volume que justifique (`INV-RMA-09` §A.3).
- **Dependências:** baseline de paridade completa e validada (Trilha A — Fases 1-10 +
  QA, `INV-RMA-05` §15). Isolamento de storage por tenant, se implementado depois de
  `EVO-SAAS-001`, reaproveita o mesmo Global Scope/`TenantContext` já decidido —
  `AnexoDoRma` entra na lista de entidades tenant-scoped sem mecanismo próprio
  (`INV-RMA-09` §A.6).
- **Decisões adiadas:** categorização de anexo por papel (foto/laudo/NF, enum simples
  se necessário); storage local vs. S3-compatible em produção real; momento de extrair
  para um módulo central de arquivos, se um segundo consumidor aparecer —
  `INV-RMA-09` §A "Pendências reais".
- **Momento recomendado:** nenhuma linha de código (migration, model, upload real)
  antes da baseline de paridade da Trilha A estar validada.
- **Prioridade sugerida:** média — não é bloqueante de nenhuma outra evolução
  registrada.
- **Fase:** pós-reconstrução (Trilha B).
- **Roadmap detalhado (arquivo-por-arquivo, OpenSpec completo):**
  `docs/produto/roadmap-evolucao-admin-arquivos.md` (Fase B, depois da Fase A —
  Configuração de admin) e
  `openspec/changes/anexos-de-rma/{proposal,design,tasks}.md`.

## EVO-DOMINIO

### EVO-DOM-001 — Relacionamento por FK real entre RMA e contrapartes

- **Origem:** `docs/legado/modelo-dominio-rma-legado.md` §Relacionamentos — zero FK em
  todo o schema legado.
- **Problema:** nomes duplicados/digitados diferente geram RMAs "órfãos" que não
  aparecem em nenhuma listagem de contraparte; sem dedução de duplicidade de cliente.
- **Legado:** tudo por comparação de string de nome.
- **Evolução:** entidade `Parceiro` polimórfica (papel: cliente/fornecedor/fabricante/
  assistência) com FK real, herdeira conceitual da tabela `assistencias(tipo)` que o
  próprio app 14.6.1 tentou introduzir e abandonou.
- **Nota:** esta correção pode ser tratada como parte da RECONSTRUÇÃO (não muda
  comportamento percebido pelo usuário, só a integridade dos dados) — decisão final cabe
  ao parecer/arquitetura (`INV-RMA-05`, ainda não escrito), não necessariamente backlog
  evolutivo puro.
- **Fase:** avaliar se entra na Trilha A (correção estrutural) ou B (mudança de produto).

### EVO-DOM-002 — Entidade Equipamento separada do RMA

- **Origem:** `docs/legado/modelo-dominio-rma-legado.md` — não existe hoje; cada retorno
  do mesmo item físico vira uma linha nova sem vínculo.
- **Evolução:** entidade `Equipamento` (fabricante/modelo/SN) reutilizável entre
  múltiplos RMAs, habilitando histórico de recorrência de defeito por item físico (não
  só por nome de contraparte, como o achado "boletins relacionados" já faz hoje de forma
  limitada).
- **Benefício:** inteligência de recorrência de defeito por equipamento específico, não
  só por fornecedor/fabricante.
- **Fase:** pós-reconstrução — muda a experiência percebida (nova tela/relação), não é
  correção invisível.

### EVO-DOM-003 — Política de garantia estruturada por fabricante

- **Origem:** RN-02 (regra MARKVISION hardcoded), `regras-negocio-rma-legado.md`.
- **Problema:** conhecimento de negócio valioso (regras de garantia por fabricante)
  preso em `if` no código-fonte em vez de configuração.
- **Evolução:** tabela de regras de garantia configurável por
  (fabricante × origem × janela de tempo), com o campo `politicadegarantia` (hoje texto
  morto) alimentando a regra de verdade em vez de só aparecer como leitura.
- **Fase:** pós-reconstrução.
- **Ver também:** `EVO-CONF-001`, mesma categoria geral de problema (regra de negócio
  hoje hardcoded/estática virando editável por admin) — recomendação registrada em
  `INV-RMA-09` §B.5 é que, quando `EVO-DOM-003` for especificado em detalhe, nasça
  dentro do mesmo módulo `App\Configuracao` proposto para `EVO-CONF-001`, em vez de
  reinventar um mecanismo de configuração próprio.

### EVO-CONF-001 — Área de configuração de admin (parâmetros de sistema editáveis)

- **Origem:** investigação de evolução inspirada no módulo real de configuração do
  CONAHOM (`~/github/online-conahom-laravel/resources/views/admin/configuracoes/`,
  `app/Comunicacao/{Dominio,Aplicacao}/`), registrada em
  `docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-admin.md` (2026-08-25). **Não é
  categoria nova (`EVO-CONFIG`) — entra em `EVO-DOMINIO` porque o problema real é de
  regra de negócio do domínio do RMA ficando hardcoded/só-`.env`, não uma capacidade de
  infraestrutura nova como `EVO-ARQ-001`** (justificativa completa em `INV-RMA-09`,
  seção final "Backlog evolutivo — itens criados").
- **Problema observado:** confirmado por leitura direta do código já implementado
  (Fases 1-9) — três parâmetros de negócio reais hoje hardcoded/só-`.env`, sem tela
  administrativa: (1) destinatário de notificação de conclusão
  (`app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php:17`,
  `config('rma.notificacoes.conclusao')`, só editável via `.env`/redeploy); (2)
  threshold de urgência R$75 (RN-12,
  `app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php:41`, literal `75.00` na query);
  (3) cidade "PORTO ALEGRE" hardcoded
  (`app/Rma/Aplicacao/ConsolidarFretePorCidade.php:21`, `private const CIDADE`, RN-16,
  Fase 7 — o próprio comentário do código já reconhece isso como candidato a
  configuração futura, sem agir, corretamente fora de escopo da Trilha A). Um quarto
  candidato (política de garantia por fabricante) já está registrado separadamente
  como `EVO-DOM-003`.
- **Legado:** nenhuma tela de configuração administrativa existe — parâmetros de
  negócio ficam presos em `if`/constante no código-fonte PHP.
- **Evolução:** módulo novo `App\Configuracao` (fronteira própria
  `Dominio/Aplicacao/Infraestrutura`), com o mesmo padrão real do CONAHOM (`INV-RMA-09`
  §B.1/§B.4): um objeto de valor `readonly` imutável e validado por configuração (ex.
  `ConfiguracaoDeNotificacaoDeRma`, `ConfiguracaoDeAlertaDeUrgencia`,
  `ConfiguracaoDeConsolidacaoDeFrete`), com autoria e data de publicação como campo do
  próprio objeto (`publicadaEm`/`publicadaPorNome`/`publicadaPorIdentificador`); um
  caso de uso `Publicar{Nome}` por configuração, dentro de transação; um objeto
  "efetivo" que resolve **publicado (se existir) ?? fallback do `.env`/constante
  atual** — preserva o comportamento atual como default seguro enquanto ninguém usa a
  tela, mesmo padrão `ConfiguracaoEfetivaDeEnvioDeEmail`/`ConfiguracaoDeComunicacao::
  padrao()` do CONAHOM. UI recomendada: **uma tela única** com os 3-4 campos reais, não
  um hub de múltiplas seções (`INV-RMA-09` §B.3) — proporcional ao volume de
  candidatos encontrados, evita estrutura vazia tipo "em breve".
- **Benefício:** parâmetros de negócio passam a ser editáveis por um administrador sem
  redeploy, com rastro de quem mudou e quando — reduz risco de regra de negócio
  desatualizada presa em código e melhora auditabilidade de mudança de política.
- **Impacto:** médio — não muda o resultado das regras em si (RN-12, RN-16 continuam
  as mesmas regras), muda quem pode ajustá-las e como.
- **Complexidade:** baixa a média — reaproveita metodologia já validada em produção
  pelo CONAHOM; escopo inicial é só 3-4 objetos de valor + casos de uso de publicação,
  não uma reconstrução de domínio.
- **Risco:** baixo se implementado depois da baseline de paridade; risco de
  overengineering se replicar as 6 telas/segredo separado do CONAHOM sem candidato
  real que justifique (`INV-RMA-09` §B.3).
- **Dependências:** baseline de paridade completa e validada (Trilha A — Fases 1-10 +
  QA, `INV-RMA-05` §15).
- **Decisões adiadas:** persistência exata (tabela por configuração vs. genérica
  chave/valor); autorização de quem pode publicar (qual `Papel`); UI hub vs. tela
  única (recomendação dada, não fechada); se `EVO-DOM-003` nasce dentro deste módulo
  ou como extensão do domínio `Rma`/`Fabricante` — `INV-RMA-09` §B.6.
- **Momento recomendado:** nenhuma linha de código (migration, model, view, config
  publicável) antes da baseline de paridade da Trilha A estar validada.
- **Prioridade sugerida:** média — não é bloqueante de nenhuma outra evolução
  registrada; conecta-se a `EVO-DOM-003` quando este for especificado.
- **Fase:** pós-reconstrução (Trilha B).
- **Roadmap detalhado (arquivo-por-arquivo, OpenSpec completo):**
  `docs/produto/roadmap-evolucao-admin-arquivos.md` (Fase A, primeira das duas
  evoluções detalhadas nesse documento) e
  `openspec/changes/configuracao-admin/{proposal,design,tasks}.md`.

## EVO-AUTOMACAO

### EVO-AUT-001 — Alertas automáticos de prazo/garantia (não só painel manual)

- **Origem:** as 10 regras de alerta (RN-01 a RN-10) hoje só aparecem se o operador abrir
  a home e olhar o painel.
- **Problema:** usuário depende de checar manualmente para descobrir urgência.
- **Evolução:** notificações configuráveis (e-mail/push) antes do vencimento de prazo/
  garantia, por regra.
- **Benefício:** redução de perda de prazo/janela de garantia.
- **Fase:** pós-reconstrução.

### EVO-AUT-002 — Automação do fluxo de crédito

- **Origem:** RN atual de crédito (`modelo-dominio-rma-legado.md` §Estoque/Financeiro) —
  hoje `solucao='GERADO CREDITO'` e o checkbox `creditodisponivel` são preenchidos
  manualmente, sem vínculo automático entre os dois.
- **Evolução:** transição de estado de crédito automatizada e auditável.
- **Fase:** pós-reconstrução.

## EVO-RELATORIOS

### EVO-REL-001 — Relatórios reais (não impressão via Ctrl+P)

- **Origem:** `inventario-tecnico-15.9.7.md` — os 3 relatórios legados geram HTML para
  impressão manual no navegador, sem PDF server-side.
- **Evolução:** geração de PDF/export real, filtros configuráveis.
- **Fase:** pós-reconstrução.

### EVO-REL-002 — Inteligência de recorrência de defeito por fornecedor/fabricante

- **Origem:** achado "boletins de defeito relacionados" (RN correlata), 15.8.1 já lista
  todos os RMAs do mesmo destinatário/fabricante/fornecedor no rodapé da tela — sem
  `LIMIT`, sem agregação, é uma lista bruta.
- **Evolução:** dashboard de taxa de defeito por fabricante/fornecedor, com paginação e
  agregação real.
- **Fase:** pós-reconstrução.

## EVO-SEGURANCA

Nota: correções de segurança que **não mudam comportamento percebido** (hashing forte de
senha, prepared statements em 100% das queries, CSRF token, escoping de include sem
LFI) são tratadas como parte da **Trilha A** (reconstrução), não backlog evolutivo — o
"regra de ouro" do usuário já classifica segurança como eixo a reconstruir, não a
evoluir depois. Os itens abaixo são melhorias que **vão além** de restaurar o
comportamento original com segurança correta.

### EVO-SEG-001 — Autenticação multifator / SSO

- **Origem:** ausência total de 2FA no legado (era mono-empresa, um único nível de
  risco).
- **Evolução:** MFA opcional, especialmente relevante quando virar multiempresa
  (EVO-SAAS-001).
- **Fase:** pós-reconstrução, provavelmente junto com EVO-SAAS-001.

## EVO-AUDITORIA

### EVO-AUD-001 — Histórico de transição com diff real (não snapshot)

- **Origem:** tabela `modificacao` do legado grava um snapshot desnormalizado dos campos
  no momento da edição, não um diff nem a ação específica realizada.
- **Evolução:** histórico estruturado com "de → para" por campo, e nome da ação
  (encaminhar/concluir/etc.), não só um retrato do estado final.
- **Nota:** ter histórico forte e rastreável já é requisito da Trilha A (regra de ouro do
  usuário: "preservar rastreabilidade"); o que fica para a Trilha B é a **melhoria** do
  formato (diff em vez de snapshot), não a existência do histórico em si.
- **Fase:** avaliar se o diff estruturado entra na Trilha A (é auditoria melhor, não
  produto novo) ou B — provável candidato à Trilha A por ser invisível ao usuário final.

## EVO-PERFORMANCE

### EVO-PERF-001 — Eliminar as ~25 queries full-scan da home

- **Origem:** achado do agente de arqueologia do 15.8.1 — a home dispara os 10 alertas +
  14 widgets do menu direito, cada um com `SELECT` sem `LIMIT` sobre `bd` inteira,
  acumulando result sets em memória (statements nunca fechados, `close()` inalcançável
  após `return`).
- **Evolução:** paginação, cache, índices adequados, fechamento correto de conexões —
  parte natural de uma reconstrução em Laravel/Eloquent, não é feature de produto nova.
- **Fase:** Trilha A (é reconstrução com tecnologia atual, não evolução de produto) —
  registrado aqui só para não perder o achado até a arquitetura ser decidida.

## EVO-IA

### EVO-IA-001 — Sugestão de diagnóstico/prioridade assistida por IA

- **Origem:** nenhuma automação de triagem existe no legado — toda classificação
  (defeito, prioridade, garantia) é manual.
- **Evolução:** IA sugere categoria/prioridade/resposta ao cliente; usuário confirma;
  alteração fica auditada (nunca decide sozinha).
- **Fase:** pós-reconstrução, fase avançada.

---

## Como este documento é usado

Cada achado novo durante a arqueologia que pareça uma oportunidade de melhoria (não uma
regra a preservar) entra aqui, imediatamente, com origem concreta. Revisão e priorização
formal só acontecem depois que a Trilha A (reconstrução fiel) estiver validada — ver
`PLANO-ATAQUE.md`.
