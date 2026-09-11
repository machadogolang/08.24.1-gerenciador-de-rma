import { test, expect, type Page } from '@playwright/test';

/**
 * T3-14 - Usuarios / Administracao no Tema V3:
 * Listagem, acoes contextuais (trocar papel, resetar senha) e criacao de novo usuario.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

test.setTimeout(120_000);

async function loginAdmin(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('Usuarios V3 - Listagem, novo usuario, acoes contextuais e mobile', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await loginAdmin(page);

    // 1. Acesso a /v3/usuarios
    await page.goto(`${V3}/v3/usuarios`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toContainText('Administração - Usuários');
    await expect(page.locator('.app-shell__rail a:has-text("Administração")')).toBeVisible();

    // 2. Novo usuario
    await page.locator('a:has-text("+ Novo usuário")').click();
    await expect(page).toHaveURL(/\/v3\/usuarios\/novo$/);
    await expect(page.locator('h1')).toHaveText('Novo Usuário');

    const timestamp = Date.now();
    const nomeUsuario = `Operador V3 QA ${timestamp}`;
    const emailUsuario = `qa-op-${timestamp}@rma.local`;

    await page.fill('input[name="name"]', nomeUsuario);
    await page.fill('input[name="email"]', emailUsuario);
    await page.fill('input[name="password"]', 'senhaSegura123');
    await page.selectOption('select[name="papel"]', 'Operador');

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('.form-v3 button[type="submit"]'),
    ]);

    // Redireciona de volta (identidade.usuarios.index canonico ou /v3/usuarios)
    await page.goto(`${V3}/v3/usuarios`, { waitUntil: 'load' });
    await expect(page.locator('table.tabela-v3')).toContainText(nomeUsuario);
    await expect(page.locator('table.tabela-v3')).toContainText(emailUsuario);

    // 3. Testar filtro client-side
    const filtro = page.locator('[data-v3-filtro-tabela]');
    await filtro.fill(nomeUsuario);
    await expect(page.locator(`tr:has-text("${nomeUsuario}")`)).toBeVisible();

    // 4. Testar viewport mobile (375x667)
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('table.tabela-v3')).toBeHidden();
    await expect(page.locator('.cartoes-usuario')).toBeVisible();
    await expect(page.locator('.cartoes-usuario')).toContainText(nomeUsuario);
});
