import { expect, type Page, test } from '@playwright/test';

/**
 * PAR-DET-V1-EDIT-01/ACTION-01/STOCK-01 - prova real de edicao inline do detalhe V1:
 * digitar, salvar, recarregar e conferir persistencia; checkbox de estoque; rodape
 * esquerda/direita.
 */
async function login(page: Page): Promise<void> {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

async function criarRma(page: Page, descricao: string): Promise<number> {
    await page.goto('/v1/rma/create', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="descricao"]', descricao);
    await page.fill('input[name="defeito"]', 'Defeito para teste de edicao inline');
    await Promise.all([
        page.waitForURL(/\/rmas\/\d+$/),
        page.click('.formButtonEnviarNovo'),
    ]);
    const id = Number(page.url().match(/\/rmas\/(\d+)$/)?.[1]);
    expect(Number.isInteger(id)).toBe(true);
    return id;
}

test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await login(page);
});

test('detalhe V1 edita input real, salva e persiste apos reload', async ({ page }) => {
    const id = await criarRma(page, `Edicao inline QA ${Date.now()}`);
    await page.goto(`/v1/rma/${id}`, { waitUntil: 'domcontentloaded' });

    await page.fill('.detalhe-bd-form input[name="os"]', 'OS-PLAYWRIGHT');
    await page.fill('.detalhe-bd-form input[name="nfcompra_chave"]', 'CHAVE-NF-PLAYWRIGHT');
    await page.fill('.detalhe-bd-form input[name="valor"]', '88,50');
    await page.uncheck('.detalhe-bd-form input[type="checkbox"][name="marcarestoque"]');
    await page.check('.detalhe-bd-form input[type="checkbox"][name="credito_disponivel"]');
    await page.fill('.detalhe-bd-form textarea[name="observacao"]', 'Observacao longa com quebra de linha e persistencia QA.');

    await Promise.all([
        page.waitForResponse((resposta) => resposta.request().method() === 'POST' && resposta.url().includes(`/rma/${id}`)),
        page.click('.detalhe-bd-rodape__direita button[type="submit"]'),
    ]);
    await page.waitForLoadState('domcontentloaded');

    expect(await page.inputValue('.detalhe-bd-form input[name="os"]')).toBe('OS-PLAYWRIGHT');
    expect(await page.inputValue('.detalhe-bd-form input[name="nfcompra_chave"]')).toBe('CHAVE-NF-PLAYWRIGHT');
    expect(await page.inputValue('.detalhe-bd-form input[name="valor"]')).toBe('88.50');
    expect(await page.locator('.detalhe-bd-form input[type="checkbox"][name="marcarestoque"]').isChecked()).toBe(false);
    expect(await page.locator('.detalhe-bd-form input[type="checkbox"][name="credito_disponivel"]').isChecked()).toBe(true);
    await expect(page.locator('.checkbox-v1-detalhe span[data-texto-falso]').first()).toHaveText('ITEM NAO E DO ESTOQUE');
    await expect(page.locator('.checkbox-v1-detalhe span[data-texto-true]').last()).toHaveText('CREDITO DISPONIVEL');

    await page.reload({ waitUntil: 'domcontentloaded' });
    expect(await page.inputValue('.detalhe-bd-form input[name="os"]')).toBe('OS-PLAYWRIGHT');
    expect(await page.inputValue('.detalhe-bd-form input[name="nfcompra_chave"]')).toBe('CHAVE-NF-PLAYWRIGHT');
    expect(await page.inputValue('.detalhe-bd-form input[name="valor"]')).toBe('88.50');
    expect(await page.locator('.detalhe-bd-form input[type="checkbox"][name="marcarestoque"]').isChecked()).toBe(false);
    expect(await page.locator('.detalhe-bd-form input[type="checkbox"][name="credito_disponivel"]').isChecked()).toBe(true);
    expect(await page.inputValue('.detalhe-bd-form textarea[name="observacao"]')).toContain('Observacao longa com quebra de linha e persistencia QA.');

    const numero = page.locator('.Tabelinha-Table input[disabled]').first();
    await expect(numero).toBeDisabled();
    expect(await numero.inputValue()).not.toBe('');
});

test('rodape operacional do detalhe V1 despacha acao do seletor', async ({ page }) => {
    const id = await criarRma(page, `Acao rodape QA ${Date.now()}`);
    await page.goto(`/v1/rma/${id}`, { waitUntil: 'domcontentloaded' });

    await expect(page.locator('.detalhe-bd-form select[name="acao"] option[value="receber"]')).toHaveCount(1);
    await page.selectOption('.detalhe-bd-form select[name="acao"]', 'receber');
    await Promise.all([
        page.waitForResponse((resposta) => resposta.request().method() === 'POST' && resposta.url().includes(`/rma/${id}`)),
        page.click('.detalhe-bd-rodape__direita button[type="submit"]'),
    ]);
    await page.waitForLoadState('domcontentloaded');

    await expect(page.locator('.detalhe-bd-form select[name="acao"] option[value="receber"]')).toHaveCount(0);
    await expect(page.locator('.detalhe-bd-form select[name="acao"] option[value="encaminhar"]')).toHaveCount(1);
});

test('rodape do detalhe V1 mantem controles esquerda/direita alinhados', async ({ page }) => {
    const id = await criarRma(page, `Rodape alinhamento QA ${Date.now()}`);
    await page.goto(`/v1/rma/${id}`, { waitUntil: 'domcontentloaded' });

    const esquerda = await page.locator('.detalhe-bd-rodape__esquerda').boundingBox();
    const direita = await page.locator('.detalhe-bd-rodape__direita').boundingBox();
    const container = await page.locator('.detalhe-bd-form').boundingBox();
    expect(esquerda).not.toBeNull();
    expect(direita).not.toBeNull();
    expect(container).not.toBeNull();

    expect(esquerda!.x).toBeLessThan(direita!.x);
    expect(direita!.x + direita!.width).toBeLessThanOrEqual(container!.x + container!.width + 2);
    expect(Math.abs(esquerda!.y - direita!.y)).toBeLessThan(60);
});
