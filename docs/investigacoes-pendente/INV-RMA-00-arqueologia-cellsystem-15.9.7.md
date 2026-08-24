# INV-RMA-00 — Arqueologia do CellSystem RMA 15.9.7 (investigação viva)

Iniciada: 2026-08-24. Status: **EM ANDAMENTO**. Este é o documento normativo da
arqueologia — a conversa que o produziu não é fonte de verdade a partir de agora. Toda
descoberta nova entra aqui (ou nos documentos especializados referenciados) antes de
qualquer outra ação.

**Nomenclatura oficial** (definida pelo autor original do sistema, 2026-08-24): **RMA V2
FINAL** = `15.9.7` · **TEMA V1** = `14.6.1` · **TEMA V2** = `15.8.1` · **RMA V3** = o
sistema sendo construído nesta iniciativa. Ver
`docs/legado/matriz-comparacao-apps-rma.md` para a árvore completa.

Tags: **[CONFIRMADO-TEMA-V2]** (= 15.8.1) · **[CONFIRMADO-TEMA-V1]** (= 14.6.1) ·
**[CONFIRMADO-COMPARTILHADO]** (camada `metodo.php`/`conexao.php`, comum aos dois temas)
· **[CONFIRMADO-BANCO]** (schema do dump) · **[CONFIRMADO-RELATO-USUARIO]** (testemunho
direto do autor original) · **[BUG-LEGADO]** · **[CODIGO-MORTO]** · **[DÚVIDA]** ·
**[EVO]** (aponta para `backlog-evolutivo.md`).

## Documentos especializados (fonte de detalhe — não duplicado aqui)

| Documento | Conteúdo |
|---|---|
| `docs/legado/inventario-tecnico-15.9.7.md` | Backup: localização, SHA-256, estrutura do tar, tecnologias, bibliotecas (A/B/C/D/E), banco/dumps, código morto/duplicado, arquivos sensíveis, cobertura de leitura |
| `docs/legado/matriz-comparacao-apps-rma.md` | Arquitetura multi-app 15.9.7, comparação funcional 14.6.1 × 15.8.1, respostas às 10 perguntas do usuário sobre os apps |
| `docs/legado/cronologia-rma.md` | Pistas de datação, marcadas como hipótese — não mais tratadas como linhagem provada |
| `docs/legado/modelo-dominio-rma-legado.md` | Entidades, campos, relacionamentos — o domínio como realmente existia |
| `docs/legado/regras-negocio-rma-legado.md` | 21 regras de negócio (RN-01 a RN-21) com origem, condição, consequência, evidência, situação |
| `docs/produto/backlog-evolutivo.md` | Oportunidades de melhoria (Trilha B), separadas da reconstrução fiel |

## 1. Identidade do backup — confirmação inequívoca

**[CONFIRMADO-COMPARTILHADO]** SHA-256 `d3811daa79087e04927613505069a7a81221691d93cca9ee37b7f0096ba354df`
(idêntico no arquivo original em `~/Downloads` e na cópia de trabalho). Identidade
confirmada por `app/15.9.7/index.php` (`<meta name="description" content="Sistema de
RMA">`, domínio `cellsystem.com.br`) e pelo nome do schema (`cellsyst_rma`/
`scripti2_cellsyst_rma`) batendo com o nome do dump. Detalhe completo em
`inventario-tecnico-15.9.7.md`.

## 2. Arquitetura de temas do RMA V2 (15.9.7)

**[CONFIRMADO-RELATO-USUARIO] + [CONFIRMADO-COMPARTILHADO]** RMA V2 (15.9.7) é o
produto no estado final capturado pelo backup. TEMA V1 (14.6.1) foi construído primeiro;
TEMA V2 (15.8.1) depois, como segunda geração da experiência; TEMA V1 foi **preservado**,
não substituído — daí a coexistência:

