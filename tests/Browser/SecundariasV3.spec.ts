import { test, expect, type Page } from '@playwright/test';

/**
 * T3-16 - Telas Secundarias no Tema V3:
 * Alertas, historicos, logistica, ajuda, perfil e creditos.
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

test('Secundarias V3 - Alertas, historico, creditos, ajuda e perfil', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    // 1. Alertas
    await page.goto(`${V3}/v3/alertas`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toHaveText('Painel de Alertas');

    // 2. Historico de Modificacao
    await page.goto(`${V3}/v3/rmas-historico`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toHaveText('Histórico de Modificações de RMA');

    // 3. Creditos
    await page.goto(`${V3}/v3/creditos`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toHaveText('Gestão de Créditos');

    // 4. Ajuda
    await page.goto(`${V3}/v3/ajuda`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toHaveText('Central de Ajuda e Procedimentos');

    // 5. Perfil
    await page.goto(`${V3}/v3/perfil`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toHaveText('Meu Perfil');

    // 6. Viewport mobile em creditos
    await page.goto(`${V3}/v3/creditos`, { waitUntil: 'load' });
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('h1')).toBeVisible();
});
