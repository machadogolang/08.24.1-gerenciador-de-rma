import { test, expect, type Page } from '@playwright/test';

/**
 * T3-17 - Selecao Explicita de Tema Visual (V1, V2 e V3).
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

test('Selecao Explicita de Tema - Alternancia e Selecao Direta a partir do V3', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    // 1. Acessa perfil no Tema V3
    await page.goto(`${BASE_URL}/v3/perfil`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toHaveText('Meu Perfil');
    await expect(page.locator('#seletor-temas')).toBeVisible();
    await expect(page.locator('#seletor-temas')).toContainText('Seleção Explícita de Tema Visual');
    await expect(page.locator('#seletor-temas')).toContainText('Tema V1 (14.6.1)');
    await expect(page.locator('#seletor-temas')).toContainText('Tema V2 (15.8.1)');
    await expect(page.locator('#seletor-temas')).toContainText('Tema V3 (Console)');

    // 2. Clica para ativar Tema V1
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('#seletor-temas button:has-text("Ativar Tema V1")'),
    ]);

    // Valida que foi para a pagina inicial do Tema V1 (rmas-entrada)
    expect(page.url()).toContain('/rmas-entrada');

    // 3. Retorna para o perfil no Tema V3
    await page.goto(`${BASE_URL}/v3/perfil`, { waitUntil: 'load' });

    // 4. Clica para ativar Tema V2
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('#seletor-temas button:has-text("Ativar Tema V2")'),
    ]);

    // Valida que foi para a listagem/visao do Tema V2 (rmas)
    expect(page.url()).toContain('/rmas');
});
