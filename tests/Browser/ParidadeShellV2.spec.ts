import { test, expect, type Page } from '@playwright/test';

/**
 * ONDA A - reconciliacao do shell do TEMA V2. Fonte Legacy:
 * `legacy-source/15.8.1/index.php` + `inc/rightmenu.php` + `inc/menu.php` +
 * `inc/footer.php`.
 *
 * Cobre o que faltava de prova na onda: sequencia de `LRTOP1`/`LRTOP2` do painel
 * lateral (PAR-RES-A-01), titulo do cabecalho preservado ao expandir
 * (PAR-RES-A-02), Logout POST e o rodape historico. Navbar/dropdown/troca de tema
 * ja tem prova propria em `ParidadeNavbarDropdownV2`/`ParidadeTrocaTemaPrefixada`.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

// Sequencia EXATA de `inc/rightmenu.php` (nao e alternancia simples: três LRTOP1
// seguidos no miolo DESTINATARIOS/PORTO A/URGENTE).
const SEQUENCIA_LRTOP = [
    'LRTOP1', 'LRTOP2', 'LRTOP1', 'LRTOP2', 'LRTOP1', 'LRTOP1', 'LRTOP1',
    'LRTOP2', 'LRTOP1', 'LRTOP2', 'LRTOP1', 'LRTOP2', 'LRTOP1', 'LRTOP2',
];

test.setTimeout(120_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
}

test('PAR-RES-A-01 - painel lateral V2 reproduz a sequencia LRTOP1/LRTOP2 do rightmenu.php', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    await expect(page.locator('.shell-v2__sidebar')).toBeVisible();
    const cabecalhos = page.locator('.shell-v2__sidebar > div[data-pmo-alvo]');
    await expect(cabecalhos).toHaveCount(14);

    const classes = await cabecalhos.evaluateAll((elementos) =>
        elementos.map((elemento) => elemento.className.trim()),
    );
    expect(classes).toEqual(SEQUENCIA_LRTOP);

    const fundos = await page.locator('.shell-v2__sidebar > div.LRTOP1, .shell-v2__sidebar > div.LRTOP2')
        .evaluateAll((elementos) => elementos.map((elemento) => getComputedStyle(elemento).backgroundColor));
    expect(fundos[0]).toBe('rgba(0, 0, 0, 0.5)');
    expect(fundos[1]).toBe('rgba(0, 0, 0, 0.4)');
});

test('PAR-RES-A-02 - cabecalho do painel lateral mantem o titulo ao expandir', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    const cabecalho = page.locator('.shell-v2__sidebar > div[data-pmo-alvo]').first();
    await expect(cabecalho).toContainText('DEU ENTRADA HOJE');

    await cabecalho.click();
    await expect(page.locator('#entrada_r')).toBeVisible();
    // O legado mantem o titulo; o gatilho NAO vira "Ocultar".
    await expect(cabecalho).toContainText('DEU ENTRADA HOJE');
    await expect(cabecalho).not.toHaveText('Ocultar');

    await cabecalho.click();
    await expect(page.locator('#entrada_r')).toBeHidden();
    await expect(cabecalho).toContainText('DEU ENTRADA HOJE');
});

test('ONDA A - shell V2 mantem navbar, dropdown Menu, Logout POST e rodape historico', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    await expect(page.locator('header.header-v2')).toBeVisible();
    await expect(page.locator('.nav-tabs.nav-v2 > li')).toHaveCount(9);

    await page.locator('.nav-v2 .dropdown-toggle').click();
    const itensMenu = page.locator('.nav-v2 .dropdown-menu > li');
    await expect(itensMenu.filter({ hasText: 'Anotacoes' })).toHaveCount(1);
    await expect(itensMenu.filter({ hasText: 'Clientes' })).toHaveCount(1);

    const logout = page.locator('.nav-v2 .logoutx form[method="post"] button');
    await expect(logout).toHaveText('Logout');
    await expect(page.locator('.nav-v2 .logoutx form input[name="_token"]')).toHaveCount(1);

    const rodape = page.locator('p.designedby');
    await expect(rodape).toHaveCount(2);
    await expect(rodape.nth(0)).toContainText('Designed by');
    await expect(rodape.nth(1)).toContainText('licenciada para');
});
