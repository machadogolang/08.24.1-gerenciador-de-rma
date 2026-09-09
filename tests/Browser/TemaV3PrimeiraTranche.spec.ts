import { test, expect, type Browser, type Page } from '@playwright/test';

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

test.setTimeout(120_000);

async function login(browser: Browser, viewport: { width: number; height: number }): Promise<Page> {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test.describe('T3-08/09/10 - primeira tranche do Tema V3', () => {

    test('desktop: shell, dashboard e listagem renderizam sem quebrar V1/V2', async ({ browser }) => {
        const page = await login(browser, { width: 1440, height: 900 });

        await page.goto(`${V3}/v3`, { waitUntil: 'load' });
        await expect(page.locator('.app-shell__rail')).toBeVisible();
        await expect(page.locator('h1')).toContainText('Dashboard');
        await expect(page.locator('.grade-filas .cartao--fila').first()).toBeVisible();
        await expect(page.locator('.painel-alertas')).toBeVisible();

        await page.goto(`${V3}/v3/rmas`, { waitUntil: 'load' });
        await expect(page.locator('table.tabela-v3')).toBeVisible();
        await expect(page.locator('.segmentos .segmento').first()).toHaveAttribute('aria-current', 'true');

        await page.goto(`${V3}/v3/rmas?fila=entrada`, { waitUntil: 'load' });
        await expect(page.locator('.segmento[aria-current="true"]')).toContainText('Entrada');

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'load' });
        await expect(page.locator('#TOPO')).toBeVisible();
        await page.goto(`${V3}/v2/rma`, { waitUntil: 'load' });
        await expect(page.locator('.header-v2')).toBeVisible();

        await page.context().close();
    });

    test('mobile 390: cards, drawer com aria-expanded, ESC e TAB', async ({ browser }) => {
        const page = await login(browser, { width: 390, height: 844 });

        await page.goto(`${V3}/v3/rmas`, { waitUntil: 'load' });
        await expect(page.locator('.app-shell__menu-button')).toBeVisible();
        await expect(page.locator('.app-shell__rail')).toBeHidden();
        await expect(page.locator('.cartoes-rma').first()).toBeVisible();

        const botaoMenu = page.locator('[data-v3-drawer-abrir]');
        await botaoMenu.click();
        await expect(page.locator('[data-v3-drawer]')).toHaveClass(/is-open/);
        await expect(botaoMenu).toHaveAttribute('aria-expanded', 'true');

        const botaoFechar = page.locator('[data-v3-drawer-fechar]');
        await expect(botaoFechar).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(page.locator('[data-v3-drawer] nav a').first()).toBeFocused();

        await page.keyboard.press('Escape');
        await expect(page.locator('[data-v3-drawer]')).not.toHaveClass(/is-open/);
        await expect(botaoMenu).toHaveAttribute('aria-expanded', 'false');

        await page.context().close();
    });

    test('tablet 768: rail visivel e tabela acessivel', async ({ browser }) => {
        const page = await login(browser, { width: 768, height: 1024 });
        await page.goto(`${V3}/v3/rmas`, { waitUntil: 'load' });
        await expect(page.locator('.app-shell__rail')).toBeVisible();
        await expect(page.locator('table.tabela-v3')).toBeVisible();
        await page.context().close();
    });
});
