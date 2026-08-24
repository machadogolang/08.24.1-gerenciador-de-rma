# Matriz de comparação dos temas do CellSystem RMA V2 (15.9.7)

Data: 2026-08-24. **Nomenclatura oficial (definida pelo autor original do sistema,
2026-08-24):**

| Termo oficial | Corresponde a |
|---|---|
| **RMA V2 FINAL** | `15.9.7` — estado final conhecido da segunda geração do produto |
| **TEMA V1** | `14.6.1` — primeira experiência construída |
| **TEMA V2** | `15.8.1` — segunda experiência construída; TEMA V1 foi preservado, não removido |
| **RMA V3** | o sistema novo sendo construído agora nesta iniciativa |

Estrutura correta: **RMA V2 (15.9.7) é o produto; dentro dele coexistem as duas
experiências visuais TEMA V1 (14.6.1) e TEMA V2 (15.8.1), alternáveis pelo usuário, mais
uma camada de regras/infraestrutura compartilhada.** Não são três aplicações
independentes.

```
CELLSYSTEM RMA V2 — 15.9.7
├── TEMA V1 — 14.6.1
├── TEMA V2 — 15.8.1
└── regras/infraestrutura/dados compartilhados (metodo.php, conexao.php)
```

A ordem de construção (TEMA V1 antes de TEMA V2, TEMA V1 preservado quando TEMA V2 foi
criado) é **[CONFIRMADO-RELATO-USUARIO]** — relato direto do autor original do sistema,
tratado como evidência válida (mais forte que a inferência estrutural que eu tinha feito
a partir do código, que só mostrava coexistência, não ordem). Esta seção usava
anteriormente `[HIPOTESE-HISTORICA]` para a ordem cronológica — mantido abaixo só para
registrar que a inferência por código *sozinha* não provava a ordem, mas o relato do
autor agora resolve a dúvida.

Tags: **[CONFIRMADO-CODIGO]** (lido no código) · **[CONFIRMADO-RELATO-USUARIO]**
(testemunho direto do autor original) · **[DÚVIDA]** (pergunta em aberto, sem evidência
suficiente ainda).

## Arquitetura multi-app da versão 15.9.7

```
CELLSYSTEM RMA 15.9.7  (container — app/15.9.7/)
├── conexao.php          → credencial única de banco (compartilhada)
├── metodo.php            → funções compartilhadas: as 10 regras de alerta,
│                           registra_modificacao(), registra_tentativa(),
│                           pms(), políticas de garantia em cascata,
│                           app() / trocarapp()
├── index.php             → tela de login do container; submete para
│                           15.8.1/pp/senha.php; oferece link para abrir 14.6.1
├── trocarapp.php         → alterna usuario.app entre "14.6.1" e "15.8.1"
├── pattern/               → identidade visual por app: 14.6.1.css/js,
│                           15.8.1.css/js, 15.9.7.css/js (15.9.7 = skin do
│                           próprio container/login, não um terceiro app)
├── 14.6.1/                → app/tema "clássico" — 2 camadas (page/ + post/)
└── 15.8.1/                → app/tema "rico" — 4 camadas (page/+pp/+subp/+inc/)
```

**[CONFIRMADO]** `usuario.app` (coluna da tabela `usuario`, `varchar(11) NOT NULL`, sem
`DEFAULT` definido no schema) guarda a escolha persistida do usuário. `trocarapp.php` lê
a escolha atual via `app($email)` e grava a oposta via `trocarapp($novoapp,$email)` —
mecanismo de alternância bidirecional, não uma migração unidirecional.

**[CONFIRMADO]** Os dois apps operam sobre **a mesma tabela `bd`, o mesmo `conexao.php`,
o mesmo schema** — não são dois sistemas com dados separados, são duas interfaces sobre o
mesmo RMA. Confirmado porque `14.6.1/banco.oo.php` e `15.8.1/banco.php` fazem `SELECT`/
`UPDATE`/`INSERT` nas mesmas tabelas (`bd`, `usuario`, `cliente`, `fabricante`,
`fornecedor`, `assistencia_tecnica`, `log`, `modificacao`) usando o mesmo `$conexao`
(incluído de `../../conexao.php` em ambos).

**[CONFIRMADO]** Existe dependência **assimétrica**: `14.6.1/inc/startpage.php` faz
`include("../15.8.1/subp/listar_*.php")` para os 10 alertas — ou seja, o app 14.6.1
**não tem implementação própria** dessas 10 regras, ele reaproveita os templates de
15.8.1. Não foi encontrada nenhuma dependência no sentido inverso (15.8.1 incluindo algo
de dentro de 14.6.1). Isso não prova qual é "mais antigo", mas prova que **15.8.1 é a
implementação de referência das 10 regras de alerta**, e 14.6.1 depende dela.

## Matriz de comparação por funcionalidade

