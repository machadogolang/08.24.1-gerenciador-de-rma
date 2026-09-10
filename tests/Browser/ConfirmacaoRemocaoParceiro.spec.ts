import { test, expect, type Page } from '@playwright/test';

/**
 * UX-002 (P8) - a remocao de parceiro pede confirmacao (JS delegado do tema), sem
 * inline JS. Prova browser: cancelar mantem o registro; confirmar remove.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

test.setTimeout(120_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('UX-002 - remocao de parceiro pede confirmacao antes de apagar', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    const nome = `PARCEIRO UX-002 ${Date.now()}`;

    await page.goto(`${V3}/v1/parceiros/fornecedores/create`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="nome"]', nome);
    // Escopo no formulario: o shell V1 tambem tem um botao submit (Logout).
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.locator('form:has(input[name="nome"]) button[type=submit]').first().click(),
    ]);

    await page.goto(`${V3}/v1/parceiros/fornecedores`, { waitUntil: 'domcontentloaded' });
    const linha = page.locator('tbody tr', { hasText: nome });
    await expect(linha).toHaveCount(1);

    // Cancelar: nada e apagado e o botao continua habilitado (UX-004 nao pode
    // desabilitar quando o submit e abortado pela confirmacao).
    page.once('dialog', (dialogo) => dialogo.dismiss());
    await linha.locator('button:has-text("Remover")').click();
    await expect(page.locator('tbody tr', { hasText: nome })).toHaveCount(1);
    await expect(linha.locator('button:has-text("Remover")')).toBeEnabled();

    // Confirmar: registro sai da listagem.
    page.once('dialog', (dialogo) => dialogo.accept());
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        linha.locator('button:has-text("Remover")').click(),
    ]);
    await expect(page.locator('tbody tr', { hasText: nome })).toHaveCount(0);
});
