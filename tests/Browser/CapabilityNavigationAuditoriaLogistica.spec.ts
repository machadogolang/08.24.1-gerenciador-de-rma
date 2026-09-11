import { test, expect } from '@playwright/test';

async function garantirTema(page: any, tema: 'v1' | 'v2'): Promise<void> {
    await page.goto('/perfil', { waitUntil: 'load' });
    for (let tentativa = 0; tentativa < 4; tentativa++) {
        const texto = await page.locator('button:has-text("Alternar tema")').textContent();
        const atual = texto?.includes('atual: v1') ? 'v1' : texto?.includes('atual: v2') ? 'v2' : null;
        if (atual === tema) return;
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.locator('button:has-text("Alternar tema")').click(),
        ]);
        await page.goto('/perfil', { waitUntil: 'load' });
    }
}

test.describe('CapabilityNavigationAuditoriaLogistica - Auditoria e Logística (V1 e V2)', () => {
    test('Cenário A (V1): Controle e Transporte Porto Alegre via menu lateral V1', async ({ page }) => {
        // 1. Login superadministrador (acesso a controle)
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await garantirTema(page, 'v1');

        await page.goto('/rmas');
        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();

        // 2. Acessar Controle (Auditoria de logs)
        const linkControle = page.locator('#JS-Sessao a.lisessao', { hasText: 'Controle' });
        await expect(linkControle).toBeVisible();
        await linkControle.click();
        await expect(page).toHaveURL(/\/(rmas-)?controle/);
        await expect(page.locator('.JS-DivLEFT')).toBeVisible();

        // 3. Acessar Logística (Transp. Porto Alegre)
        const linkTransp = page.locator('#JS-Sessao a.lisessao', { hasText: 'Transp. Porto Alegre' });
        await expect(linkTransp).toBeVisible();
        await linkTransp.click();
        await expect(page).toHaveURL(/\/(rmas-logistica\/frete-porto-alegre|v1\/logistica\/porto-alegre)/);
        await expect(page.locator('.JS-DivLEFT')).toBeVisible();
    });

    test('Cenário B (V2): Controle e Transporte Porto Alegre via dropdown Menu V2', async ({ page }) => {
        // 1. Login superadministrador
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await garantirTema(page, 'v2');

        await page.goto('/v2/rma');

        // 2. Dropdown Menu -> Controle
        let menuDropdown = page.locator('li.dropdown > a.dropdown-toggle', { hasText: 'Menu' });
        await menuDropdown.click();

        const linkControle = page.locator('ul.dropdown-menu a', { hasText: 'Controle' });
        await expect(linkControle).toBeVisible();
        await linkControle.click();
        await expect(page).toHaveURL(/\/v2\/controle/);

        // 3. Dropdown Menu -> Transp. Porto Alegre
        menuDropdown = page.locator('li.dropdown > a.dropdown-toggle', { hasText: 'Menu' });
        await menuDropdown.click();

        const linkTransp = page.locator('ul.dropdown-menu a', { hasText: 'Transp. Porto Alegre' });
        await expect(linkTransp).toBeVisible();
        await linkTransp.click();
        await expect(page).toHaveURL(/\/v2\/logistica\/(frete-)?porto-alegre/);
    });
});
