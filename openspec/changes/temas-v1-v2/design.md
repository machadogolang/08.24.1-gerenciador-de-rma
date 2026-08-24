# Design — Apresentação (Temas V1/V2)

## Paleta (fonte: `inventario-visual-tema-{v1,v2}.md`)

```scss
// _v1.scss
$fundo: #262626;
$acento: #C3FF00;
$texto: #FFF;
$fonte: "Open Sans", "Arial", "Fira Sans";

// _v2.scss
$azul-petroleo: #224A5D;
$azul-marinho: #18354B;
$fundo: #FFF;

// _compartilhado.scss (mesmo tom de alerta nos dois temas)
$cor-alerta: #904141;
```

`ClasseDeAlerta` (Fase 5) mapeia para classes CSS por tema — TEMA V2 já tem o mapeamento
completo confirmado (`.TrInconformidade`, `.TrUrgente`, `.TrSemGarantia1/2`); TEMA V1
usa o mesmo `$cor-alerta` até a pendência 2 do `proposal.md` ser resolvida (renderizar
telas internas reais).

## `ResolverTemaAtivo` (middleware)

```php
final class ResolverTemaAtivo
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tema = $request->user()?->tema_preferido ?? TemaPreferido::V2;
        View::share('temaAtivo', $tema);
        // Controllers continuam únicos; a resolução de view por tema acontece
        // no retorno da action (helper `view_do_tema('rma.index')` resolve para
        // resources/views/temas/{v1,v2}/rma/index.blade.php)
        return $next($request);
    }
}
```

Não duplica Controller — o mesmo `RmaController@index` (Fase 3) responde a
`GET /v1/rma` e `GET /v2/rma` (`routes/tema-{v1,v2}.php`), só a view retornada muda.

## Estrutura de diretórios

```
resources/views/temas/
├── v1/
│   ├── layout.blade.php
│   ├── rma/{index,create,edit,show}.blade.php
│   ├── parceiros/{index,_form}.blade.php
│   └── identidade/{login,usuarios,perfil}.blade.php
└── v2/
    ├── layout.blade.php
    └── (mesma árvore)
```

## Testes

- `RenderizaTemaV1Test`, `RenderizaTemaV2Test` — smoke: cada tela principal (login,
  home/painel de alertas, novo RMA, detalhe, cadastros) renderiza sem erro no tema
  certo, para um usuário com `tema_preferido` correspondente.
- Playwright (`tests/Browser/`) — comparação lado a lado com LEGACY-RUNTIME (`:8094`)
  nos 3 breakpoints (390/768/1440), pelos dois temas — só executável depois de resolver
  a pendência 2 (telas internas de TEMA V1 renderizadas de verdade no legado).

## Pendências que bloqueiam a implementação (repetido do `proposal.md`, para quem for direto ao `design.md`)

1. Mecanismo das âncoras de TEMA V2 — decidir com `docker ps`/Network tab do
   LEGACY-RUNTIME antes de implementar o roteamento de TEMA V2.
2. RN-11 em TEMA V1 — capturar telas internas reais antes de fixar `ClasseDeAlerta` em
   TEMA V1.
