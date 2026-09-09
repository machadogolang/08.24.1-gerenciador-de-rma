import { test, expect, type Browser, type Page } from '@playwright/test';

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(120_000);

async function login(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
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

async function garantirTema(page: Page, tema: 'v1' | 'v2'): Promise<void> {
    await page.goto(`${V3}/perfil`, { waitUntil: 'load' });
    const botao = page.locator('button:has-text("Alternar tema"), button:has-text("Trocar p/")').first();
    const texto = await botao.textContent();
    const atual = texto?.includes('atual: v1') ? 'v1' : texto?.includes('atual: v2') ? 'v2' : null;
    if (atual === null) return;
    if (atual !== tema) {
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            botao.click(),
        ]);
        await page.goto(`${V3}/perfil`, { waitUntil: 'load' });
    }
}

test('FLOW-RMA-003 - Troca V1→V2 e V2→V1 pelo menu, com persistência', async ({ browser }) => {
    // Garante ponto de partida V1.
    let page = await login(browser);
    await garantirTema(page, 'v1');

    // V1 → V2 pelo novo item de menu (POST/CSRF) em rota canônica.
    await page.goto(`${V3}/parceiros/fornecedores`, { waitUntil: 'load' });
    await expect(page.locator('#FIXADO')).toBeVisible();
    const trocarV1 = page.locator('button.menu-trocar-tema:has-text("Trocar p/ 15.8.1")');
    await expect(trocarV1).toBeVisible();
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        trocarV1.click(),
    ]);
    await expect(page.locator('.header-v2')).toBeVisible();
    await page.context().close();

    // Relogin: preferência V2 permanece.
    page = await login(browser);
    await page.goto(`${V3}/parceiros/fornecedores`, { waitUntil: 'load' });
    await expect(page.locator('.header-v2')).toBeVisible();

    // V2 → V1 pelo dropdown do tema.
    await page.locator('li.dropdown a.dropdown-toggle').click();
    const trocarV2 = page.locator('form button.link-como-item-dropdown:has-text("Trocar p/ 14.6.1")');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        trocarV2.click(),
    ]);
    await expect(page.locator('#FIXADO')).toBeVisible();
    await expect(page.locator('button.menu-trocar-tema:has-text("Trocar p/ 15.8.1")')).toBeVisible();
    await page.context().close();

    // Relogin: preferência V1 permanece.
    page = await login(browser);
    await page.goto(`${V3}/parceiros/fornecedores`, { waitUntil: 'load' });
    await expect(page.locator('#FIXADO')).toBeVisible();
    await page.context().close();
});
