# Cache local de CDN do legado (referência visual)

O LEGACY-RUNTIME (`08.24.4-legacy-gerenciador-de-rma`) carrega Bootstrap 3.3.5 via CDN
externo (`maxcdn.bootstrapcdn.com`) — comportamento real do legado, não alterado (nunca
editamos `legacy-source/`). Para capturas de referência visual (Playwright) não
dependerem de acesso à internet em toda sessão futura, os arquivos reais foram baixados
uma vez e vendorizados aqui:

- `bootstrap-3.3.5/css/bootstrap.min.css`
- `bootstrap-3.3.5/js/bootstrap.min.js`

## Como usar em um script de captura Playwright

Interceptar a requisição e servir o arquivo local em vez de bloquear (bloquear quebra o
layout de verdade — já aconteceu nesta sessão e gerou uma comparação enganosa):

```js
import { readFileSync } from 'node:fs';
import { join } from 'node:path';

const CACHE = join(process.cwd(), 'tests/Browser/Support/legacy-cdn-cache/bootstrap-3.3.5');

await page.route('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css', (route) =>
    route.fulfill({ body: readFileSync(join(CACHE, 'css/bootstrap.min.css')), contentType: 'text/css' }),
);
await page.route('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js', (route) =>
    route.fulfill({ body: readFileSync(join(CACHE, 'js/bootstrap.min.js')), contentType: 'application/javascript' }),
);
```

Isso reproduz o resultado visual real (Bootstrap carregado) sem depender de internet
disponível na sessão. Fontes externas que o legado usa de verdade (Google Fonts Fira
Mono/Sans, `code.cdn.mozilla.net`) podem ser cacheadas da mesma forma se necessário —
não incluídas aqui ainda porque não bloquearam nenhuma captura até agora.

**Nunca use isso para servir Open Sans** — a decisão de produto (`INV-RMA-07`... na
verdade `temas-v1-v2/proposal.md`, 2026-08-25) é reproduzir o fallback quebrado
(`Arial`/`Fira Sans`), nunca fazer essa fonte específica carregar de verdade.
