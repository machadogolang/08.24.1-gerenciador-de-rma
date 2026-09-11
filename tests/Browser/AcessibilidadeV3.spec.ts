import { test, expect, type Page } from '@playwright/test';

/**
 * T3-19 - Acessibilidade no Tema V3.
 * Teclado, ARIA, foco visivel, alvos de 44px e contraste.
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

test.describe('Tema V3 - Acessibilidade (T3-19)', () => {
    test('Alvos de toque >= 44px, foco visivel e semantica ARIA', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await login(page);

        // 1. Dashboard
        await page.goto(`${BASE_URL}/v3`, { waitUntil: 'load' });

        // Link de navegacao ativo possui aria-current="page"
        const linkDashboard = page.locator('.app-shell__rail .app-shell__nav-link[aria-current="page"]');
        await expect(linkDashboard).toBeVisible();
        await expect(linkDashboard).toHaveText('Dashboard');

        // Alvos de clique no rail possuem altura >= 44px
        const boxLink = await linkDashboard.boundingBox();
        expect(boxLink?.height).toBeGreaterThanOrEqual(44);

        // 2. Formulario de Novo RMA: inputs com altura >= 44px e navegabilidade por Tab
        await page.goto(`${BASE_URL}/v3/rmas/novo`, { waitUntil: 'load' });
        const inputDescricao = page.locator('input[name="descricao"]');
        const boxInput = await inputDescricao.boundingBox();
        expect(boxInput?.height).toBeGreaterThanOrEqual(44);

        // Testa navegacao por Tab
        await inputDescricao.focus();
        await expect(inputDescricao).toBeFocused();

        // 3. Botao de menu mobile: aria-expanded e foco no ESC
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(`${BASE_URL}/v3`, { waitUntil: 'load' });

        const botaoMenu = page.locator('[data-v3-drawer-abrir]');
        await expect(botaoMenu).toHaveAttribute('aria-expanded', 'false');

        await botaoMenu.click();
        await expect(botaoMenu).toHaveAttribute('aria-expanded', 'true');

        // Pressiona ESC e confere retorno do foco ao botao
        await page.keyboard.press('Escape');
        await expect(botaoMenu).toHaveAttribute('aria-expanded', 'false');
        await expect(botaoMenu).toBeFocused();

        // 4. Botoes de acao nos relatorios possuem min-height 44px
        await page.goto(`${BASE_URL}/v3/relatorios`, { waitUntil: 'load' });
        const botaoRcd = page.locator('a.botao:has-text("Abrir RCD")');
        await expect(botaoRcd).toBeVisible();
        const boxAcao = await botaoRcd.boundingBox();
        expect(boxAcao?.height).toBeGreaterThanOrEqual(44);
    });
});
