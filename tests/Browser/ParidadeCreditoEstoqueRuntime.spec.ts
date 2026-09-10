import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * PF-14 / prioridade 3 da matriz forense - medicao em runtime dos controles de
 * CREDITO e ESTOQUE do detalhe RMA, Legacy x novo, por tema:
 *
 * - V1 (`14.6.1/page/detalhes.php`): dois CHECKBOXES reais no rodape
 *   (`marcarestoque`/`creditodisponivel`) com rotulos em `data-text-*`.
 * - V2 (`15.8.1/page/rma.php`): dois SELECTS Nao/Sim na 3a coluna
 *   (`marcarestoque`/`creditodisponivel`).
 *
 * Compara tipo de controle, rotulos/opcoes e geometria (boundingBox). Cada tema
 * preserva o proprio Legacy; nao se "corrige" V2 transformando em checkbox.
 */
const LEGACY_ROOT = process.env.LEGACY_ROOT ?? 'http://localhost:8094';
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(180_000);

async function loginLegacy(browser: Browser, app: '14.6.1' | '15.8.1'): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    // 14.6.1 nao tem rewrite `/login` (o .htaccess do legado V1 e vazio): o gateway
    // dele e o proprio `index.php`. O 15.8.1 tem `login.php` via rewrite.
    const urlLogin = app === '14.6.1' ? `${LEGACY_ROOT}/${app}/` : `${LEGACY_ROOT}/${app}/login`;
    await page.goto(urlLogin, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    return page;
}

