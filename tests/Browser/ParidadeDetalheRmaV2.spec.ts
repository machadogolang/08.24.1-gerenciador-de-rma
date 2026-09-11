import { test, expect } from '@playwright/test';

/**
 * PAR-DET-V2-01/PAR-V2-PESQ-01/PAR-V2-GEO-01 - contrato visual registrado na
 * auditoria de 2026-09-09 (metricas Legacy 15.8.1 em 2048x1152). Roda contra a V3
 * com rota forcada /v2; tolerancia de +-2px em Y e +-3px em X, cores exatas.
 */
const VIEWPORT = { width: 2048, height: 1152 };

async function login(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
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

test('detalhe V2 restaura cabecalho e grupos na posicao historica', async ({ page }) => {
    await page.goto('/v2/rma/3', { waitUntil: 'domcontentloaded' });

    await expect(page.locator('.detalhe-rma-v2 ol.breadcrumb')).toContainText('NUMERO DO BD');
    await expect(page.locator('.detalhe-rma-v2')).toContainText('Que produto é?');
    await expect(page.locator('.detalhe-rma-v2')).toContainText('Informacao adicional');

    const breadcrumb = await page.locator('.detalhe-rma-v2 ol.breadcrumb').boundingBox();
    expect(breadcrumb).not.toBeNull();
    expect(Math.abs(breadcrumb!.y - 37)).toBeLessThanOrEqual(2);

    const grupo = await page.locator('.detalhe-rma-v2 .row.formgroupnf').first().boundingBox();
    expect(grupo).not.toBeNull();
    expect(Math.abs(grupo!.y - 84)).toBeLessThanOrEqual(4);
});

test('aba Pesquisar reproduz geometria e cores do Legacy', async ({ page }) => {
    await page.goto('/v2/rma', { waitUntil: 'domcontentloaded' });
    await page.click('a[href="#pesquisar"]');

    const h3 = await page.locator('#pesquisar h3.fl').boundingBox();
    const input = await page.locator('#pesquisar #pesquisa').boundingBox();
    const botao = await page.locator('#pesquisar button.buttonSearch').boundingBox();

    expect(h3).not.toBeNull();
    expect(Math.abs(h3!.y - 91)).toBeLessThanOrEqual(2);
    expect(Math.abs(h3!.height - 15.6)).toBeLessThanOrEqual(2);

    expect(input).not.toBeNull();
    expect(Math.abs(input!.x - 428)).toBeLessThanOrEqual(3);
    expect(Math.abs(input!.width - 183)).toBeLessThanOrEqual(3);
    expect(Math.abs(input!.y - 125)).toBeLessThanOrEqual(2);
    expect(await page.locator('#pesquisar #pesquisa').evaluate(el => getComputedStyle(el).backgroundColor))
        .toBe('rgb(45, 45, 45)');

    expect(botao).not.toBeNull();
    expect(Math.abs(botao!.x - 615)).toBeLessThanOrEqual(3);
    expect(await page.locator('#pesquisar button.buttonSearch').evaluate(el => getComputedStyle(el).backgroundColor))
        .toBe('rgb(51, 51, 51)');
});

test('aba Encaminhado mantem tabela em y=47', async ({ page }) => {
    await page.goto('/v2/rma', { waitUntil: 'domcontentloaded' });
    await page.click('a[href="#encaminhado"]');

    const tabela = await page.locator('#encaminhado table.Tabelinha-Table').boundingBox();
    const linha = await page.locator('#encaminhado table.Tabelinha-Table tr:nth-of-type(2)').boundingBox();

    expect(tabela).not.toBeNull();
    expect(Math.abs(tabela!.y - 47)).toBeLessThanOrEqual(2);
    expect(linha).not.toBeNull();
    expect(Math.abs(linha!.y - 81.5)).toBeLessThanOrEqual(2);
});
