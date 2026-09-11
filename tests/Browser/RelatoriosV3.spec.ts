import { test, expect, type Page } from '@playwright/test';

/**
 * T3-15 - Relatorios no Tema V3:
 * Hub de Relatorios + navegacao RCD, RPEC, RMPE e adaptacao mobile.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

test.setTimeout(120_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('Relatorios V3 - Hub, navegacao entre relatorios e mobile', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    // 1. Acessar hub de relatorios
    await page.goto(`${V3}/v3/relatorios`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toContainText('Relatórios Operacionais e Fiscais');
    await expect(page.locator('.app-shell__rail a:has-text("Relatórios")')).toBeVisible();

    // 2. Abrir RCD via card principal
    await page.locator('a:has-text("Abrir RCD")').click();
    await expect(page).toHaveURL(/\/v3\/relatorios\/rcd$/);
    await expect(page.locator('h1')).toContainText('Relatorio de Creditos Disponiveis');

    // 3. Navegar pelas abas
    await page.locator('.segmentos a:has-text("Contagem de Estoque")').click();
    await expect(page).toHaveURL(/\/v3\/relatorios\/rpec$/);
    await expect(page.locator('h1')).toContainText('Produtos em Estoque para Contagem');

    await page.locator('.segmentos a:has-text("Produtos Encaminhados")').click();
    await expect(page).toHaveURL(/\/v3\/relatorios\/rmpe$/);
    await expect(page.locator('h1')).toContainText('Produtos Encaminhados');

    await page.locator('.segmentos a:has-text("Painel Geral")').click();
    await expect(page).toHaveURL(/\/v3\/relatorios$/);

    // 4. Testar viewport mobile (375x667)
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('.detalhe-v3__secoes')).toBeVisible();
});
