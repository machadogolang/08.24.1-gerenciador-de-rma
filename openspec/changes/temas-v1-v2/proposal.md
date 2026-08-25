# Proposal — Apresentação (Temas V1/V2 fiéis)

Fase 8 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §13).

## Por quê

Todas as funcionalidades (Fases 1-7) já existem com views mínimas, sem fidelidade
visual. Esta fase é a única responsável por fazer a V3 *parecer* o legado — os dois
temas visuais coexistindo, exatamente como o produto original sempre operou.

## O que entra

- Árvore de Blade por tema (`resources/views/temas/{v1,v2}/`), reaproveitando os
  **mesmos** Controllers/casos de uso das Fases 1-7 (nenhum duplicado).
- Sass por tema com a paleta capturada (`inventario-visual-tema-{v1,v2}.md`), incluindo
  um `_compartilhado.scss` que porta de verdade `pattern/15.9.7.css`/`.js` — a folha de
  estilo/script que os DOIS temas do legado carregam além do CSS próprio (achado desta
  revisão, já incorporado ao plano — não é pendência aberta, ver `design.md`
  "Organização Vite/Sass por tema").
- `ResolverTemaAtivo` (middleware) — lê `tema_preferido` (Fase 1), decide a árvore de
  rotas/views.
- Rotas próprias por tema (`routes/tema-v1.php`, `routes/tema-v2.php`).
- QA visual lado a lado com o LEGACY-RUNTIME (`:8094`).

## O que não entra

- Qualquer regra de negócio nova — todas já existem desde as Fases 1-7; esta fase é
  puramente apresentação.
- Reescrita de design system além do necessário para reproduzir a aparência.

## Decisão registrada — granularidade de compartilhamento (resolve a pendência do checklist)

`checklist-master-v3.md` Parte 2 registrava como pendente decidir "se a diferença entre
temas é só view ou também Controller/rota". Evidência reunida nas Fases 1-7: as 21
regras de negócio já foram confirmadas como compartilhadas (camada `metodo.php`) ou
duplicadas **identicamente** entre temas (RN-13/RN-14) — nenhuma diverge por regra de
negócio, só por presença/ausência (RN-15/RN-21). **Decisão: Controllers/casos de uso
únicos** (já assim desde a Fase 1), **views e rotas por tema**. A navegação diverge de
verdade (TEMA V1 = páginas completas, TEMA V2 = âncoras de aba) — isso é roteamento e
front-end, não regra de negócio; o mesmo Controller pode responder às duas formas de
rota.

## Pendências — atualizado 2026-08-24 (inspeção direta do LEGACY-RUNTIME `:8094`)

As 2 pendências originais foram **resolvidas por evidência direta** (HTML/CSS/JS real,
com sessão autenticada, não só os inventários estáticos). Um achado adicional dessa
mesma inspeção (terceira folha de estilo compartilhada) já foi incorporado diretamente
ao plano — não é uma decisão em aberto, ver "O que entra" acima e `design.md`
"Organização Vite/Sass por tema". **2 pendências novas, genuínas** (decisão de produto
que ainda precisa ser tomada, não decidida nesta revisão) surgiram da mesma inspeção.
Detalhe completo em `design.md` ("Mecanismo de navegação por tema", "RN-11 em TEMA V1",
"Fontes", "Estrutura de diretórios").

1. ~~Mecanismo exato das âncoras de TEMA V2~~ **RESOLVIDO.** É o plugin de abas nativo
   do Bootstrap 3.3.5 (`data-toggle="tab"` + `.tab-pane`) — client-side puro, sem
   fetch/AJAX. Os 7 painéis (`#inicio`, `#pesquisar`, `#novo_rma`, `#entrada`,
   `#recebido`, `#encaminhado`, `#concluido`) já vêm todos renderizados no mesmo HTML da
   página inicial; só páginas de detalhe/CRUD (`/info/{id}`, `/clientes`, etc.) são
   reload completo, via URLs limpas já mapeadas em `15.8.1/.htaccess`.
2. ~~RN-11 (classificação visual de inconformidade) em TEMA V1~~ **RESOLVIDO.** TEMA V1
   carrega `pattern/15.9.7.css` (CSS compartilhado com TEMA V2, achado adicional já
   incorporado ao plano — ver acima) ALÉM do seu próprio `14.6.1.css`, e usa
   `TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2` em `page/entrada.php`,
   `page/encaminhados.php`, `page/localizar.php` — confirmado por leitura direta do PHP
   fonte. Única diferença real: TEMA V1 não usa `TrSemGarantia1/2` como classe própria
   (mapeia "SEM GARANTIA" para `TrInconformidade`); TEMA V2 usa o conjunto completo.
3. **RESOLVIDA (decisão do usuário, 2026-08-25) — Fonte "Open Sans" do TEMA V2 nunca
   carrega de fato.** `css/font-opensans.css` aponta pra uma URL de produção morta
   (`cellsystem.com.br`, domínio fora do ar, caminho de versão errado). **Decisão: a V3
   "deve respeitar" o resultado visual REAL do legado — reproduzir o fallback
   (`"Arial","Fira Sans"`), nunca fazer Open Sans carregar de verdade.** Fidelidade
   visual é do que o usuário efetivamente vê rodando hoje (LEGACY-RUNTIME confirmado),
   não da intenção original nunca observada — self-hostar Open Sans mudaria a
   tipografia percebida para algo que nenhum usuário real do legado jamais viu,
   violando o princípio "visual idêntico ao legado". `resources/sass/temas/v2.scss`
   declara `font-family: "Arial", "Fira Sans", sans-serif` diretamente, sem nenhum
   `@font-face`/import de Open Sans.
4. **RESOLVIDA (decisão do usuário, 2026-08-25) — Comportamento pós-login assimétrico
   entre temas.** O login-gateway compartilhado (porta de entrada padrão,
   `http://localhost:8094/`) respeita `usuario.app`/`tema_preferido`; o login embutido
   em TEMA V1 (`14.6.1/index.php`, tela própria, tabela HTML) ignorava essa preferência
   e sempre mantinha o usuário em TEMA V1. **Decisão: unificar — `SessaoController`
   (Fase 1) sempre respeita `tema_preferido` no redirect pós-login, independente de por
   qual rota/tela o usuário autenticou.** Não existe "login embutido de TEMA V1"
   separado do gateway na V3 — os dois caminhos convergem no mesmo `SessaoController`,
   eliminando a assimetria por construção (não há dois códigos de login para
   divergir). Tratado como correção de inconsistência, não como comportamento de
   negócio a preservar.

## Rastreabilidade com o legado

Não introduz `LEG-RMA-NNN` novo — é a camada de apresentação de tudo que já foi
especificado (`LEG-RMA-006`, seleção de tema, já existe desde a Fase 1).
