# Paridade visual do TEMA V1 — Legacy 14.6.1 × V3

Data: 2026-08-25. Estado: checkpoint desktop 1440 px concluído.

## Escopo e fontes

[CONFIRMADO-14.6.1] A especificação visual executável foi comparada em
`http://localhost:8094/14.6.1/` com o V3 em `http://localhost:8095/`. Foram lidas
integralmente `pattern/14.6.1.css` (207 linhas), `pattern/15.9.7.css` (296 linhas), a
estrutura HTML autenticada e os SCSS/Blades do V3. O objetivo foi paridade, sem
modernização e mantendo a largura histórica fixa de 984 px.

O ensaio reproduzível é `tests/Browser/ParidadeVisualTemaV1.spec.ts`. Ele gera as
capturas em `docs/produto/screenshots-paridade-v1/`; os PNGs ficam locais e ignorados
pelo Git porque o runtime histórico pode exibir dados reais do backup.

## Diagnóstico comprovado

1. O entrypoint Vite estava correto: `v1.js` importava `v1.scss`, que incluía
   `_compartilhado.scss`. O stylesheet chegava ao navegador, mas era um port parcial.
2. O Blade reconstruído usava um `ul.breadcrumb` vertical dentro do conteúdo. O legado
   autenticado usa logo e três `li.menu-up` flutuados no cabeçalho, além de painel de
   sessão com coluna esquerda de 838 px e direita de 144 px. CSS isolado não corrigiria
   a incompatibilidade estrutural.
3. Os `@font-face` apontavam para `../fonts`, gerando `/build/fonts/...` 404. Após a
   correção do caminho, os dois TTF existentes ainda falhavam no FontFaceSet e no
   `fc-query`: eram arquivos inválidos. Foram substituídos pelas fontes oficiais Fira
   Mono sob a OFL já versionada.
4. O logo histórico não existia no V3. Uma cópia própria e versionável de
   `ferramenta-logo.png` foi incorporada; o V3 não consulta o container Legacy como CDN.
5. Depois da correção, Vite gera CSS e fontes com hash, os recursos essenciais retornam
   200 e o navegador carrega Fira Mono sem erro.

## Matriz de seletores históricos

A classificação abaixo cobre os seletores por famílias funcionais. Seletores compostos
(`:hover`, filhos e estados) seguem a decisão da família indicada.

| Seletores Legacy | Antes no V3 | HTML V3 | Decisão / estado |
|---|---:|---:|---|
| `html`, `body`, `#FIXADO`, `#TOPO`, `#BASE`, `#MEIO`, `#CONTEUDO`, `#RODAPE` | sim | sim | mantidos em `_compartilhado.scss`/`v1.scss`; 984 px preservados |
| `a`, `li`, `.fl`, `.fr`, `.clear`, `.tam`, `.active` | parcial | sim | utilitários usados portados; não se importou reset global morto |
| `.image-up`, `.menu-up`, `.formButtonMENU`, `.formButtonSIGNOUT` | não | sim, após correção | estrutura e regras históricas portadas |
| `.menuDivSession`, `.JS-SessaoLEFT`, `.JS-SessaoRIGHT`, `.JS-DivLEFT`, `.lisessao` | não | sim, após correção | painel 838+144 px portado e navegação Laravel preservada |
| `.JS-Novo`, `.JS-Localizar`, `.JSformLocalizarInput`, `.JSformLocalizarButton` | parcial | sim | wrappers/classes corrigidos e regras portadas |
| `.tablenovo`, `.novo_formInput*`, `.novo_defeito`, `.formSelectempresa`, `.formButtonEnviarNovo` | equivalente | sim por componentes V3 | já cobertos pelos seletores conscientes de formulário em `v1.scss`; não duplicados |
| `.Tabelinha-Table`, `.Tabelinha-TR1/2/3`, `.Tabelinha-TD*`, `.TDD*`, `.TRD*` | parcial | sim | base compartilhada mantida; linhas do painel e tabela de usuários completadas |
| `.formInputPanel`, `.formSelectPanel`, `.formButtonEnviarPanel`, `.usuariosemail` | não/parcial | sim | portados para usuários e cadastros; ações Laravel continuam funcionais |
| `.formInputStats`, `.formLabelStats`, `.estatisticas`, `.formTRDetailD`, `.formSelectView*` | sim/equivalente | sim nas superfícies RMA | port existente preservado; sem duplicação |
| `.centrodeavisos`, `.TrZebrada*`, `.TrInconformidade`, `.TrUrgente`, `.TrSemGarantia*` | sim | sim | camada compartilhada já comprovada nas fases 5 e 8 |
| `.linha-usuario`, `.menu-right`, `.li-right`, `.ul-right`, `.menu` | não | não | variantes sem correspondência no HTML autenticado selecionado; não portar |
| `.image-signin`, `.SignInCenterForm`, `.SignUpCenterForm`, `.formInputSignIn*`, `.formInputSignUp*` | não | não | substituídos conscientemente pelo gateway compartilhado aprovado na F8 |
| `.HospedagemDiv`, `.controle-add` | não | não | domínio/resíduo sem superfície V3; não portar |
| `.usuarioButtonDel`, `.usuarioInputDel`, `.assistenciaButton*`, `.blistAdd*`, `.numero_rma*` | não | não | ações procedurais antigas substituídas pelos CRUDs/policies Laravel; não portar |
| `.polinput`, `.observacaoinput`, `.textarea-obs`, `.p-observacao` | equivalente | sim onde aplicável | componentes V3 existentes mantidos; sem cópia cega |
| `.breadcrumb`, `.submenutitulo`, `.pmo` de `15.9.7.css` | não no V1 autenticado | não | pertencem à camada/estrutura do outro app; não usar para reconstruir o menu V1 |
| classes Lightbox/AdminLTE e seletores sem ocorrência nos Blades V1 | não | não | não portar; investigações residuais gerais C-03/C-04 permanecem fora deste checkpoint |