| Funcionalidade | Camada compartilhada (15.9.7/metodo.php) | TEMA V1 (14.6.1) | TEMA V2 (15.8.1) | Diferença | Conclusão | Evidência |
|---|---|---|---|---|---|---|
| Login/sessão | `conexao.php` comum | `inc/signin.php`, chave de sessão própria | `inc/signin_padrao.php` (órfão, `login.php` redireciona sem renderizar), chave de sessão `START1597` | Fluxo de entrada diferente por app | Cada app tem tela de login própria; container 15.9.7 tem uma terceira tela de login que decide para qual app enviar a autenticação | `15.9.7/index.php` → `15.8.1/pp/senha.php`; `14.6.1/inc/signin.php` |
| Camadas de código | — | `page/` + `post/` (2 camadas) | `page/` + `pp/` + `subp/` + `inc/` (4 camadas) | 15.8.1 tem separação mais granular (listar/editar/apagar/ver em arquivos distintos) | **[CONFIRMADO]** diferença estrutural real, não é só tema visual — a organização do código é distinta | Estrutura de diretórios de cada app |
| As 10 regras de alerta (prazo/garantia/NF/SN) | **Sim — implementadas em `metodo.php`** | Reaproveita via include direto da pasta de 15.8.1 (não tem versão própria) | Implementação de referência (`subp/listar_*.php` como view + `metodo.php` como query) | Nenhuma — é a mesma regra para os dois apps, porque é a mesma função PHP | **[CONFIRMADO]** regra pertence à camada compartilhada, não a um app específico | `14.6.1/inc/startpage.php` inclui `../15.8.1/subp/listar_*.php` |
| Regra MARKVISION / Receita | Em `metodo.php` (`listar_naovaidargarantia`) | Herdada via mesmo include | Idem | Nenhuma | **[CONFIRMADO]** regra da camada compartilhada | `metodo.php:325-336` |
| Threshold R$ 75 (urgência) | `banco.php:777` (`right_urgente()`) — **não está em `metodo.php`, está dentro de `15.8.1/banco.php`** | **[DÚVIDA]** não verificado se 14.6.1 tem função equivalente | Confirmado | Possível diferença funcional real (não só de tema) | **[HIPOTESE-HISTORICA]** pode ser um recurso exclusivo do app 15.8.1, não compartilhado — precisa verificar `14.6.1/banco.oo.php` por uma função equivalente antes de afirmar | `15.8.1/banco.php:777-837` |
| Modelo de contraparte (cliente/fabricante/fornecedor/assistência) | Tabelas compartilhadas (mesmo banco) | Tem também a tabela extra `assistencias(tipo)` sendo referenciada por `menujs-right/fornecedores.php` | Não referencia `assistencias` — só as 4 tabelas normalizadas; arquivos `*_autorizada*` existem mas sem rota (código morto) | **Diferença funcional real**, não só visual: os dois apps leem cadastros de formas diferentes | **[CONFIRMADO]** diferença de comportamento entre os apps, coexistindo sobre o mesmo banco — um usuário no app 14.6.1 vê uma origem de dados de "fornecedores" ligeiramente diferente da que vê no app 15.8.1 | `14.6.1/menujs-right/fornecedores.php` vs. ausência de referência em `15.8.1` |
| Status do RMA (entrada/recebido/encaminhado/concluido/arquivado) | Nomes e semântica idênticos | Transições implementadas em `banco.oo.php`, com bug: `post/arquivar.php` chama classe inexistente (`Fatal Error`) | Transições implementadas em `banco.php`, funcionais | **Diferença de robustez**, não de conceito — o *nome* dos estados e a *máquina* são as mesmas em ambos | **[CONFIRMADO]** mesma máquina de estados conceitual nos dois apps; "arquivar" funciona em 15.8.1 e está quebrado em 14.6.1 nesta fotografia do backup | `14.6.1/banco.oo.php:446-455` vs `15.8.1/banco.php:448-455` |
| Rollback "retornar p/ entrada" | — | Permitido só no mesmo dia do encaminhamento, ou `permissao==4` | Regra idêntica | Nenhuma (bug cosmético só em 15.8.1: comparação com string com espaço à esquerda numa das duas cópias do select) | **[CONFIRMADO]** mesma regra nos dois apps | `14.6.1/page/detalhes.php:319` vs `15.8.1/page/rma.php:149,656` |
| Permissões (`-1/1/2/3/4`) | Tabela `usuario` compartilhada | Guardas idênticas (`pms()`, `permissao>1` para gravar, `==3` para deletar, `==4` para rollback) | Guardas idênticas | Nenhuma confirmada | **[CONFIRMADO]** mesmo modelo de permissão nos dois apps — faz sentido, é a mesma tabela `usuario` | `14.6.1/banco.oo.php` vs `15.8.1/banco.php` |
| Crédito (`solucao='PENDENTE CREDITO'` → `creditodisponivel`) | — | Presente (`page/aguardandocredito.php`) | Presente (`page/credito.php`, `page/creditos.php` — este último quebrado, inclui arquivo inexistente) | Nomenclatura de tela diferente, mesmo conceito | **[CONFIRMADO]** mesmo fluxo de crédito conceitual, module de "creditos" (plural) está quebrado só em 15.8.1 nesta fotografia | ambos os apps |
| Identidade visual | `pattern/{14.6.1,15.8.1}.css/js` — arquivos **distintos e não intercambiáveis** | CSS/JS próprio (207 linhas CSS) | CSS/JS próprio (905 linhas CSS — bem mais rico: classes de estado `TrInconformidade`/`TrUrgente`/`TrSemGarantia`/`TrZebrada`) | **Diferença visual real e significativa** | **[CONFIRMADO]** os dois apps têm aparência diferente entre si; 15.8.1 tem o sistema de destaque visual de linha mais elaborado | `pattern/14.6.1.css` (207 linhas) vs `pattern/15.8.1.css` (905 linhas) |

