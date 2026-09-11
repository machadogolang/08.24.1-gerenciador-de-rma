import { test, expect, type Page } from '@playwright/test';

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

async function login(page: Page): Promise<void> {
    await page.setViewportSize(VIEWPORT);
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]'),
    ]);
}

test.describe('PaginacaoSemVazamentoSvg - Garante que paginacao nao vaza SVGs descontrolados (BUG-PAG-SVG-001)', () => {
    test('rmas-historico sob V1 renderiza paginacao limpa sem SVG gigante', async ({ page }) => {
        await login(page);

        // Acessa historico de modificacoes
        await page.goto(`${V3}/rmas-historico`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.Tabelinha-Table').first()).toBeVisible();

        // Se houver paginacao
        const paginacao = page.locator('.paginacao-container');
        if (await paginacao.count() > 0) {
            await expect(paginacao.first()).toBeVisible();
            await expect(paginacao.first()).toContainText('registros');
        }

        // Valida que NENHUM elemento SVG na pagina tem dimensao gigante (> 100px)
        const svgs = page.locator('svg');
        const totalSvgs = await svgs.count();
        for (let i = 0; i < totalSvgs; i++) {
            const box = await svgs.nth(i).boundingBox();
            if (box) {
                expect(box.width).toBeLessThanOrEqual(100);
                expect(box.height).toBeLessThanOrEqual(100);
            }
        }
    });

    test('historico-de-acesso renderiza paginacao canonica sem SVG gigante', async ({ page }) => {
        await login(page);

        await page.goto(`${V3}/historico-de-acesso`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.Tabelinha-Table, .table')).toBeVisible();

        const svgs = page.locator('svg');
        const totalSvgs = await svgs.count();
        for (let i = 0; i < totalSvgs; i++) {
            const box = await svgs.nth(i).boundingBox();
            if (box) {
                expect(box.width).toBeLessThanOrEqual(100);
                expect(box.height).toBeLessThanOrEqual(100);
            }
        }
    });
});