```
CELLSYSTEM RMA V2 — 15.9.7
├── TEMA V1 — 14.6.1   (2 camadas: page/+post/, tela de login própria)
├── TEMA V2 — 15.8.1   (4 camadas: page/+pp/+subp/+inc/, tela de login própria,
│                        implementação de referência das regras mais sofisticadas)
└── camada compartilhada (app/15.9.7/metodo.php, conexao.php) — as 10 regras de
    alerta, auditoria, controle de permissão, políticas de garantia em cascata
```

- **`usuario.app`** — coluna que persiste a escolha do usuário entre os dois temas.
- **`trocarapp.php`** — mecanismo de alternância bidirecional (não migração).

Não são três aplicações independentes nem uma linhagem onde um substitui o outro — são
duas experiências do mesmo produto, coexistindo por decisão de produto (confirmado pelo
autor original).

**[CONFIRMADO-COMPARTILHADO]** Os dois apps operam sobre o **mesmo banco, mesma tabela
`bd`, mesmo `conexao.php`** — não são sistemas com dados separados.

**[CONFIRMADO-COMPARTILHADO]** Dependência assimétrica: `14.6.1/inc/startpage.php`
inclui templates de `../15.8.1/subp/listar_*.php` — 14.6.1 não tem implementação própria
das 10 regras de alerta, reaproveita as de 15.8.1. Nenhuma dependência no sentido
inverso encontrada.

**[HIPOTESE-HISTORICA]** Qual app é "clássico" e qual é "moderno", e se há ordem
cronológica real entre eles — não provado por comentário/changelog explícito, só
inferido por estrutura de código e pela dependência assimétrica. Ver
`matriz-comparacao-apps-rma.md` §Hipótese histórica.

**[DÚVIDA]** Qual app era o *default* de `usuario.app` na época do backup — não
verificado (exigiria abrir dados reais de usuário, decisão deliberada de não fazer sem
necessidade clara).

Matriz funcional completa (14 linhas de comparação) em `matriz-comparacao-apps-rma.md`.

## 3. Domínio — resumo (detalhe completo em `modelo-dominio-rma-legado.md`)

**[CONFIRMADO-COMPARTILHADO]** Entidade central `bd` (o RMA), ~60 colunas, chave de
negócio `numero` gerado em PHP (não AUTO_INCREMENT real). Sem entidade `Equipamento`
separada — atributos do produto são colunas diretas de `bd`.

**[CONFIRMADO-15.8.1]** Quatro entidades de contraparte (`cliente`, `fabricante`,
`fornecedor`, `assistencia_tecnica`), relacionadas a `bd` **por nome, sem FK**.
"Destinatário" é polimórfico, resolvido em cascata (assistência → fornecedor →
fabricante). "Autorizada" é **[CODIGO-MORTO]** — alias de `assistencia_tecnica`, sem
rota alcançável.

**[CONFIRMADO-14.6.1]** Tabela extra `assistencias(tipo)` tentando unificar as 4
entidades — usada só por 14.6.1 (`menujs-right/fornecedores.php`), com inconsistência
interna (política de garantia em cascata não a inclui). **Abandonada** na versão de
referência (15.8.1).

**[CONFIRMADO-COMPARTILHADO]** `usuario` com 5 níveis de permissão
(`-1`/`1`/`2`/`3`/`4`), idênticos nos dois apps — hierarquia estável, evidência mais
sólida do levantamento inteiro.

**[CONFIRMADO-BANCO]** `bd.empresa` — 6 valores de texto livre, embrião de multiempresa
sem qualquer isolamento. **[EVO]** → `EVO-SAAS-001`.

## 4. Fluxo — status × solução (duas máquinas independentes)

**[CONFIRMADO-COMPARTILHADO]** `status`: `entrada → recebido → encaminhado → concluido`
+ `arquivado` (paralelo, reabrível) + `retornou` **[CODIGO-MORTO]** (rota existe, tela
vazia, nunca gravado por nenhum app).

