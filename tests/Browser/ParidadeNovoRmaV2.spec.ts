import { test, expect, type Page } from '@playwright/test';

/**
 * PAR-V2-NOVO-01 - Novo RMA inline no painel/aba do Tema V2: formulario real,
 * condicional por Origem, CRIAR BD e redirecionamento para o detalhe.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 1000 };

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

test.beforeEach(async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);
});

test('aba Novo V2 cria RMA inline pela composicao 15.8.1 e cai no detalhe', async ({ page }) => {
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    await page.click('a[href="#novo_rma"]');

    const formulario = page.locator('#novo_rma form');
    await expect(formulario.locator('button[type="submit"]')).toContainText('CRIAR BD');
    await expect(formulario.locator('input[name="descricao"]')).toBeVisible();

    // Condicional do Legacy: Cliente mostra NF Venda; outra origem mostra NF Compra.
    await formulario.locator('select[name="origem"]').selectOption('Cliente');
    await expect(page.locator('#novo_rma #cli')).toBeVisible();
    await expect(page.locator('#novo_rma #outraorigem')).toBeHidden();
    await formulario.locator('select[name="origem"]').selectOption('Loja');
    await expect(page.locator('#novo_rma #outraorigem')).toBeVisible();
    await expect(page.locator('#novo_rma #cli')).toBeHidden();

    const descricao = `Novo RMA inline QA ${Date.now()}`;
    await formulario.locator('select[name="origem"]').selectOption('Cliente');
    await formulario.locator('input[name="descricao"]').fill(descricao);
    await formulario.locator('input[name="modelo"]').fill('MODELO INLINE PW');
    await formulario.locator('input[name="sn"]').fill('SN-INLINE-PW');
    await formulario.locator('select[name="prioridade"]').selectOption('alta');
    await formulario.locator('input[name="nfvenda"]').fill('NFV-PW');
    await formulario.locator('input[name="nfvenda_emissao"]').fill('05/09/2026');
    await formulario.locator('input[name="nfvenda_chave"]').fill('CHAVE-NFV-PW');
    await formulario.locator('input[name="cliente_nome"]').fill('Cliente Inline PW');
    await formulario.locator('textarea[name="defeito"]').fill('Defeito novo RMA inline PW');
    await formulario.locator('textarea[name="observacao"]').fill('Observacao novo RMA inline PW.');

    await Promise.all([
        page.waitForURL(/\/v2\/rma\/\d+$/),
        formulario.locator('button[type="submit"]').click(),
    ]);

    await expect(page.locator('form.detalhe-rma-v2__form input[name="descricao"]')).toHaveValue(descricao);
    await expect(page.locator('form.detalhe-rma-v2__form input[name="modelo"]')).toHaveValue('MODELO INLINE PW');
    await expect(page.locator('form.detalhe-rma-v2__form select[name="prioridade"]')).toHaveValue('alta');
});
