import { test, expect, type Page } from '@playwright/test';

/**
 * UX-004 / P8 - Contrato Transversal de Feedback, Erros HTTP (403, 404) e Estados.
 */
const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

test.setTimeout(120_000);

async function login(page: Page, email = 'operador@rma.local'): Promise<void> {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'load' });
    await page.fill('#email', email);
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('Contrato Transversal - Pagina 404 estruturada com navegacao de retorno', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    // 1. Acessa rota inexistente
    const response = await page.goto(`${BASE_URL}/rota-inexistente-404-qa`, { waitUntil: 'load' });
    expect(response?.status()).toBe(404);

    await expect(page.locator('.erro-codigo')).toHaveText('404');
    await expect(page.locator('.erro-titulo')).toHaveText('Página ou Registro Não Encontrado');

    // 2. Clica no botao de retorno seguro
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('a:has-text("Voltar à Página Inicial")'),
    ]);

    // Retornou para o sistema logado
    expect(page.url()).not.toContain('rota-inexistente');
});
