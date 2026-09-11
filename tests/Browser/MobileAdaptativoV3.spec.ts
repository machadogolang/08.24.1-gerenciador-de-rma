import { test, expect, type Page } from '@playwright/test';

/**
 * T3-18 - Mobile e Adaptabilidade de Browser no Tema V3.
 * Valida o comportamento em viewports Mobile (375x667), Tablet (768x1024) e Desktop (1440x900).
 */
const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

test.setTimeout(120_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test.describe('Tema V3 - Adaptabilidade Multi-Viewport e Cartoes Mobile (T3-18)', () => {
    test('Viewport Mobile (375x667) - Drawer, cartoes de RMA, usuarios e parceiros', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await login(page);

        // 1. Dashboard no mobile
        await page.goto(`${BASE_URL}/v3`, { waitUntil: 'load' });
        const botaoMenu = page.locator('[data-v3-drawer-abrir]');
        await expect(botaoMenu).toBeVisible();

        // Abre drawer
        await botaoMenu.click();
        const drawer = page.locator('[data-v3-drawer]');
        await expect(drawer).toBeVisible();
        await expect(drawer).toHaveClass(/is-open/);

        // Fecha com tecla ESC
        await page.keyboard.press('Escape');
        await expect(drawer).not.toHaveClass(/is-open/);

        // 2. Listagem de RMAs: tabela oculta, cartoes visiveis
        await page.goto(`${BASE_URL}/v3/rmas`, { waitUntil: 'load' });
        const tabelaRma = page.locator('.tabela-v3');
        const cartoesRma = page.locator('.cartoes-rma');
        await expect(tabelaRma).toBeHidden();
        await expect(cartoesRma).toBeVisible();

        // Verifica que nao ha overflow horizontal no body
        const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
        const bodyClientWidth = await page.evaluate(() => document.body.clientWidth);
        expect(bodyScrollWidth).toBeLessThanOrEqual(bodyClientWidth + 2);

        // 3. Listagem de Parceiros Clientes: cartoes visiveis no mobile
        await page.goto(`${BASE_URL}/v3/parceiros/clientes`, { waitUntil: 'load' });
        const cartoesParceiro = page.locator('.cartoes-parceiro');
        await expect(cartoesParceiro).toBeVisible();

        // 4. Formulario de RMA no mobile: campos em 1 coluna
        await page.goto(`${BASE_URL}/v3/rmas/novo`, { waitUntil: 'load' });
        const inputDescricao = page.locator('input[name="descricao"]');
        await expect(inputDescricao).toBeVisible();
        const box = await inputDescricao.boundingBox();
        expect(box?.height).toBeGreaterThanOrEqual(40);
    });

    test('Viewport Tablet (768x1024) e Desktop (1440x900) - Rail e tabelas densas', async ({ page }) => {
        await login(page);

        // 1. Tablet (768px)
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.goto(`${BASE_URL}/v3/rmas`, { waitUntil: 'load' });

        const railTablet = page.locator('[data-v3-rail-conteudo]');
        await expect(railTablet).toBeVisible();
        const tabelaTablet = page.locator('.tabela-v3');
        await expect(tabelaTablet).toBeVisible();

        // 2. Desktop (1440px)
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto(`${BASE_URL}/v3/rmas`, { waitUntil: 'load' });

        const railDesktop = page.locator('[data-v3-rail-conteudo]');
        await expect(railDesktop).toBeVisible();
        const tabelaDesktop = page.locator('.tabela-v3');
        await expect(tabelaDesktop).toBeVisible();

        // Testa recolhimento do rail no desktop
        const toggleRail = page.locator('[data-v3-rail]');
        await toggleRail.click();
        await expect(railDesktop).toHaveClass(/is-collapsed/);
        await toggleRail.click();
        await expect(railDesktop).not.toHaveClass(/is-collapsed/);
    });
});