## Perguntas do usuário — respostas com o que já temos, sem ler mais código agora

1. **O que muda entre 14.6.1 e 15.8.1?** Estrutura de código (2 vs 4 camadas), tela de
   login, CSS/JS próprios, e ao menos uma diferença de modelo de dados confirmada
   (tabela `assistencias` só é usada pelo 14.6.1). As 10 regras de alerta são
   compartilhadas (14.6.1 as reaproveita via include cruzado).
2. **São apenas temas/interface?** **Não só.** Há pelo menos duas diferenças funcionais
   reais confirmadas: (a) a tabela `assistencias` sendo usada só por 14.6.1; (b)
   "arquivar" quebrado em 14.6.1 e funcional em 15.8.1 (embora isso possa ser só um bug
   pontual desta fotografia, não uma diferença de design).
3. **Diferenças funcionais existem?** Sim, ver item 2.
4. **Quais regras pertencem à camada compartilhada?** As 10 de alerta, geração de
   número de RMA (embora com código duplicado, não uma função só), auditoria
   (`registra_modificacao`/`registra_tentativa`), políticas de garantia em cascata,
   controle de permissão (`pms()`).
5. **Quais pertencem a cada app especificamente?** Modelo de contraparte (tabela
   `assistencias` só em 14.6.1); threshold de R$75 — **[DÚVIDA]**, só confirmado em
   15.8.1, não verificado se existe equivalente em 14.6.1.
6. **Usam o mesmo banco?** **[CONFIRMADO]** sim.
7. **Manipulam a mesma tabela `bd`?** **[CONFIRMADO]** sim.
8. **Um era "clássico" e outro "novo"?** **[CONFIRMADO-RELATO-USUARIO]** sim — TEMA V1
   (14.6.1) é a experiência clássica, TEMA V2 (15.8.1) é a segunda geração, preservada
   junto com a primeira. Consistente com a estrutura de código (2 camadas vs 4) e com a
   dependência assimétrica (TEMA V1 usa include de dentro de TEMA V2, nunca o contrário).
9. **Qual era o default na época do backup?** **[DÚVIDA]** — não verificado. Verificar
   exigiria abrir a coluna `app` de linhas reais da tabela `usuario` no dump, o que não
   foi feito porque envolveria ler dados pessoais reais (nome/e-mail de usuários) sem
   necessidade — decisão deliberada de não abrir. Se for necessário resolver esta dúvida,
   a forma seguem seria contar `SELECT app, COUNT(*) FROM usuario GROUP BY app` sem expor
   nome/e-mail — ainda não feito.
10. **Por que o usuário podia alternar?** **[HIPOTESE-HISTORICA]** — mais provável é
    preferência pessoal de interface entre operadores que já conheciam o app antigo e
    os que preferiam o novo, mas isso não está documentado em lugar nenhum do código,
    é inferência razoável a partir do mecanismo existir.

## Ordem histórica — RESOLVIDA por relato do autor original

**[CONFIRMADO-RELATO-USUARIO]** TEMA V1 (14.6.1) foi construído primeiro; TEMA V2
(15.8.1) foi construído depois, como segunda geração da experiência; TEMA V1 foi
**preservado**, não removido, quando TEMA V2 foi criado — daí a coexistência e o
mecanismo de alternância (`trocarapp.php`) confirmados por código. As pistas indiretas de
código que eu já tinha (jQuery mais antigo em `14.10.2`, estrutura de 2 camadas mais
simples em TEMA V1, dependência unidirecional — TEMA V1 usa código de TEMA V2, nunca o
contrário) são **consistentes** com este relato e passam a ser lidas como corroboração,
não mais como hipótese solta. Nenhuma data de commit/changelog interna ao backup foi
encontrada (não é necessário buscar mais — a ordem já está resolvida pela fonte mais
forte disponível, o relato do autor).
