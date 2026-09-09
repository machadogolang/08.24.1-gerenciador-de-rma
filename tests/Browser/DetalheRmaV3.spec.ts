import { expect, type Page, test } from '@playwright/test';

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('detalhe V3 renderiza cabecalho e secoes sem overflow no desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    await page.goto(`${V3}/v3/rma/3`, { waitUntil: 'load' });

    await expect(page.locator('.detalhe-v3__numero')).toBeVisible();
    await expect(page.locator('.detalhe-v3__meta')).toContainText('Proxima acao');
    await expect(page.locator('.detalhe-v3__secoes > section')).toHaveCount(7);
    await expect(page.locator('.detalhe-v3__secoes')).toContainText('Fiscal');
    await expect(page.locator('.detalhe-v3__secoes')).toContainText('Historico e auditoria');

    const semOverflow = await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);
    expect(semOverflow).toBe(true);
});

test('listagem V3 leva ao detalhe e o mobile empilha as secoes', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await login(page);

    await page.goto(`${V3}/v3/rmas`, { waitUntil: 'load' });
    await page.locator('.cartoes-rma a').first().click();
    await page.waitForURL(/\/v3\/rma\/\d+$/);

    await expect(page.locator('.detalhe-v3__secoes > section').first()).toBeVisible();
    const primeiro = await page.locator('.detalhe-v3__secoes > section').first().boundingBox();
    const segundo = await page.locator('.detalhe-v3__secoes > section').nth(1).boundingBox();
    expect(primeiro).not.toBeNull();
    expect(segundo).not.toBeNull();
    expect(segundo!.y).toBeGreaterThanOrEqual(primeiro!.y + primeiro!.height - 2);
});