**Rastreabilidade da regra "arquivar":**
- 14.6.1: **[BUG-LEGADO]** `post/arquivar.php` chama classe inexistente → Fatal Error.
- 15.8.1: **[CONFIRMADO-15.8.1]** funcional, `status='arquivado'`, reabrível.
- **CONCLUSÃO NORMATIVA:** reconstruir com a semântica de 15.8.1 (referência); bug do
  14.6.1 não é motivo para descartar a funcionalidade.

**Rastreabilidade da regra "retornar p/ entrada" (rollback):**
- 14.6.1: **[CONFIRMADO-14.6.1]** só no mesmo dia do encaminhamento, ou `permissao==4`.
- 15.8.1: **[CONFIRMADO-15.8.1]** regra idêntica; bug cosmético só aqui (comparação com
  string mal formatada numa das duas cópias do select).
- **CONCLUSÃO NORMATIVA:** regra estável nos dois apps — comportamento intencional.

`solucao`: 17 valores (`CASO SOLUCIONADO`, `TROCA DO PRODUTO`, `GERADO CREDITO`,
`PROCON`, `SEM GARANTIA` etc.), completamente ortogonal ao `status`. Domínio completo em
`modelo-dominio-rma-legado.md`.

## 5. As 10 regras de alerta — cadeia funcional completa

Todas em **[CONFIRMADO-COMPARTILHADO]** (vivem em `metodo.php`, herdadas por ambos os
apps). Documentadas integralmente, com a cadeia DADO→CÁLCULO→CONDIÇÃO→CLASSIFICAÇÃO→
APRESENTAÇÃO→AÇÃO pedida, em `regras-negocio-rma-legado.md` (RN-01 a RN-10). Achado
central: **nem todas filtram de verdade no SQL** — as que dependem de cálculo de data
(prazo, garantia) trazem o conjunto bruto e filtram em PHP linha a linha; 4 das 10 têm
`num_rows` "mentiroso" (mostram tabela vazia em vez de "nenhum item").

Duas regras confirmadas **só em 15.8.1**, ainda não verificadas em 14.6.1:
- **RN-02** (não vai dar garantia) tem um ramo hardcoded específico para o fabricante
  MARKVISION + fornecedor "Receita" — a peça de conhecimento tácito mais valiosa do
  levantamento.
- **RN-12** (`right_urgente()`, threshold de R$ 75) — vive em `15.8.1/banco.php`, não na
  camada compartilhada.

## 6. Regras de negócio "escondidas em camadas" — 21 regras catalogadas

RN-01 a RN-21 em `regras-negocio-rma-legado.md`, incluindo:
- **[BUG-LEGADO] RN-17** — `marcarestoque` computado corretamente e depois **sobrescrito**
  pelo POST bruto do formulário — provável bug de maior impacto operacional (produtos de
  cliente escapam de todos os alertas de prazo legal se o checkbox não for desmarcado).
- **[BUG-LEGADO] RN-18** — módulo de Créditos "pendentes/usados/disponíveis" quebrado
  (rotas existem, arquivos de destino não).
- **[BUG-LEGADO] RN-21** — troca de senha pelo próprio usuário nunca funcionou (SQL
  inválido em `alterar_senha()`).
- RN-13 a RN-16 (normalização HGST→Hitachi, cascata de `origem`, `snretorno` anti-fraude,
  consolidação de frete Porto Alegre) — todas **[DÚVIDA]** quanto à presença em 14.6.1.

## 7. Segurança (sem reprodução de valores)

**[CONFIRMADO-COMPARTILHADO]** Credencial de banco em texto plano (`conexao.php`);
hashes SHA1 sem salt (`Key1461`/`Key1581`); sem CSRF em nenhum formulário de nenhum app;
dumps com dados reais de cliente/log não abertos além de schema+contagem.

