import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * PAR-V2-THEME-01 - troca V1 <-> V2 partindo de rota QA prefixada, com
 * redirecionamento para a contraparte do outro tema e persistencia.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
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
    for (let tentativa = 0; tentativa < 4; tentativa++) {
        const texto = await page.locator('button:has-text("Alternar tema")').textContent();
        const atual = texto?.includes('atual: v1') ? 'v1' : texto?.includes('atual: v2') ? 'v2' : null;
        if (atual === tema) return;
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.locator('button:has-text("Alternar tema")').click(),
        ]);
        await page.goto(`${V3}/perfil`, { waitUntil: 'load' });
    }
    throw new Error(`Nao consegui garantir tema ${tema}`);
}

test('PAR-V2-THEME-01 - /v2/rma/{id} -> V1 e /v1/rma/{id} -> V2 com persistencia', async ({ browser }) => {
    let page = await login(browser);
    await garantirTema(page, 'v2');

    await page.goto(`${V3}/v2/rma/3`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.header-v2')).toBeVisible();

    await page.locator('.nav-v2 li.dropdown a.dropdown-toggle').click();
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.locator('.nav-v2 .dropdown-menu button.link-como-item-dropdown:has-text("Trocar p/ 14.6.1")').click(),
    ]);

    expect(page.url()).toMatch(/\/v1\/rma\/3$/);
    await expect(page.locator('#FIXADO')).toBeVisible();
    await expect(page.locator('.header-v2')).toHaveCount(0);
    await page.context().close();

    // V1 -> V2 pela mesma rota de detalhe (menu de sessoes precisa abrir para o
    // botao "Trocar p/ 15.8.1" ficar visivel no Tema V1).
    page = await login(browser);
    await garantirTema(page, 'v1');
    await page.goto(`${V3}/v1/rma/3`, { waitUntil: 'domcontentloaded' });
    await page.locator('#menu-sessao').click();
    const botao = page.locator('button.menu-trocar-tema:has-text("Trocar p/ 15.8.1")');
    await expect(botao).toBeVisible();
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        botao.click(),
    ]);

    expect(page.url()).toMatch(/\/v2\/rma\/3$/);
    await expect(page.locator('.header-v2')).toBeVisible();
    await expect(page.locator('#FIXADO')).toHaveCount(0);
    await page.context().close();

    // Persistencia apos novo login: preferencia final e V2.
    page = await login(browser);
    await page.goto(`${V3}/parceiros/fornecedores`, { waitUntil: 'load' });
    await expect(page.locator('.header-v2')).toBeVisible();
    await page.context().close();
});
