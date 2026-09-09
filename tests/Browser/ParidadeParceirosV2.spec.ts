import { test, expect, type Page } from '@playwright/test';

/**
 * PAR-RES-D-01..04 - formularios V2 de parceiros na composicao historica:
 * grade de colunas, controles escuros, campos lado a lado e botao Cadastrar.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

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

test('parceiros V2 create usa grade historica com controles escuros', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    for (const tipo of ['fornecedores', 'fabricantes', 'clientes', 'assistencias-tecnicas']) {
        await page.goto(`${V3}/v2/parceiros/${tipo}/create`, { waitUntil: 'load' });
        await expect(page.locator('form.form-parceiro-v2 form, .form-parceiro-v2 form')).toBeVisible();
        const colunas = await page.locator('.form-parceiro-v2 .row > .col-md-4').count();
        expect(colunas).toBeGreaterThanOrEqual(2);
        const fundo = await page.locator('.form-parceiro-v2 input[name="nome"]').evaluate((el) => getComputedStyle(el).backgroundColor);
        expect(fundo).not.toBe('rgb(255, 255, 255)');
        await expect(page.locator('.form-parceiro-v2 button[type="submit"]')).toContainText('Cadastrar');
    }
});