async function loginV3(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

function primeiroId(href: string | null, padrao: RegExp): string | null {
    if (!href) {
        return null;
    }
    const match = href.match(padrao);
    return match ? match[1] : null;
}

/** Le um href sem ficar preso quando o elemento nao existe. */
async function hrefOuNulo(page: Page, seletor: string, timeout = 6000): Promise<string | null> {
    try {
        return await page.locator(seletor).first().getAttribute('href', { timeout });
    } catch {
        return null;
    }
}

async function opcoesSelect(page: Page, seletor: string): Promise<string[]> {
    return page.locator(`${seletor} option`).allInnerTexts();
}

test('V1 - estoque/credito sao checkboxes reais com os rotulos do 14.6.1', async ({ browser }) => {
    const legacy = await loginLegacy(browser, '14.6.1');
    await legacy.goto(`${LEGACY_ROOT}/14.6.1/index.php?page=entrada`, { waitUntil: 'domcontentloaded' });
    const idLegacy = primeiroId(await hrefOuNulo(legacy, 'a[href*="page=detalhes"]'), /id=(\d+)/);
    test.skip(idLegacy === null, 'Legacy 14.6.1 sem RMA de entrada para amostrar.');
    await legacy.goto(`${LEGACY_ROOT}/14.6.1/index.php?page=detalhes&id=${idLegacy}`, { waitUntil: 'domcontentloaded' });

    const v3 = await loginV3(browser);
    // Amostra do detalhe V1 a partir de um id real listado no painel V2 (mesma
    // controller; o prefixo `/v1` forca o tema e evita depender da base de QA ter
    // RMA de Entrada).
    await v3.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    const idV3 = primeiroId(await hrefOuNulo(v3, 'a[href*="/v2/rma/"]'), /\/v2\/rma\/(\d+)/);
    test.skip(idV3 === null, 'Sem RMA para amostrar no novo.');
    await v3.goto(`${V3}/v1/rma/${idV3}`, { waitUntil: 'domcontentloaded' });

    // Estoque e credito: checkbox real nos dois temas V1.
    // O nome `marcarestoque` tambem aparece no painel global Novo (presente em toda
    // pagina do V1); escopa o form do detalhe.
    const formLegacy = legacy.locator('form[action*="processa_detalhes"]');
    const formV3 = v3.locator('form.detalhe-bd-form');
    await expect(formLegacy.locator('input[type=checkbox][name=marcarestoque]')).toHaveCount(1);
    await expect(formV3.locator('input[type=checkbox][name=marcarestoque]')).toHaveCount(1);
    await expect(formLegacy.locator('input[type=checkbox][name=creditodisponivel]')).toHaveCount(1);
    await expect(formV3.locator('input[type=checkbox][name=credito_disponivel]')).toHaveCount(1);

    // Rotulos historicos do estoque e do credito.
    await expect(formLegacy.locator('label[for=checkbox1]')).toHaveAttribute('data-text-true', 'O ITEM E DO ESTOQUE');
    await expect(formLegacy.locator('label[for=checkbox1]')).toHaveAttribute('data-text-false', 'ITEM NAO E DO ESTOQUE');
    await expect(formV3.locator('label[data-text-true="O ITEM E DO ESTOQUE"][data-text-false="ITEM NAO E DO ESTOQUE"]')).toHaveCount(1);
    await expect(formLegacy.locator('label[for=checkbox2]')).toHaveAttribute('data-text-true', 'CREDITO DISPONIVEL');
    await expect(formLegacy.locator('label[for=checkbox2]')).toHaveAttribute('data-text-false', 'MARQUE P/ VALIDAR CREDITO');
    await expect(formV3.locator('label[data-text-true="CREDITO DISPONIVEL"][data-text-false="MARQUE P/ VALIDAR CREDITO"]')).toHaveCount(1);

    // Interacao: o clique alterna o estado do controle real nos dois lados.
    const checkLegacy = formLegacy.locator('input[type=checkbox][name=marcarestoque]');
    const antesLegacy = await checkLegacy.isChecked();
    // O input do Legacy e `display:none` (check custom via label); o clique real do
    // usuario e no label, mas aqui basta provar que o controle responde.
    await checkLegacy.evaluate((el: HTMLInputElement) => el.click());
    expect(await checkLegacy.isChecked()).toBe(!antesLegacy);

    const checkV3 = formV3.locator('input[type=checkbox][name=marcarestoque]');
    const antesV3 = await checkV3.isChecked();
    // Novo V1 agora replica o check custom do Legacy (input oculto + label).
    await checkV3.evaluate((el: HTMLInputElement) => el.click());
    expect(await checkV3.isChecked()).toBe(!antesV3);

    // Geometria do controle VISIVEL (o check custom e o label).
    const labelLegacy = await formLegacy.locator('label[data-text-true]').first().boundingBox();
    const labelV3 = await formV3.locator('label[data-text-true]').first().boundingBox();
    console.log('V1 label toggle legacy=', labelLegacy, ' novo=', labelV3);
    expect(labelLegacy && labelV3 ? Math.abs(labelLegacy.width - labelV3.width) : 99).toBeLessThanOrEqual(2);
    expect(labelLegacy && labelV3 ? Math.abs(labelLegacy.height - labelV3.height) : 99).toBeLessThanOrEqual(2);

    const caixaLegacy = await formLegacy.locator('input[type=checkbox][name=marcarestoque]').boundingBox();
    const caixaV3 = await formV3.locator('input[type=checkbox][name=marcarestoque]').boundingBox();
    console.log('V1 estoque boundingBox legacy=', caixaLegacy, ' novo=', caixaV3);
});

test('V2 - estoque/credito sao selects Nao/Sim com os rotulos do 15.8.1', async ({ browser }) => {
    const legacy = await loginLegacy(browser, '15.8.1');
    await legacy.goto(`${LEGACY_ROOT}/15.8.1/entrada`, { waitUntil: 'domcontentloaded' });
    const idLegacy = primeiroId(await hrefOuNulo(legacy, 'a[href*="info/"]'), /info\/(\d+)/);
    test.skip(idLegacy === null, 'Legacy 15.8.1 sem RMA de entrada para amostrar.');
    await legacy.goto(`${LEGACY_ROOT}/15.8.1/info/${idLegacy}`, { waitUntil: 'domcontentloaded' });

    const v3 = await loginV3(browser);
    await v3.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    const idV3 = primeiroId(await hrefOuNulo(v3, 'a[href*="/v2/rma/"]'), /\/v2\/rma\/(\d+)/);
    test.skip(idV3 === null, 'Tema V2 novo sem RMA para amostrar.');
    await v3.goto(`${V3}/v2/rma/${idV3}`, { waitUntil: 'domcontentloaded' });

    await expect(legacy.locator('select[name=marcarestoque]')).toHaveCount(1);
    await expect(v3.locator('select[name=marcarestoque]')).toHaveCount(1);
    expect((await opcoesSelect(legacy, 'select[name=marcarestoque]')).map(t => t.trim())).toEqual(['Nao', 'Sim']);
    expect((await opcoesSelect(v3, 'select[name=marcarestoque]')).map(t => t.trim())).toEqual(['Nao', 'Sim']);

    await expect(legacy.locator('select[name=creditodisponivel]')).toHaveCount(1);
    await expect(v3.locator('select[name=credito_disponivel]')).toHaveCount(1);
    expect((await opcoesSelect(legacy, 'select[name=creditodisponivel]')).map(t => t.trim())).toEqual(['Nao', 'Sim']);
    expect((await opcoesSelect(v3, 'select[name=credito_disponivel]')).map(t => t.trim())).toEqual(['Nao', 'Sim']);

    await expect(legacy.locator('label:has-text("E um produto do estoque")')).toHaveCount(1);
    await expect(v3.locator('label:has-text("E um produto do estoque")')).toHaveCount(1);
    await expect(legacy.locator('label:has-text("E credito disponivel")')).toHaveCount(1);
    await expect(v3.locator('label:has-text("E credito disponivel")')).toHaveCount(1);

    // Interacao: trocar a opcao do select muda o valor selecionado (Nao <-> Sim).
    const selectV3 = v3.locator('select[name=marcarestoque]');
    await selectV3.selectOption('1');
    expect(await selectV3.inputValue()).toBe('1');
    await selectV3.selectOption('0');
    expect(await selectV3.inputValue()).toBe('0');

    const caixaLegacy = await legacy.locator('select[name=marcarestoque]').boundingBox();
    const caixaV3 = await v3.locator('select[name=marcarestoque]').boundingBox();
    console.log('V2 estoque boundingBox legacy=', caixaLegacy, ' novo=', caixaV3);
    expect(caixaLegacy && caixaV3 ? Math.abs(caixaLegacy.height - caixaV3.height) : 99).toBeLessThanOrEqual(1);
});
