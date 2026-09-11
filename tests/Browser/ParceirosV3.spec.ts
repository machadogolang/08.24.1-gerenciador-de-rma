import { test, expect, type Page } from '@playwright/test';

/**
 * T3-13 - Parceiros no Tema V3:
 * Listagem com abas de navegacao, busca/filtro, criacao e edicao em secoes,
 * detalhe operacional e adaptacao para viewport mobile.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

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

test('Parceiros V3 - Navegacao entre abas, criacao, edicao, detalhe e mobile', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);

    // 1. Acesso via rota /v3/parceiros redireciona para clientes
    await page.goto(`${V3}/v3/parceiros`, { waitUntil: 'load' });
    await expect(page).toHaveURL(/\/v3\/parceiros\/clientes$/);
    await expect(page.locator('h1')).toContainText('Parceiros - Clientes');

    // 2. Navegacao pelas abas
    await page.locator('.segmentos a:has-text("Fornecedores")').click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/fornecedores$/);
    await expect(page.locator('h1')).toContainText('Parceiros - Fornecedores');

    await page.locator('.segmentos a:has-text("Fabricantes")').click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/fabricantes$/);
    await expect(page.locator('h1')).toContainText('Parceiros - Fabricantes');

    await page.locator('.segmentos a:has-text("Assistências técnicas")').click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/assistencias-tecnicas$/);
    await expect(page.locator('h1')).toContainText('Parceiros - Assistências técnicas');

    // 3. Voltar para Clientes e criar novo parceiro
    await page.locator('.segmentos a:has-text("Clientes")').click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/clientes$/);

    await page.locator('a:has-text("Novo cliente")').click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/clientes\/create$/);
    await expect(page.locator('h1')).toHaveText('Novo cliente');

    const timestamp = Date.now();
    const nomeCliente = `Cliente V3 QA ${timestamp}`;
    await page.fill('input[name="nome"]', nomeCliente);
    await page.fill('input[name="representante"]', 'Maria Representante');
    await page.fill('input[name="telefone"]', '11999887766');
    await page.fill('input[name="email"]', `qa-${timestamp}@cliente.com`);
    await page.fill('input[name="cidade"]', 'Santos');

    await Promise.all([
        page.waitForURL(/\/v3\/parceiros\/clientes$/),
        page.click('.form-v3 button[type="submit"]'),
    ]);

    // 4. Verificar que o novo cliente aparece na listagem
    await expect(page.locator('table.tabela-v3')).toContainText(nomeCliente);

    // 5. Testar o filtro rapido client-side
    const campoFiltro = page.locator('[data-v3-filtro-tabela]');
    await campoFiltro.fill(nomeCliente);
    await expect(page.locator(`tr:has-text("${nomeCliente}")`)).toBeVisible();

    // 6. Acessar detalhe do parceiro
    await page.locator(`tr:has-text("${nomeCliente}") a:has-text("Ver")`).click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/clientes\/\d+$/);
    await expect(page.locator('.detalhe-v3__numero')).toHaveText(nomeCliente);
    await expect(page.locator('.detalhe-v3__secoes')).toContainText('Maria Representante');
    await expect(page.locator('.detalhe-v3__secoes')).toContainText('Santos');

    // 7. Editar parceiro
    await page.locator('a:has-text("Editar cliente")').click();
    await expect(page).toHaveURL(/\/v3\/parceiros\/clientes\/\d+\/edit$/);
    await expect(page.locator('h1')).toContainText('Editar cliente:');

    const nomeAtualizado = `${nomeCliente} Atualizado`;
    await page.fill('input[name="nome"]', nomeAtualizado);
    await Promise.all([
        page.waitForURL(/\/v3\/parceiros\/clientes$/),
        page.click('.form-v3 button[type="submit"]'),
    ]);

    await expect(page.locator('table.tabela-v3')).toContainText(nomeAtualizado);

    // 8. Testar viewport mobile (375x667)
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('table.tabela-v3')).toBeHidden();
    await expect(page.locator('.cartoes-parceiro')).toBeVisible();
    await expect(page.locator('.cartoes-parceiro')).toContainText(nomeAtualizado);
});
