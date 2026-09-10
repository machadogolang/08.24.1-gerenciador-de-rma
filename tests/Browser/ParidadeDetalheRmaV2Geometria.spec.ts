import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * PAR15-RMA-DET-011..014 - geometria do detalhe RMA do TEMA V2 contra o Legacy
 * `15.8.1/page/rma.php`, em dois viewports:
 *
 * - `selectacaoup` = `formSelect3` (130x25);
 * - botao OK do topo = `buttonSalvar` (50x25);
 * - relacao vertical entre o bloco de acao e a primeira `.formgroupnf`;
 * - conjunto de opcoes (o Legacy NAO tem ARQUIVAR nesses selects).
 */
const LEGACY_ROOT = process.env.LEGACY_ROOT ?? 'http://localhost:8094';
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORTS = [
    { width: 1440, height: 900 },
    { width: 1366, height: 768 },
];

test.setTimeout(180_000);

async function loginLegacy(browser: Browser, viewport: { width: number; height: number }): Promise<Page> {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    await page.goto(`${LEGACY_ROOT}/15.8.1/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    return page;
}

async function loginV3(browser: Browser, viewport: { width: number; height: number }): Promise<Page> {
    const context = await browser.newContext({ viewport });
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

async function hrefOuNulo(page: Page, seletor: string, timeout = 6000): Promise<string | null> {
    try {
        return await page.locator(seletor).first().getAttribute('href', { timeout });
    } catch {
        return null;
    }
}

for (const viewport of VIEWPORTS) {
    test(`PAR15-RMA-DET-011..014 - geometria do detalhe V2 em ${viewport.width}x${viewport.height}`, async ({ browser }) => {
        const legacy = await loginLegacy(browser, viewport);
        await legacy.goto(`${LEGACY_ROOT}/15.8.1/entrada`, { waitUntil: 'domcontentloaded' });
        const hrefLegacy = await hrefOuNulo(legacy, 'a[href*="info/"]');
        const idLegacy = hrefLegacy ? hrefLegacy.match(/info\/(\d+)/)?.[1] ?? null : null;
        test.skip(idLegacy === null, 'Legacy 15.8.1 sem RMA para amostrar.');
        await legacy.goto(`${LEGACY_ROOT}/15.8.1/info/${idLegacy}`, { waitUntil: 'domcontentloaded' });

        const v3 = await loginV3(browser, viewport);
        await v3.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
        const hrefV3 = await hrefOuNulo(v3, 'a[href*="/v2/rma/"]');
        const idV3 = hrefV3 ? hrefV3.match(/\/v2\/rma\/(\d+)/)?.[1] ?? null : null;
        test.skip(idV3 === null, 'Sem RMA para amostrar no novo.');
        await v3.goto(`${V3}/v2/rma/${idV3}`, { waitUntil: 'domcontentloaded' });

        const seletorSelect = 'select[name=selectacaoup]';
        const seletorOk = 'button[name=okup]';

        await expect(legacy.locator(seletorSelect)).toHaveCount(1);
        await expect(v3.locator(seletorSelect)).toHaveCount(1);

        const selectLegacy = await legacy.locator(seletorSelect).boundingBox();
        const selectV3 = await v3.locator(seletorSelect).boundingBox();
        const okLegacy = await legacy.locator(seletorOk).boundingBox();
        const okV3 = await v3.locator(seletorOk).boundingBox();
        const grupoLegacy = await legacy.locator('.formgroupnf').first().boundingBox();
        const grupoV3 = await v3.locator('.formgroupnf').first().boundingBox();

        console.log(`[${viewport.width}] select legacy=`, selectLegacy, ' novo=', selectV3);
        console.log(`[${viewport.width}] ok legacy=`, okLegacy, ' novo=', okV3);
        console.log(`[${viewport.width}] grupo legacy=`, grupoLegacy, ' novo=', grupoV3);

        expect(selectLegacy?.width).toBeCloseTo(130, 0);
        expect(selectV3?.width).toBeCloseTo(130, 0);
        expect(selectLegacy?.height).toBeCloseTo(25, 0);
        expect(selectV3?.height).toBeCloseTo(25, 0);

        expect(okLegacy?.width).toBeCloseTo(50, 0);
        expect(okV3?.width).toBeCloseTo(50, 0);
        expect(okLegacy?.height).toBeCloseTo(25, 0);
        expect(okV3?.height).toBeCloseTo(25, 0);

        // Relacao vertical entre o bloco de acao e a primeira linha do formulario.
        if (okLegacy && grupoLegacy && okV3 && grupoV3) {
            const gapLegacy = Math.round(grupoLegacy.y - (okLegacy.y + okLegacy.height));
            const gapV3 = Math.round(grupoV3.y - (okV3.y + okV3.height));
            console.log(`[${viewport.width}] gap legacy=${gapLegacy} novo=${gapV3}`);
            expect(Math.abs(gapLegacy - gapV3)).toBeLessThanOrEqual(12);
        }

        // Opcoes: sem ARQUIVAR em nenhum dos lados (o Legacy nao tinha).
        const opcoesLegacy = (await legacy.locator(`${seletorSelect} option`).allInnerTexts()).map(t => t.trim());
        const opcoesV3 = (await v3.locator(`${seletorSelect} option`).allInnerTexts()).map(t => t.trim());
        console.log('opcoes legacy=', opcoesLegacy, ' novo=', opcoesV3);
        expect(opcoesLegacy).not.toContain('ARQUIVAR');
        expect(opcoesV3).not.toContain('ARQUIVAR');
        expect(opcoesLegacy[0]).toBe('SALVAR');
        expect(opcoesV3[0]).toBe('SALVAR');
    });
}
