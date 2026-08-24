# Parecer — Arqueologia do CellSystem RMA V2 (15.9.7)

Data: 2026-08-24. Conclui a investigação registrada em
`docs/investigacoes-pendente/concluido/INV-RMA-00-arqueologia-cellsystem-15.9.7-concluido-2026-08-24.md`
e nos documentos especializados de `docs/legado/`. Responde aos 17 pontos pedidos.

## 1. O que realmente era o CellSystem RMA 15.9.7

Um sistema interno de controle de RMA (Return Merchandise Authorization) da CellSystem,
cobrindo o ciclo completo de um produto com defeito: entrada, triagem, encaminhamento
para garantia (fabricante/fornecedor/assistência técnica), acompanhamento de prazo,
crédito, conclusão e relatórios fiscais/contábeis. **Não é um app único** — é um
container (RMA V2/15.9.7) com duas experiências de usuário coexistentes e alternáveis
(TEMA V1 = 14.6.1, TEMA V2 = 15.8.1), confirmadas rodando de fato no ambiente executável
(`08.24.4-legacy-gerenciador-de-rma`). TEMA V1 foi construído primeiro; TEMA V2 é a
segunda geração, mais rica, e TEMA V1 foi preservado, não substituído (confirmado pelo
autor original do sistema).

## 2. Arquitetura

PHP procedural clássico, sem framework, com duas camadas de organização diferentes por
tema: TEMA V1 usa `page/` + `post/` (2 camadas); TEMA V2 usa `page/` + `pp/` + `subp/` +
`inc/` (4 camadas, separação mais granular entre listar/editar/apagar/ver). Uma camada
compartilhada na raiz do container (`metodo.php`, `conexao.php`, `trocarapp.php`) contém
as 10 regras de alerta mais sofisticadas e a infraestrutura de auditoria/permissão,
usada por ambos os temas — TEMA V1 depende de TEMA V2 para essas regras (include
cruzado), nunca o inverso. Sem MVC, sem ORM, sem injeção de dependência, sem testes.

## 3. Módulos

RMA (núcleo), Cadastros (cliente/fabricante/fornecedor/assistência técnica), Usuários e
Permissões, Créditos, Relatórios (RCD/RPEC/RMPE), Auditoria (log de autenticação +
modificação), Pesquisa/Localização. Ver inventário completo (48 itens catalogados) em
`docs/legado/inventario-funcional-rma-v2.md`.

## 4. Entidades

`bd` (o RMA, ~56 colunas, sem FK para nada), `cliente`, `fabricante`, `fornecedor`,
`assistencia_tecnica` (quatro tabelas de "contraparte" com schema quase idêntico,
relacionadas a `bd` só por nome de string), `usuario`, `log`, `modificacao`,
`relatorio`, e uma tabela órfã (`assistencias`, tentativa de unificação abandonada, só
usada por TEMA V1). Não existe entidade `Equipamento` separada do RMA. Detalhe completo:
`docs/legado/modelo-dominio-rma-legado.md` e `docs/legado/inventario-banco-rma-v2.md`.

## 5. Regras

21 regras catalogadas (`docs/legado/regras-negocio-rma-legado.md`), das quais as 10 mais
importantes são os alertas operacionais (prazo, garantia, NF, S/N faltando, prioridade),
todas na camada compartilhada. Achados centrais: a regra hardcoded de garantia do
fabricante MARKVISION (conhecimento tácito nunca estruturado), o threshold econômico de
R$75 para urgência (só em TEMA V2), e — o achado mais importante desta arqueologia — a
troca de senha pelo próprio usuário **funciona corretamente em TEMA V1 e está quebrada
em TEMA V2** (regressão real e comprovada, não suposição), resolvendo de vez a dúvida
sobre qual é o "comportamento pretendido" a reconstruir.

## 6. Fluxos

`entrada → recebido → encaminhado → concluido` (status logístico) mais `arquivado`
(pausa reabrível) e `retornou` (estado morto, nunca usado). Ortogonal a isso, `solucao`
(17 valores) descreve o desfecho comercial/técnico. Fluxo de crédito paralelo
(`solucao='PENDENTE CREDITO' → 'GERADO CREDITO' → creditodisponivel`, controle manual em
duas camadas independentes). Máquina de estados detalhada em
`docs/legado/regras-negocio-rma-legado.md` e `docs/legado/modelo-dominio-rma-legado.md`.

