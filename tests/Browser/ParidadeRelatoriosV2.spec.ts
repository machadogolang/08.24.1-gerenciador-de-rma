import { test, expect, type Page } from '@playwright/test';

/**
 * PAR-RES-E-01..03 - relatorios RCD/RPEC/RMPE com largura integrada ao shell V2
 * e titulo no tamanho historico (sem tabela encolhida no canto).
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

async function loginV2(page: Page): Promise<void> {
    await page.setViewportSize(VIEWPORT);
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('button[type=submit]')]);
    await page.goto(`${V3}/perfil`, { waitUntil: 'load' });
    const texto = await page.locator('button:has-text("Alternar tema")').textContent();
    if (!texto?.includes('atual: v2')) {
        await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.locator('button:has-text("Alternar tema")').click()]);
    }
}

test('RCD/RPEC/RMPE no V2 usam largura do shell e titulo historico', async ({ page }) => {
    await loginV2(page);
    const rotas = ['/rmas-relatorios/rcd', '/rmas-relatorios/rpec', '/rmas-relatorios/rmpe?data_inicio=2026-01-01&data_fim=2026-12-31'];

    for (const rota of rotas) {
        await page.goto(`${V3}${rota}`, { waitUntil: 'load' });
        const tabela = page.locator('.relatorio-tabela');
        if (await tabela.count() === 0) continue;
        const caixa = await tabela.boundingBox();
        expect(caixa).not.toBeNull();
        expect(caixa!.width).toBeGreaterThanOrEqual(900);
        await expect(page.locator('h2.relatorio-titulo').first()).toHaveCSS('font-size', '18px');
        const semOverflow = await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);
        expect(semOverflow).toBe(true);
    }
});