**[CONFIRMADO-15.8.1]** SQL Injection confirmada e explorável em `pesquisar()` (versão
seguray com prepared statements existe **comentada** no próprio código, abandonada);
Local File Inclusion via `?subp=` sem whitelist; senha em texto plano enviada por e-mail
em dois fluxos (`enviar_saudacao`, `enviar_senha`); enumeração de usuário no login por
redirect diferenciado.

**[CONFIRMADO-14.6.1]** Segredo de convite fixo para autocadastro (hash SHA1 hardcoded);
SQL Injection em `page/detalhes.php` (`WHERE numero=$numero` sem aspas nem bind); Local
File Inclusion via `?page=`/`?assistencia=` sem whitelist.

Catálogo completo por app nos respectivos documentos de arqueologia (relatórios
originais dos agentes, incorporados nos documentos especializados acima).

## 8. Cobertura de leitura (transparência)

Ver `inventario-tecnico-15.9.7.md` §Cobertura para a lista completa de arquivos lidos
integralmente, parcialmente e não lidos. Resumo: núcleo funcional de 15.8.1 (banco.php,
page/rma.php, os 10 subp/listar_*, salvar_rma/novo_rma) lido integralmente; 14.6.1 lido
quase integralmente (~40 arquivos); 14.10.2 lido integralmente (28 arquivos, confirmado
protótipo sem banco); bibliotecas de terceiros (`js/`, `framework/`, `lib/`) não lidas em
profundidade — baixa prioridade, comportamento de terceiro bem conhecido.

## 9. Dúvidas abertas (para próxima rodada de arqueologia, ainda não retomada)

1. RN-12 a RN-17, RN-18, RN-21 — presença/ausência de implementação equivalente em
   TEMA V1/14.6.1 (comparação linha a linha `14.6.1/post/*` vs `15.8.1/pp/*` ainda não
   feita).
2. ~~Ordem cronológica entre os temas~~ **RESOLVIDA** por relato do autor original — ver
   `matriz-comparacao-apps-rma.md` e `cronologia-rma.md`.
3. Default de `usuario.app` na época do backup.
4. Se `RN-11` (classificação visual de inconformidade) tem equivalente exato em TEMA V1.
5. Biblioteca AdminLTE — confirmar se skin está realmente ativa (nenhuma classe
   `skin-*` referenciada no HTML lido até agora) antes de classificar A/B/C/D/E.
6. Onde exatamente Lightbox2 é usado no fluxo real do RMA (não confirmado).

## 10. Requisito formal — migração de banco V2 → V3

**[CONFIRMADO-RELATO-USUARIO], requisito de produto, não ferramenta temporária.** A V3
precisa ser capaz de migrar um banco legítimo do CellSystem RMA V2 (schema documentado
em `modelo-dominio-rma-legado.md`) preservando histórico e semântica — não é uma
conveniência de desenvolvimento, é uma das três provas de que a V3 é continuação real do
produto (ver §11).

### Regra de evolução do banco — baseline antes de melhoria estrutural

**[CONFIRMADO-RELATO-USUARIO]** A ordem é obrigatória e não pode ser invertida:

```
V2 original → inventário do schema → migrador V2→V3 → paridade de dados
→ paridade funcional → baseline V3 validada → melhorias estruturais incrementais
```

A V3 **não começa "idealizada"** — o schema inicial da V3 é o que o migrador consegue
produzir preservando 100% do dado e da semântica do legado (mesmo que isso signifique
manter, na baseline, uma modelagem imperfeita como relação por nome em vez de FK). Só
**depois** de provar a baseline (mesma quantidade de RMAs, mesmos clientes/fornecedores
associados, nenhuma relação perdida) é que uma melhoria estrutural (ex.: introduzir FK
real) pode ser feita — e cada uma delas precisa do template abaixo, rastreável:

| Campo | Conteúdo |
|---|---|
| **ANTES** | como o legado representava o conceito |
| **PROBLEMA** | qual limitação real existia |
| **DEPOIS** | como a V3 passa a representar |
| **MIGRAÇÃO** | como os dados existentes são transformados |
| **COMPATIBILIDADE** | como o comportamento anterior continua funcionando |
| **TESTE** | como se prova que não houve perda |