## 7. Como funcionava o banco

MySQL/MariaDB (10.3.14 na origem), sem nenhuma foreign key em nenhuma tabela — todo
relacionamento por comparação de string de nome. `bd` é MyISAM (as tabelas de contraparte
são InnoDB — inconsistência de engine, sem motivo aparente). Índice composto muito largo
em `bd` (`idx_3`, 16 colunas) provavelmente para acelerar a busca genérica por `LIKE` em
23 campos. Schema completo, com domínios de valor confirmados por coluna, em
`docs/legado/inventario-banco-rma-v2.md`.

## 8. Tecnologias utilizadas

PHP 7.4-compatível (confirmado por ausência de qualquer API removida em PHP 7+, validado
rodando de verdade em PHP 7.4 no LEGACY-RUNTIME), `mysqli` com prepared statements
parcial (SQLi confirmada em vários pontos onde não é usado), Apache + `mod_rewrite`,
Bootstrap 3.3.5 (CDN), AdminLTE 2.2.0 (skin não ativa), jQuery 1.11-2.1, iCheck,
Lightbox2 (uso real não confirmado). Detalhe: `docs/legado/inventario-tecnico-15.9.7.md`.

## 9. Bibliotecas antigas ainda úteis

Nenhuma precisa ser preservada como dependência de runtime da V3 — o requisito é
preservar **comportamento e aparência**, não a biblioteca. Fontes (Open Sans/Roboto) são
reaproveitáveis via Google Fonts. Ícones/imagens do legado (raposa, ícones de ação) são
ativos visuais reaproveitáveis como referência de design, não como arquivo servido tal
qual.

## 10. O que deve ser substituído

Bootstrap 3.3.5 → 5.3 (comportamento visual equivalente, não bit a bit); AdminLTE 2.2.0
(sem uso de skin ativo, substituível por CSS próprio); jQuery/iCheck → JS moderno, exceto
se alguma interação específica só for viável com eles (nenhuma identificada até agora);
`mysqli` procedural → Eloquent/query builder do Laravel.

## 11. Assets a preservar (como referência de identidade, não arquivo)

Paleta de cor por tema (TEMA V1: fundo `#262626`, acento `#C3FF00`; TEMA V2: azul
petróleo `#224A5D`/`#18354B`, vermelho de alerta `#904141` — mesmo tom nos dois temas),
tipografia (Open Sans/Fira), classes de estado visual (`TrInconformidade`, `TrUrgente`,
`TrSemGarantia`, `TrZebrada`), estrutura de navegação de cada tema (páginas completas em
TEMA V1, abas por âncora em TEMA V2 — diferença de UX real, não só visual). Detalhe:
`docs/legado/inventario-visual-tema-v1.md` e `-v2.md`.

## 12. Comportamentos a preservar 1:1

As 21 regras de negócio marcadas VIGENTE (RN-01 a RN-16 na versão de TEMA V2, que é a
referência funcional), a máquina de estados `status`/`solucao`, o modelo de permissão de
5 níveis (estável e idêntico nos dois temas — a evidência mais sólida do levantamento),
a troca de tema com persistência de preferência, os dois temas visuais coexistindo.

## 13. Pontos que só precisam de implementação equivalente

Tudo que hoje é PHP procedural correto mas tecnicamente datado: geração de número de RMA
(pode virar sequência por tenant/ULID no futuro, mas na baseline preserva o formato
percebido), auditoria (formato pode virar diff estruturado em vez de snapshot, é melhoria
interna invisível ao usuário), buscas (`pesquisar()` genérica pode virar Scout/índice
melhor, resultado percebido igual).

## 14. Problemas estruturais que não devem ser copiados

Ausência total de FK (relacionamento por string); SQL Injection em múltiplos pontos
(`pesquisar()` tem versão seguray comentada e abandonada no próprio código-fonte); Local
File Inclusion via `?page=`/`?subp=` sem whitelist; senha SHA1 sem salt; senha em texto
plano enviada por e-mail; ausência de CSRF; `marcarestoque` sem enforcement real
(RN-17); módulo de créditos com rota quebrada em TEMA V2 (RN-18).

