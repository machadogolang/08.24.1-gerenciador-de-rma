import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * PAR15-CREDIT-001/003 - pagina de Creditos do TEMA V2 contra o Legacy.
 *
 * Arqueologia: a rota `/creditos` do Legacy cai em `index.php?p=credito` ->
 * `page/credito.php` (a tabela). O `page/creditos.php` + `inc/menu_creditos.php`
 * (Disponiveis/Pendentes/Usados) apontam para `subp/*.php` inexistentes: sao CODIGO
 * MORTO e nao devem aparecer em nenhum dos lados (PAR15-CREDIT-002/004).
 */
const LEGACY_ROOT = process.env.LEGACY_ROOT ?? 'http://localhost:8094';
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };
const COLUNAS = ['DATA', 'NF C', 'FABRICANTE', 'DESCRICAO', 'MODELO', 'NF R', 'PROTOCOLO', 'DESTINATARIO', 'OS', 'VALOR', 'A'];

test.setTimeout(180_000);

async function loginLegacy(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    await page.goto(`${LEGACY_ROOT}/15.8.1/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    return page;
}

async function loginV3(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test('PAR15-CREDIT-001/003 - /v2/creditos usa as colunas da tabela do 15.8.1', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    await legacy.goto(`${LEGACY_ROOT}/15.8.1/creditos`, { waitUntil: 'domcontentloaded' });

    const v3 = await loginV3(browser);
    await v3.goto(`${V3}/v2/creditos`, { waitUntil: 'domcontentloaded' });

    const colunasLegacy = (await legacy.locator('table.Tabelinha-Table tr.SuperTr th').allInnerTexts()).map(t => t.trim()).filter(Boolean);
    const colunasV3 = (await v3.locator('table.Tabelinha-Table thead th').allInnerTexts()).map(t => t.trim()).filter(Boolean);

    expect(colunasLegacy.slice(0, COLUNAS.length)).toEqual(COLUNAS);
    expect(colunasV3.slice(0, COLUNAS.length)).toEqual(COLUNAS);

    // O cabecalho "Creditos" + icone pertence ao `page/creditos.php` MORTO (nunca
    // executado); a rota real (`p=credito`) renderiza so a tabela - nos dois lados.
    expect(await legacy.locator('h3.box-subpage').count()).toBe(0);
    expect(await v3.locator('h3.box-subpage').count()).toBe(0);

    // O menu morto de Disponiveis/Pendentes/Usados nao existe em nenhum dos lados.
    expect(await legacy.content()).not.toContain('Pendentes');
    expect(await v3.content()).not.toContain('Pendentes');

    // A primeira linha de dados (quando existir) tem a mesma altura nos dois lados.
    const linhaLegacy = legacy.locator('table.Tabelinha-Table tr').filter({ hasNot: legacy.locator('th') }).first();
    const linhaV3 = v3.locator('table.Tabelinha-Table tbody tr').first();
    if ((await linhaLegacy.count()) > 0 && (await linhaV3.count()) > 0) {
        const caixaLegacy = await linhaLegacy.boundingBox();
        const caixaV3 = await linhaV3.boundingBox();
        console.log('linha credito legacy=', caixaLegacy, ' novo=', caixaV3);
        expect(Math.abs((caixaLegacy?.height ?? 0) - (caixaV3?.height ?? 0))).toBeLessThanOrEqual(4);
    }
});
