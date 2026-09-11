import { test, expect } from '@playwright/test';

test.describe('CapabilityNavigationRelatorios - Jornada Real de Relatórios (V1 e V2)', () => {
    test('Cenário A (V1): Acesso a RCD, RPEC, RMPE e Estatísticas Gerais via menu lateral', async ({ page }) => {
        // 1. Login supervisor (V1)
        await page.goto('/login');
        await page.fill('#email', 'supervisor@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await page.goto('/rmas');
        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();

        const relatoriosV1 = [
            { texto: 'Relatórios (RCD)', urlParte: '/rmas-relatorios/rcd' },
            { texto: 'Relatório RPEC', urlParte: '/rmas-relatorios/rpec' },
            { texto: 'Relatório RMPE', urlParte: '/rmas-relatorios/rmpe' },
            { texto: 'Estatísticas Gerais', urlParte: '/relatorios' },
        ];

        for (const rel of relatoriosV1) {
            const link = page.locator('#JS-Sessao a.lisessao', { hasText: rel.texto });
            await expect(link).toBeVisible();
            await link.click();
            await expect(page).toHaveURL(new RegExp(rel.urlParte));
            await expect(page.locator('.JS-DivLEFT')).toBeVisible();
        }
    });

    test('Cenário B (V2): Relatórios via Menu Dropdown e rotas de compatibilidade RCD/RPEC/RMPE', async ({ page }) => {
        // 1. Login superadministrador (V2)
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await page.goto('/v2/rma');

        // 2. Dropdown Menu -> Relatórios
        const menuDropdown = page.locator('li.dropdown > a.dropdown-toggle', { hasText: 'Menu' });
        await menuDropdown.click();

        const linkRelatorios = page.locator('ul.dropdown-menu a', { hasText: 'Relatorios' });
        await expect(linkRelatorios).toBeVisible();
        await linkRelatorios.click();
        await expect(page).toHaveURL(/\/v2\/relatorios/);

        // 3. Rotas especializadas RCD, RPEC e RMPE no tema V2
        for (const tipo of ['rcd', 'rpec', 'rmpe']) {
            await page.goto(`/v2/relatorios/${tipo}`);
            await expect(page).toHaveURL(new RegExp(`/v2/relatorios/${tipo}`));
            await expect(page.locator('body')).toBeVisible();
        }
    });
});
