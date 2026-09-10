import { test, expect } from '@playwright/test';

test.describe('CapabilityNavigationIdentidade - Click-Through de Usuários', () => {
    test('Cenário A: fluxo canônico com usuário de preferência V1 (supervisor) -> MENU -> Usuários', async ({ page }) => {
        const consoleErrors: string[] = [];
        const failedRequests: string[] = [];

        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });
        page.on('requestfailed', request => {
            failedRequests.push(`${request.method()} ${request.url()} - ${request.failure()?.errorText}`);
        });

        // 1. Login como supervisor (preferência V1)
        await page.goto('/login');
        await page.fill('#email', 'supervisor@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        // 2. Abrir página inicial
        await page.goto('/rmas');
        await expect(page.locator('#menu-sessao')).toBeVisible();

        // 3. Clicar MENU para expandir #JS-Sessao
        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();

        // 4. Localizar link Usuários e validar href
        const linkUsuarios = page.locator('#JS-Sessao a.lisessao', { hasText: 'Usuários' });
        await expect(linkUsuarios).toBeVisible();
        const href = await linkUsuarios.getAttribute('href');
        expect(href).toMatch(/\/(v1\/)?usuarios/);

        // 5. Clicar e aguardar navegação
        const responsePromise = page.waitForResponse(res => res.url().includes('usuarios') && res.status() === 200);
        await linkUsuarios.click();
        const response = await responsePromise;

        // 6. Validações da navegação final
        expect(response.status()).toBe(200);
        await expect(page).toHaveURL(/\/(v1\/)?usuarios/);
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('table.tabela-usuarios-v1')).toBeVisible();
        await expect(page.locator('text=supervisor@rma.local')).toBeVisible();

        expect(consoleErrors).toHaveLength(0);
        expect(failedRequests).toHaveLength(0);
    });

    test('Cenário B: fluxo forçado V1 (/v1/rma) -> MENU -> Usuários', async ({ page }) => {
        // 1. Login como superadministrador
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        // 2. Abrir rota forçada V1
        await page.goto('/v1/rma');
        await expect(page.locator('#menu-sessao')).toBeVisible();

        // 3. Clicar MENU
        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();

        // 4. Clicar Usuários
        const linkUsuarios = page.locator('#JS-Sessao a.lisessao', { hasText: 'Usuários' });
        await expect(linkUsuarios).toBeVisible();
        const responsePromise = page.waitForResponse(res => res.url().includes('/v1/usuarios') && res.status() === 200);
        await linkUsuarios.click();
        const response = await responsePromise;

        // 5. Validações
        expect(response.status()).toBe(200);
        await expect(page).toHaveURL(/\/v1\/usuarios/);
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('table.tabela-usuarios-v1')).toBeVisible();
    });

    test('Cenário C: controle V2 (/v2/rma) -> Menu -> Usuários', async ({ page }) => {
        // 1. Login como superadministrador
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        // 2. Abrir rota V2
        await page.goto('/v2/rma');
        const menuDropdown = page.locator('li.dropdown > a.dropdown-toggle', { hasText: 'Menu' });
        await expect(menuDropdown).toBeVisible();
        await menuDropdown.click();

        const linkUsuariosV2 = page.locator('ul.dropdown-menu a', { hasText: 'Usuários' });
        await expect(linkUsuariosV2).toBeVisible();

        // 3. Clicar Usuários e validar
        const responsePromise = page.waitForResponse(res => res.url().includes('/v2/usuarios') && res.status() === 200);
        await linkUsuariosV2.click();
        const response = await responsePromise;

        expect(response.status()).toBe(200);
        await expect(page).toHaveURL(/\/v2\/usuarios/);
        await expect(page.locator('table.tabela-usuarios-v2')).toBeVisible();
    });
});
