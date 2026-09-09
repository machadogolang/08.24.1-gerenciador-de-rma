# Plano de execucao - primeira onda de implementacao do Tema V3

Data: 2026-09-09. Status: [R] REVISADO (aprovado para execucao desta tranche).

## Baseline

- HEAD: `1f5acb3`; origin/main: `1f5acb3`; working tree limpa.
- Frente: EVO-UX-001 / Tema V3 / Console Operacional Adaptativa.
- Referencias: OpenSpec `tema-v3-console-operacional`, refinamento EVO-UX-001,
  matriz, mapa de telas, wireframes e spike Tailwind x CSS semantico.

## Escopo desta rodada

- [x] T3-08 - Fundacao oculta: enum com caso V3 (sem toggle publico), prefixo de
  rota `/v3`, middleware forcando V3, bundle Vite proprio, tokens e shell.
- [x] T3-09 - Dashboard operacional: busca rapida, filas e alertas com destino
  real; sem criar novo motor de busca.
- [x] T3-10 - Listagem operacional de RMAs com filtros enderecaveis e cartoes
  mobile.

Fora de escopo: detalhe/formulario/parceiros/admin/relatorios V3, selecao publica
de tema e T3-GATE.

## Tema V3 oculto

- Adicionar `TemaPreferido::V3` ao enum e manter `alternar()` binario apenas para
  V1/V2 (V3 nunca e alvo de alternancia).
- Criar `routes/tema-v3.php` com prefixo `/v3`, nome `v3.*`, auth.
- `ResolverTemaAtivo` ganha ramo `v3.` forcando V3.
- Nenhum usuario normal recebe V3; login nao muda; V1/V2 nao mudam.

## Arquivos previstos

- `app/Identidade/Dominio/TemaPreferido.php` (case V3 + ramo seguro em alternar).
- `app/Http/Middleware/ResolverTemaAtivo.php` (ramo v3).
- `routes/tema-v3.php` e carga em `bootstrap/app.php`.
- `app/Http/Controllers/Rma/V3{..}.php` para dashboard e listagem (presentacao).
- `resources/views/temas/v3/` (layout, dashboard, rma/index).
- `resources/sass/temas/v3.scss` com tokens nomeados e BEM-like.
- `resources/js/temas/v3.js` para drawer/rail (progressive enhancement).
- `vite.config.js` entrada v3.
- Testes Feature e Browser.

## Stack visual

Sass/CSS semantico moderno (BEM-like + tokens), conforme T3-SPIKE-01. V3 nao
importa `_v1-base`/`_v2-base`.

## QA

- Feature: renderiza V3 por `/v3/...`, V1/V2 intactos, seletor publico sem V3.
- Tenant/policy preservados.
- Playwright 390/768/1440: drawer abre/fecha/ESC/TAB/aria-expanded, dashboard e
  listagem.
- PHPUnit completo ao final da tranche.

## Criterio de saida

T3-08/09/10 verdes com evidencia; V1/V2/tenant/policy sem regressao; V3 oculto;
build verde; plano/handoff reconciliados.

## Riscos

- Adicionar enum V3 pode afetar testes que enumeram temas (nenhum caso encontrado;
  regressao sera conferida por suite).
- Bundle V3 vazando para V1/V2 (mitigado por entrada Vite propria e sem import
  cruzado).
- Dashboard adicionando consultas (mitigado por contagem simples por status e
  reuso do compositor de alertas existente).

## Nao fazer

- Nao expor V3 no seletor.
- Nao alterar toggle V1/V2 publico.
- Nao implementar T3-11+ nesta tranche.
- Nao fazer push.