## 15. Problemas de segurança que existiam

Catalogados por tema em `docs/legado/regras-negocio-rma-legado.md` §7-8 do relatório de
arqueologia original: SQLi, LFI, SHA1 sem salt, CSRF ausente, enumeração de usuário no
login, XSS refletido, senha em claro por e-mail, `phpinfo()` não encontrado aqui (achado
de outro projeto desta sessão, não deste). Nenhum foi reproduzido em nenhum documento
com valor real — só localização e classe de vulnerabilidade.

## 16. Arquitetura moderna proposta (a detalhar em INV-RMA-05, ainda não escrita)

Linha CONAHOM adaptada: Laravel 13/PHP 8.3 (já confirmado executável em
`08.24.1-gerenciador-de-rma`, porta 8095), Eloquent com FK reais desde a baseline onde
isso não muda comportamento percebido (ver regra de evolução do banco), Form
Requests/Policies para validação e autorização, arquitetura de temas com domínio/
controllers compartilhados e camada de apresentação (view/componentes) variável por
tema — a decidir a granularidade exata depois de comparar mais profundamente as
diferenças reais de UX entre TEMA V1 e TEMA V2 (feito parcialmente, ver inventários
visuais). Migrador V2→V3 como funcionalidade oficial do produto, não script avulso.

## 17. Estratégia de alta fidelidade sem dívida técnica

Baseline primeiro: banco V3 nasce da migração do schema legado preservando semântica
(mesmo que isso signifique, na primeira versão, manter alguma modelagem imperfeita);
correções de segurança/arquitetura entram desde o início por serem invisíveis ao
comportamento percebido; correções de UX/produto (FK real, diff de auditoria, temas
modernos) ficam explicitamente registradas no backlog evolutivo até a paridade funcional
+ visual + de dados estar comprovada. Cada regra preservada tem rastreabilidade por tema
(`[CONFIRMADO-TEMA-V1]`/`[CONFIRMADO-TEMA-V2]`) — nenhuma decisão de reconstrução nasce
de suposição.

---

## Estado de prontidão para a V3 (seção 40 da diretriz mestre)

| Pergunta | Resposta |
|---|---|
| Entendemos suficientemente o legado? | Sim — 48 funcionalidades catalogadas, 21 regras com rastreabilidade por tema, schema completo, os dois temas rodando de verdade |
| Inventário funcional suficiente? | Sim, com 1 item residual (RN-12/threshold R$75, não localizado em TEMA V1) |
| Banco mapeado? | Sim (`inventario-banco-rma-v2.md`) |
| Regras mapeadas? | Sim (`regras-negocio-rma-legado.md`) |
| TEMA V1 compreendido? | Sim, inclusive rodando; faltam telas internas (novo RMA, detalhe) em evidência visual |
| TEMA V2 compreendido? | Sim, mais profundamente que TEMA V1 (é a referência funcional) |
| Diferenças V1/V2 claras? | Sim (`matriz-comparacao-apps-rma.md`) |
| Runtime legado funciona? | **Sim, de verdade** — smoke test completo em `08.24.4-legacy-gerenciador-de-rma`, porta 8094 |
| Migração é viável? | Sim, em princípio — mapa formal (`INV-RMA-06`) ainda não escrito |
| Riscos restantes | RN-12 residual; screenshots/telas internas não capturadas; nenhuma decisão de arquitetura de tema fixada ainda |
| OpenSpecs necessárias primeiro | Catálogo de 9 grupos já proposto em `PLANO-ATAQUE.md`, nenhuma escrita |
| Primeira fatia segura da V3 | Autenticação + domínio `Cliente`/`Fabricante`/`Fornecedor`/`AssistenciaTecnica` (schema estável, regra simples, baixo risco) |

**Conclusão:** a arqueologia está madura o suficiente para avançar para `INV-RMA-05`
(arquitetura) e o catálogo formal de OpenSpec. Não há bloqueio material para começar a
especificar a primeira fatia da V3.