Exemplo já antecipado (ainda não implementado, só ilustrativo do padrão a seguir):
ANTES `bd.fornecedor` = nome em texto livre → PROBLEMA sem FK, duplicidade por
digitação → DEPOIS `supplier_id` estruturado → MIGRAÇÃO resolve nomes existentes, cria
entidades, relaciona, reporta ambiguidade → COMPATIBILIDADE nenhuma (é mudança interna,
invisível ao usuário) → TESTE mesma quantidade de RMAs, mesmos fornecedores associados,
nenhuma relação silenciosamente perdida.

Estratégia geral (a detalhar em `INV-RMA-06`, ainda não escrito):
1. Mapa completo **LEGADO → V3** por tabela/campo, nascido da arqueologia já feita, não
   inventado (ex.: `bd.numero` → identificador de negócio equivalente; `bd.cliente`
   (string) → `Cliente` deduplicado e relacionado por FK; `bd.destinatario` polimórfico →
   relação de parceiro adequada; `usuario` → `users` + papel; `modificacao` → auditoria).
2. Migrador que **preserva dado e semântica, não a estrutura ruim** — deduplica
   parceiros por nome, cria registros corretos, relaciona por FK, preserva o valor
   original quando houver ambiguidade, gera relatório de inconsistência (não "importa e
   torce").
3. Migração **auditável**: contagens de entrada vs. migrado vs. alerta, por entidade
   (RMAs, clientes, fornecedores, usuários, relacionamentos ambíguos, dados não
   reconhecidos).
4. Valores legados fora do domínio moderno (`origem=Rolo`, `prioridade=urgente`,
   `status=retornou`, `empresa=R A`) não são descartados silenciosamente — decisão
   explícita caso a caso (normalização, enum legado, campo original preservado,
   warning).
5. Dados sensíveis do backup (cadastro real de cliente, hash de senha, log de acesso)
   nunca entram no Git — só fixtures anonimizadas/sintéticas para testes versionados.
6. Teste de migração dedicado: banco V2 → migrador → banco V3 → validação de contagens e
   integridade por entidade.

## 11. Critério de sucesso da V3 — três paridades

Não basta "as telas parecerem parecidas". A V3 só é considerada reconstrução fiel quando
comprovar:

- **Paridade funcional** — mesmas regras e fluxos essenciais (RN-01 a RN-21, máquina de
  estados, permissões).
- **Paridade visual** — TEMA V1 e TEMA V2 reconhecíveis e fiéis, ambos coexistindo na V3
  (não um "modo claro/escuro" genérico — são as duas experiências históricas reais).
- **Paridade de dados** — um banco legítimo do RMA V2 pode ser migrado para a V3
  preservando histórico e semântica (§10).

## 12. Bug legado × comportamento legado — critério de reprodução

Fidelidade não significa copiar bug. Para cada comportamento estranho encontrado,
classificar: **comportamento intencional** (reproduzir), **bug** (corrigir, preservando
a intenção funcional comprovada — ex.: "arquivar" deve funcionar corretamente na V3
mesmo estando quebrado no TEMA V1), **dívida técnica** (corrigir como parte da
reconstrução, é eixo de engenharia, não de produto), **código morto** (não reconstruir),
**inconclusivo** (`[DÚVIDA]`, não decidir sem mais evidência).

## 13. Próxima etapa

Aguardando decisão do usuário sobre retomar a arqueologia (resolver as dúvidas restantes
da seção 9, especialmente as regras `[BUG-LEGADO]` de maior impacto) ou avançar direto
para `INV-RMA-04` (interface/identidade visual dos dois temas) e o parecer consolidado
com o que já está maduro. Ver `PLANO-ATAQUE.md` para o checkpoint ARQ-RMA corrente.
