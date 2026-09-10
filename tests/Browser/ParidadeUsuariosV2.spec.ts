import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * PF-14 / PAR15-USR-001 - comparacao Legacy x novo da tela de usuarios do TEMA V2.
 * Fonte Legacy: `15.8.1/subp/usuarios.php` (colunas Nome, E-mail, QT Login,
 * Ultimo login, Permissao, Acao; linha de 30px). Alvo novo: `/v2/usuarios`.
 *
 * Mede o contrato de TELA (rotulos de coluna e altura de linha) - nao pixel-diff -
 * porque os dois ambientes tem bases diferentes. Sem `LOCALHOST` fixo: usa
 * `LEGACY_BASE_URL`/`PLAYWRIGHT_BASE_URL` como os outros specs.
 */
const LEGACY_V2 = process.env.LEGACY_BASE_URL
    ? process.env.LEGACY_BASE_URL.replace(/14\.6\.1\/?$/, '15.8.1/')
    : 'http://localhost:8094/15.8.1/';
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 1000 };
const COLUNAS = ['Nome', 'E-mail', 'QT Login', 'Ultimo login', 'Permissao', 'Acao'];

test.setTimeout(180_000);

async function loginLegacy(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    await page.goto(`${LEGACY_V2}login`, { waitUntil: 'domcontentloaded' });
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

async function cabecalhos(page: Page): Promise<string[]> {
    return page.locator('table.Tabelinha-Table thead th, table.Tabelinha-Table tr.SuperTr th').allInnerTexts();
}

async function alturaDaPrimeiraLinha(page: Page): Promise<number | null> {
    const linha = page.locator('table.Tabelinha-Table tbody tr, table.Tabelinha-Table tr').filter({ hasNot: page.locator('th') }).first();
    if ((await linha.count()) === 0) {
        return null;
    }
    const caixa = await linha.boundingBox();
    return caixa ? Math.round(caixa.height) : null;
}

test('PAR15-USR-001 - /v2/usuarios usa o mesmo contrato de tela do Legacy 15.8.1', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    await legacy.goto(`${LEGACY_V2}usuarios/`, { waitUntil: 'domcontentloaded' });

    const v3 = await loginV3(browser);
    await v3.goto(`${V3}/v2/usuarios`, { waitUntil: 'domcontentloaded' });

    // O Legacy renderiza os rotulos em CAIXA ALTA via CSS (`SuperTr`); comparar
    // normalizado para maiusculas e o contrato real de tela.
    const esperadas = COLUNAS.map(c => c.toUpperCase());
    const colunasLegacy = (await cabecalhos(legacy)).map(t => t.trim().toUpperCase()).filter(Boolean);
    const colunasV3 = (await cabecalhos(v3)).map(t => t.trim().toUpperCase()).filter(Boolean);

    expect(colunasLegacy.slice(0, esperadas.length)).toEqual(esperadas);
    expect(colunasV3.slice(0, esperadas.length)).toEqual(esperadas);

    const alturaLegacy = await alturaDaPrimeiraLinha(legacy);
    const alturaV3 = await alturaDaPrimeiraLinha(v3);

    expect(alturaLegacy).not.toBeNull();
    expect(alturaV3).not.toBeNull();
    expect(Math.abs((alturaLegacy as number) - (alturaV3 as number))).toBeLessThanOrEqual(2);
});
