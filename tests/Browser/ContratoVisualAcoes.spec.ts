import { test, expect, type Browser, type Page } from '@playwright/test';

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(120_000);

async function loginV3(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test.describe('FRONT-003/UI-02D — contrato visual de ações (browser)', () => {

    test('Parceiros V1 e V2: Novo/Editar/Remover reconhecíveis, cursor, hover, TAB e semântica', async ({ browser }) => {
        for (const tema of ['v1', 'v2'] as const) {
            const page = await loginV3(browser);
            await page.goto(`${V3}/${tema}/parceiros/fornecedores`, { waitUntil: 'load' });

            const novo = page.locator('a.acao--primaria:has-text("Novo")').first();
            const ver = page.locator('a.acao--secundaria.acao--compacta:has-text("Ver")').first();
            const editar = page.locator('a.acao--secundaria.acao--compacta:has-text("Editar")').first();
            const remover = page.locator('button.acao--perigo.acao--compacta:has-text("Remover")').first();

            await expect(novo).toBeVisible();
            await expect(editar).toBeVisible();
            await expect(remover).toBeVisible();

            // Visualmente ação: alvo com geometria mínima e fundo de ação (não link cru).
            const novoBox = await novo.boundingBox();
            expect(novoBox?.height).toBeGreaterThanOrEqual(24);
            expect(await novo.evaluate(el => getComputedStyle(el).display)).toBe('inline-block');
            const novoFundo = await novo.evaluate(el => getComputedStyle(el).backgroundColor);
            expect(novoFundo).not.toBe('rgba(0, 0, 0, 0)');

            // Cursor real (link e botão).
            await expect(novo).toHaveCSS('cursor', 'pointer');
            await expect(editar).toHaveCSS('cursor', 'pointer');
            await expect(remover).toHaveCSS('cursor', 'pointer');
            // Paleta de perigo congelada: base escura #904141 nos dois temas (V1
            // recalibrado; hover claro #CD5C5C só no estado).
            await expect(remover).toHaveCSS('background-color', 'rgb(144, 65, 65)');

            // Hover altera estado visual (não só cor).
            const antesHover = await remover.evaluate(el => getComputedStyle(el).backgroundColor);
            await remover.hover();
            await page.waitForTimeout(80);
            const depoisHover = await remover.evaluate(el => getComputedStyle(el).backgroundColor);
            expect(depoisHover).not.toBe(antesHover);

            // TAB percorre Ver → Editar → Remover e o focus-visible tem outline.
            await novo.focus();
            await page.keyboard.press('Tab');
            await expect(ver).toBeFocused();
            await page.keyboard.press('Tab');
            await expect(editar).toBeFocused();
            await page.keyboard.press('Tab');
            await expect(remover).toBeFocused();
            await expect(remover).toHaveCSS('outline-style', 'solid');

            // Semântica: Novo/Editar continuam <a href> (GET); Remover é <button
            // type=submit> dentro de POST/DELETE com CSRF — ENTER/SPACE disparam a
            // mutação do navegador, sem JS novo.
            expect(await novo.evaluate(el => el.tagName)).toBe('A');
            expect(await editar.evaluate(el => el.tagName)).toBe('A');
            expect(await remover.evaluate(el => el.tagName)).toBe('BUTTON');
            const forma = await remover.evaluate(el => {
                const form = el.closest('form');
                return form
                    ? {
                          method: form.method,
                          action: form.getAttribute('action'),
                          deleteViaMethod: !!form.querySelector('input[name="_method"][value="DELETE"]'),
                          token: !!form.querySelector('input[name="_token"]'),
                      }
                    : null;
            });
            expect(forma).toEqual({
                method: 'post',
                action: expect.stringContaining(`/${tema}/parceiros/fornecedores/`),
                deleteViaMethod: true,
                token: true,
            });

            await page.context().close();
        }
    });

    test('Detalhe RMA e crédito expõem ações com contrato nos dois temas', async ({ browser }) => {
        for (const tema of ['v1', 'v2'] as const) {
            const page = await loginV3(browser);

            const origemListagem = tema === 'v1'
                ? `${V3}/rmas-entrada`
                : `${V3}/v2/rma#entrada`;
            await page.goto(origemListagem, { waitUntil: 'load' });
            const linkDetalhe = page.locator('table a[href*="/rmas/"]').first();
            await expect(linkDetalhe).toBeVisible();
            const urlDetalhe = await linkDetalhe.getAttribute('href');
            expect(urlDetalhe).not.toBeNull();
            const segmentos = new URL(urlDetalhe!, V3).pathname.split('/').filter(Boolean);
            const idDetalhe = segmentos[segmentos.length - 1];
            expect(idDetalhe).toMatch(/^\d+$/);
            await page.goto(`${V3}/${tema}/rma/${idDetalhe}`, { waitUntil: 'load' });

            const editar = page.locator('a.acao--primaria:has-text("Editar")').first();
            await expect(editar).toBeVisible();
            await expect(editar).toHaveCSS('cursor', 'pointer');
            await expect(page.locator('.acoes-de-transicao')).toBeVisible();
            const salvarSolucao = page.locator('.acoes-de-transicao button.acao--primaria:has-text("Salvar solução")');
            await expect(salvarSolucao).toHaveCSS('cursor', 'pointer');

            await page.goto(`${V3}/rmas-credito`, { waitUntil: 'load' });
            const marcarCredito = page.locator('button.acao--primaria:has-text("Marcar crédito disponível")');
            await expect(marcarCredito).toBeVisible();
            await expect(marcarCredito).toHaveCSS('cursor', 'pointer');
            // /rmas-credito segue o tema_preferido do usuário, não o prefixo
            // de QA; detectamos o shell renderizado e validamos a primária do tema.
            const emShellV2 = await page.locator('.header-v2').count() > 0;
            const corPrimariaEsperada = emShellV2 ? 'rgb(34, 74, 93)' : 'rgb(102, 45, 55)';
            await expect(marcarCredito).toHaveCSS('background-color', corPrimariaEsperada);

            await page.context().close();
        }
    });
});
