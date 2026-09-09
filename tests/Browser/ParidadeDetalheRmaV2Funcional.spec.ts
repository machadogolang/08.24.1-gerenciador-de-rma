import { test, expect, type Page } from '@playwright/test';

/**
 * PAR-V2-DETAIL-02 - prova browser do detalhe RMA V2 como formulario operacional:
 * criar, alterar campos reais, salvar pelo rodape e conferir persistencia no reload.
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

async function criarRmaV2(page: Page, descricao: string): Promise<number> {
    await page.goto(`${V3}/v2/rma/create`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="descricao"]', descricao);
    await page.fill('textarea[name="defeito"]', 'Defeito do detalhe funcional V2');
    await page.selectOption('select[name="origem"]', 'Loja');
    await Promise.all([
        page.waitForURL(/\/v2\/rma\/\d+$/),
        page.click('.shell-v2 .container button[type="submit"]'),
    ]);
    const id = Number(page.url().match(/\/v2\/rma\/(\d+)$/)?.[1]);
    expect(Number.isInteger(id)).toBe(true);
    return id;
}

test.beforeEach(async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);
});

test('detalhe RMA V2 edita controles reais, salva e persiste apos reload', async ({ page }) => {
    const id = await criarRmaV2(page, `Detalhe V2 funcional QA ${Date.now()}`);
    await page.goto(`${V3}/v2/rma/${id}`, { waitUntil: 'domcontentloaded' });

    await expect(page.locator('form.detalhe-rma-v2__form')).toBeVisible();
    await page.fill('form.detalhe-rma-v2__form input[name="modelo"]', 'MODELO-EDITADO-PLAYWRIGHT');
    await page.fill('form.detalhe-rma-v2__form input[name="os"]', 'OS-EDITADA-PW');
    await page.selectOption('form.detalhe-rma-v2__form select[name="origem"]', 'Cliente');
    await page.selectOption('form.detalhe-rma-v2__form select[name="prioridade"]', 'normal');
    await page.selectOption('form.detalhe-rma-v2__form select[name="marcarestoque"]', '0');
    await page.selectOption('form.detalhe-rma-v2__form select[name="credito_disponivel"]', '1');
    await page.fill('form.detalhe-rma-v2__form textarea[name="observacao"]', 'Observacao persistida pelo detalhe funcional V2.');

    await Promise.all([
        page.waitForResponse((resposta) => resposta.request().method() === 'POST' && resposta.url().includes(`/rma/${id}`)),
        page.click('.detalhe-rma-v2__acoes-finais button[type="submit"]'),
    ]);
    await page.waitForLoadState('domcontentloaded');

    expect(await page.inputValue('form.detalhe-rma-v2__form input[name="modelo"]')).toBe('MODELO-EDITADO-PLAYWRIGHT');
    expect(await page.inputValue('form.detalhe-rma-v2__form input[name="os"]')).toBe('OS-EDITADA-PW');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="origem"]')).toBe('Cliente');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="prioridade"]')).toBe('normal');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="marcarestoque"]')).toBe('0');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="credito_disponivel"]')).toBe('1');
    expect(await page.inputValue('form.detalhe-rma-v2__form textarea[name="observacao"]')).toContain('Observacao persistida pelo detalhe funcional V2.');

    await page.reload({ waitUntil: 'domcontentloaded' });
    expect(await page.inputValue('form.detalhe-rma-v2__form input[name="modelo"]')).toBe('MODELO-EDITADO-PLAYWRIGHT');
    expect(await page.inputValue('form.detalhe-rma-v2__form input[name="os"]')).toBe('OS-EDITADA-PW');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="origem"]')).toBe('Cliente');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="prioridade"]')).toBe('normal');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="marcarestoque"]')).toBe('0');
    expect(await page.inputValue('form.detalhe-rma-v2__form select[name="credito_disponivel"]')).toBe('1');
    expect(await page.inputValue('form.detalhe-rma-v2__form textarea[name="observacao"]')).toContain('Observacao persistida pelo detalhe funcional V2.');
});
