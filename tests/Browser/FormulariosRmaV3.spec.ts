import { test, expect, type Page } from '@playwright/test';

/**
 * T3-12 - formularios Novo/Editar RMA no Tema V3: criar pela listagem, editar
 * pelo detalhe e conferir persistencia. V3 continua oculto (acesso por /v3).
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

test('Novo RMA V3 cria em secoes e Editar persiste apos reload', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    await page.goto(`${V3}/v3/rmas`, { waitUntil: 'load' });
    await page.locator('a:has-text("Novo RMA")').click();
    await page.waitForURL(/\/v3\/rmas\/novo$/);

    await expect(page.locator('h1')).toHaveText('Novo RMA');
    await expect(page.locator('section.cartao')).toHaveCount(5);

    const descricao = `RMA V3 formulario QA ${Date.now()}`;
    await page.fill('input[name="descricao"]', descricao);
    await page.fill('input[name="modelo"]', 'MODELO V3 PW');
    await page.fill('input[name="sn"]', 'SN-V3-PW');
    await page.selectOption('select[name="origem"]', 'Cliente');
    await page.selectOption('select[name="prioridade"]', 'alta');
    await page.fill('textarea[name="defeito"]', 'Defeito do formulario V3 PW');
    await page.fill('textarea[name="observacao"]', 'Observacao do formulario V3 PW.');

    await Promise.all([
        page.waitForURL(/\/v3\/rma\/\d+$/),
        page.click('.form-v3 button[type="submit"]'),
    ]);

    await expect(page.locator('.detalhe-v3__numero')).toBeVisible();
    await expect(page.locator('.detalhe-v3__secoes')).toContainText(descricao);

    await page.click('a:has-text("Editar RMA")');
    await page.waitForURL(/\/v3\/rma\/\d+\/editar$/);
    await page.fill('input[name="modelo"]', 'MODELO EDITADO V3 PW');
    await page.fill('textarea[name="observacao"]', 'Observacao editada e persistida V3 PW.');
    await Promise.all([
        page.waitForURL(/\/v3\/rma\/\d+$/),
        page.click('.form-v3 button[type="submit"]'),
    ]);

    await expect(page.locator('.detalhe-v3__secoes')).toContainText('MODELO EDITADO V3 PW');
    await expect(page.locator('.detalhe-v3__secoes')).toContainText('Observacao editada e persistida V3 PW.');
});

test('formulario V3 empilha em 1 coluna no mobile sem overflow', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await login(page);

    await page.goto(`${V3}/v3/rmas/novo`, { waitUntil: 'load' });
    const semOverflow = await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);
    expect(semOverflow).toBe(true);
    await expect(page.locator('.form-v3__grade').first()).toBeVisible();
});
