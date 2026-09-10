import { test, expect, type Page } from '@playwright/test';

/**
 * PAR-RES-E-04 - organizacao historica do TEMA V2: Anotacoes como pagina propria
 * (`15.8.1/page/anotacoes.php`) e Alterar senha como superficie separada
 * (`15.8.1/subp/senha.php`), fora do `/perfil`.
 *
 * Prova browser: navegacao, persistencia da anotacao, composicao do formulario de
 * senha (POST + CSRF + `_method=PUT` + validacao) e que o V1 continua intacto.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

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

test('PAR-RES-E-04 - Anotacoes V2 e pagina propria, salva por POST/CSRF e persiste', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    // Link do menu aponta para a pagina dedicada (nao mais para o perfil).
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    await page.locator('.nav-v2 .dropdown-toggle').click();
    const linkAnotacoes = page.locator('.nav-v2 .dropdown-menu > li > a:has-text("Anotacoes")');
    await expect(linkAnotacoes).toHaveAttribute('href', /\/v2\/anotacoes$/);

    await page.goto(`${V3}/v2/anotacoes`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.shell-v2 .box-subpage')).toHaveText(/QUADRO DE ANOTACOES/);

    const marcador = `QA PAR-RES-E-04 ${Date.now()}`;
    const campo = page.locator('#anotacao');
    await expect(campo).toBeVisible();

    const form = page.locator('form:has(#anotacao)');
    await expect(form).toHaveAttribute('method', /post/i);
    await expect(form.locator('input[name="_token"]')).toHaveCount(1);
    await expect(form.locator('input[name="_method"][value="PUT"]')).toHaveCount(1);

    await campo.fill(marcador);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        form.locator('button[type=submit]').click(),
    ]);

    await page.goto(`${V3}/v2/anotacoes`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#anotacao')).toHaveValue(marcador);
});

test('PAR-RES-E-04 - /perfil V2 nao mistura mais senha e anotacao (so atalhos)', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    await page.goto(`${V3}/v2/perfil`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('textarea[name="anotacao"]')).toHaveCount(0);
    await expect(page.locator('input[name="senha_atual"]')).toHaveCount(0);
    await expect(page.locator('button:has-text("Alternar tema")')).toBeVisible();
    await expect(page.locator('a:has-text("Quadro de Anotacoes")')).toHaveAttribute('href', /\/v2\/anotacoes$/);
    await expect(page.locator('a:has-text("Alterar senha")')).toHaveAttribute('href', /\/v2\/perfil\/senha$/);
});

test('PAR-RES-E-04 - Alterar senha V2 e superficie separada com POST/CSRF/validacao', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    await page.goto(`${V3}/v2/perfil/senha`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.shell-v2 .breadcrumb')).toContainText('Alterar senha');
    await expect(page.locator('#senha_atual')).toBeVisible();
    await expect(page.locator('#nova_senha')).toBeVisible();
    await expect(page.locator('#nova_senha_confirmation')).toBeVisible();

    const form = page.locator('form:has(#senha_atual)');
    await expect(form).toHaveAttribute('method', /post/i);
    await expect(form).toHaveAttribute('action', /\/perfil\/senha$/);
    await expect(form.locator('input[name="_token"]')).toHaveCount(1);
    await expect(form.locator('input[name="_method"][value="PUT"]')).toHaveCount(1);

    // Senha atual errada: o POST e aceito mas a validacao moderna nega e reexibe a
    // superficie separada - prova que o endpoint de troca esta ligado aqui sem
    // alterar a senha de QA.
    await page.fill('#senha_atual', 'senha-incorreta-qa');
    await page.fill('#nova_senha', 'nova-senha-qa-123');
    await page.fill('#nova_senha_confirmation', 'nova-senha-qa-123');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        form.locator('button[type=submit]').click(),
    ]);

    expect(page.url()).toContain('/v2/perfil/senha');
    await expect(page.locator('ul.text-danger li').first()).toBeVisible();
});

test('PAR-RES-E-04 - V1 continua com anotacao e senha no /perfil (sem regressao)', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    await page.goto(`${V3}/v1/perfil`, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('textarea[name="anotacao"]')).toHaveCount(1);
    await expect(page.locator('input[name="senha_atual"]')).toHaveCount(1);
});
