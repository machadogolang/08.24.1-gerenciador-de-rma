import { test, expect, type Browser, type Page } from '@playwright/test';

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(120_000);

async function login(browser: Browser, email = 'superadministrador@rma.local', pass = 'password'): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'load' });
    await page.fill('#email', email);
    await page.fill('#password', pass);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

async function garantirTema(page: Page, tema: 'v1' | 'v2'): Promise<void> {
    for (let tentativa = 0; tentativa < 4; tentativa++) {
        await page.goto(`${BASE_URL}/perfil`, { waitUntil: 'load' });
        const botao = page.locator('button:has-text("Alternar tema")').first();
        const texto = await botao.textContent();
        const atual = texto?.includes('atual: v1') ? 'v1' : texto?.includes('atual: v2') ? 'v2' : null;

        if (atual === tema) {
            return;
        }
        if (atual === null) {
            return;
        }

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            botao.click(),
        ]);
    }
}

test.describe('Quadrante 3 - Relatorios Operacionais e Indicadores (V1 e V2)', () => {

    test('Fluxo V1: Relatorios RCD, RPEC, RMPE, Hub e ordenacao skinless', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v1');

        // 1. Acesso ao Relatorio RCD no Tema V1
        await page.goto(`${BASE_URL}/rmas-relatorios/rcd`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#FIXADO')).toBeVisible();
        await expect(page.locator('h1.title-comicone, table.Tabelinha-Table').first()).toBeVisible();

        // 2. Acesso ao Relatorio RPEC no Tema V1
        await page.goto(`${BASE_URL}/rmas-relatorios/rpec`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('h1.title-comicone, table.Tabelinha-Table').first()).toBeVisible();

        // 3. Acesso ao Relatorio RMPE no Tema V1
        await page.goto(`${BASE_URL}/rmas-relatorios/rmpe`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('h1.title-comicone, table.Tabelinha-Table').first()).toBeVisible();

        // 4. Acesso ao Hub Estatistico de Relatorios no Tema V1
        await page.goto(`${BASE_URL}/relatorios`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.JS-DivLEFT, #CONTEUDO').first()).toBeVisible();

        await page.context().close();
    });

    test('Fluxo V2: Hub de relatorios, rotas RCD/RPEC/RMPE e tabelas', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v2');

        // 1. Acesso ao Hub de Relatorios V2
        await page.goto(`${BASE_URL}/v2/relatorios`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('ul.nav-v2')).toBeVisible();

        // 2. Navegacao para RCD V2
        await page.goto(`${BASE_URL}/v2/relatorios/rcd`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.relatorio-titulo, table.Tabelinha-Table').first()).toBeVisible();

        // 3. Navegacao para RPEC V2
        await page.goto(`${BASE_URL}/v2/relatorios/rpec`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.relatorio-titulo, table.Tabelinha-Table').first()).toBeVisible();

        // 4. Navegacao para RMPE V2
        await page.goto(`${BASE_URL}/v2/relatorios/rmpe`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.relatorio-titulo, table.Tabelinha-Table').first()).toBeVisible();

        await page.context().close();
    });

});