## Fontes e dependências externas

| Referência histórica | Uso efetivo | Decisão V3 |
|---|---|---|
| Fira Mono | e-mails, campos e superfícies monoespaçadas | local: Regular/Bold válidos, Vite 200 |
| Open Sans | perde precedência para Arial/Fira nas superfícies comparadas | fallback consciente já decidido em C-05; não baixar |
| Roboto | import histórico malformado e sem efeito computado relevante | não utilizado |
| Fira Sans | importado, sem efeito computado nas superfícies reconstruídas | não utilizado |
| Arial | tipografia visual dominante em corpo/tabelas | fonte do sistema, mantida |
| `http://scripting.com.br` | link do rodapé | link de navegação histórico, não asset; mantido com `rel=noopener` |

A busca no código-fonte do V3 não encontrou Google Fonts/CDN usados como recurso
essencial do TEMA V1. URLs em documentação, configuração de ambiente, licença, SVG e
links de navegação não são dependências visuais. O CSS/JS, as fontes e o logo usados no
runtime autenticado pertencem ao build/repositório V3.

## Matriz de superfícies em 1440 px

| Superfície | Legacy | V3 | HTML/CSS/assets | Diferença conhecida | Aprovação deste checkpoint |
|---|---:|---:|---|---|---:|
| Login | sim | sim | gateways capturados | V3 usa gateway compartilhado aprovado na F8, não login interno V1 | sim, por decisão existente |
| Home/dashboard | sim | sim | cabeçalho/base/rodapé compatíveis | conteúdo e dados reconstruídos | sim |
| Usuários | sim | sim | painel, tabela, fonte e controles corrigidos | V3 permite papel/reset; legado é painel mais simples | sim |
| Clientes | sim | sim | painel 838+144 e tabela compatíveis | massa de dados distinta | sim |
| Fabricantes | sim | sim | painel e tabela compatíveis | massa de dados distinta | sim |
| Fornecedores | sim | sim | painel e tabela compatíveis | massa de dados distinta | sim |
| Assistências | sim | sim | painel e tabela compatíveis | massa de dados distinta | sim |
| RMA listagem | sim | sim | busca/tabela/cores compatíveis | filtros e dados reconstruídos | sim |
| RMA novo | sim | sim | wrapper, campos e geometria histórica | sem PHP procedural; domínio Laravel preservado | sim |
| RMA detalhe | sim | sim | composição autenticada capturada | dados/ações dependem do ciclo V3 | sim |

A aprovação acima é restrita às dez superfícies desktop exigidas e às diferenças
funcionais já justificadas. Não encerra o gate visual completo da F10, que ainda exige
TEMA V2, demais superfícies e a matriz de breakpoints do checklist mestre.

## Evidência e validação

- 20 screenshots locais: par Legacy/V3 para cada uma das dez superfícies.
- Geometria assertada: base 984 px, painel 982 px, colunas 838/144 px e três itens
  `.menu-up` flutuados.
- Recursos essenciais monitorados: nenhuma resposta 4xx/5xx ou falha em `/build/` e
  `/images/tema-v1/`.
- Fira Mono validada por FontFaceSet e `fc-query`.
- Logo local SHA-256: `7c0e10bdbae8f445dcc0db94917a4755dd803365379eedcc48455676a4359aed`.
- Fira Mono Regular SHA-256: `2e00b0cf3106a3d792f35711b7722740b76c02ee7300ea8c60f940584b7a8ac9`.
- Fira Mono Bold SHA-256: `61f0ca3ae72a83deb807040f314e1b8b4e40b08213df243db9fd430095cab305`.

- Vite: `npm run build` aprovado.
- PHPUnit: 310 testes, 608 assertions, sem falhas.
- Playwright: 3 testes novos de paridade/assets e 3 testes existentes de largura fixa,
  todos aprovados. Foram executados separadamente por topologia: o comparador Legacy
  precisa das portas do host; o teste antigo prepara dados via `tinker` no container.

Não surgiu intenção visual sem fonte histórica suficiente; nenhuma investigação nova foi
aberta. As investigações gerais já existentes sobre Lightbox2 e skin AdminLTE continuam
registradas, sem bloquear este recorte do TEMA V1.
