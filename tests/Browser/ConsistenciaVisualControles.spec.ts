import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * UI-AUD-001..018 / FRONT-003/UI-09 - contrato de consistência de controles e
 * formulários (V1 e V2). Assert de DOM/computedStyle/geometria, sem screenshot.
 * Roda contra o V3 local com `PLAYWRIGHT_BASE_URL=http://localhost:8095`.
 */

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
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

async function visivel(page: Page, texto: string): Promise<number> {
    return page.evaluate((textoEsperado) => {
        const normalizar = (v: string) => (v || '').replace(/\s+/g, ' ').trim().toLowerCase();
        return Array.from(document.querySelectorAll('h1,h2,h3')).filter((h) => {
            if (h.classList.contains('sr-only')) return false;
            const r = h.getBoundingClientRect();
            const s = getComputedStyle(h);
            const visivelEl = s.display !== 'none' && s.visibility !== 'hidden' && r.width > 0 && r.height > 0;
            return visivelEl && normalizar(h.textContent || '') === normalizar(textoEsperado);
        }).length;
    }, texto);
}

test.describe('UI-AUD - consistência de formulários e controles', () => {

    test('A - todo select habilitado nas rotas cobertas tem cursor pointer', async ({ browser }) => {
        const rotas = [
            '/rmas-relatorios/rpec',
            '/usuarios',
            '/v2/usuarios',
            '/v1/parceiros/fornecedores/create',
            '/v2/parceiros/fornecedores/create',
            '/v2/rma/create',
            // PAR-V2-DETAIL-02/NOVO-01 - detalhe/editaveis voltaram a expor selects
            // reais; a varredura de cursor cobre as superficies restauradas.
            '/v2/rma/3',
            '/v1/rma/3',
        ];
        for (const rota of rotas) {
            const page = await loginV3(browser);
            await page.goto(`${V3}${rota}`, { waitUntil: 'load' });
            const selects = page.locator('select:visible:enabled');
            const contagem = await selects.count();
            expect(contagem, `${rota} precisa expor selects habilitados`).toBeGreaterThan(0);
            for (let i = 0; i < contagem; i++) {
                await expect(selects.nth(i)).toHaveCSS('cursor', 'pointer');
            }
            await page.context().close();
        }
    });

    test('B - Parceiro V1: input, select UF e textareas principais compartilham largura útil', async ({ browser }) => {
        for (const tipo of ['fornecedores', 'fabricantes', 'clientes', 'assistencias-tecnicas']) {
            const page = await loginV3(browser);
            await page.goto(`${V3}/v1/parceiros/${tipo}/create`, { waitUntil: 'load' });
            const caixas = await page.evaluate(() => {
                const box = (el: Element) => {
                    const r = el.getBoundingClientRect();
                    return { x: +r.x.toFixed(1), w: +r.width.toFixed(1) };
                };
                const nome = document.querySelector<HTMLInputElement>('input[name="nome"]');
                const uf = document.querySelector<HTMLSelectElement>('select[name="uf"]');
                const obs = document.querySelector<HTMLTextAreaElement>('textarea[name="observacao"]');
                return {
                    nome: nome ? box(nome) : null,
                    uf: uf ? box(uf) : null,
                    obs: obs ? box(obs) : null,
                };
            });
            expect(caixas.nome).not.toBeNull();
            expect(caixas.uf).not.toBeNull();
            expect(caixas.obs).not.toBeNull();
            expect(Math.abs(caixas.nome!.w - caixas.uf!.w)).toBeLessThanOrEqual(2);
            expect(Math.abs(caixas.nome!.w - caixas.obs!.w)).toBeLessThanOrEqual(2);
            expect(Math.abs(caixas.uf!.x - caixas.nome!.x)).toBeLessThanOrEqual(2);
            await page.context().close();
        }
    });

    test('B2 - Parceiro V2: input, select e textarea com a mesma largura útil', async ({ browser }) => {
        const page = await loginV3(browser);
        await page.goto(`${V3}/v2/parceiros/fornecedores/create`, { waitUntil: 'load' });
        const caixas = await page.evaluate(() => {
            const w = (el: Element | null) => (el ? el.getBoundingClientRect().width : null);
            return {
                nome: w(document.querySelector<HTMLInputElement>('input[name="nome"]')),
                uf: w(document.querySelector<HTMLSelectElement>('select[name="uf"]')),
                obs: w(document.querySelector<HTMLTextAreaElement>('textarea[name="observacao"]')),
            };
        });
        expect(caixas.nome).not.toBeNull();
        expect(Math.abs(caixas.nome! - caixas.uf!)).toBeLessThanOrEqual(2);
        expect(Math.abs(caixas.nome! - caixas.obs!)).toBeLessThanOrEqual(2);
        await page.context().close();
    });

    test('C - RCD/RPEC/RMPE V1: um único título visível por relatório', async ({ browser }) => {
        const rotas = [
            '/rmas-relatorios/rcd',
            '/rmas-relatorios/rpec',
            '/rmas-relatorios/rmpe?data_inicio=2026-01-01&data_fim=2026-12-31',
        ];
        const titulos = [
            'Relatório de Créditos Disponíveis (RCD)',
            'Relatório de Produtos em Estoque para Contagem (RPEC)',
            'Relatório de Produtos Encaminhados (RMPE)',
        ];
        for (let i = 0; i < rotas.length; i++) {
            const page = await loginV3(browser);
            await page.goto(`${V3}${rotas[i]}`, { waitUntil: 'load' });
            expect(await visivel(page, titulos[i])).toBe(1);
            await page.context().close();
        }
    });

    test('D - Usuários V1: controles não estouram a célula e botões não encolhem', async ({ browser }) => {
        const page = await loginV3(browser);
        await page.goto(`${V3}/usuarios`, { waitUntil: 'load' });
        const primeiraLinha = page.locator('.tabela-usuarios-v1 tbody tr').first();
        const caixas = await primeiraLinha.evaluate((tr) => {
            const box = (el: Element) => {
                const r = el.getBoundingClientRect();
                return { left: r.left, right: r.right };
            };
            const celula = tr.children[3];
            const inputs = Array.from(tr.querySelectorAll<HTMLInputElement>('input[type="password"]'));
            const botoes = Array.from(tr.querySelectorAll<HTMLButtonElement>('button'));
            return {
                celula: box(celula),
                inputs: inputs.map(box),
                largurasInputs: inputs.map((el) => el.getBoundingClientRect().width),
                largurasBotoes: botoes.map((el) => el.getBoundingClientRect().width),
            };
        });
        for (const input of caixas.inputs) {
            expect(input.right).toBeLessThanOrEqual(caixas.celula.right + 1);
            expect(input.left).toBeGreaterThanOrEqual(caixas.celula.left - 1);
        }
        for (const largura of caixas.largurasInputs) expect(largura).toBeGreaterThanOrEqual(108);
        for (const largura of caixas.largurasBotoes) expect(largura).toBeGreaterThanOrEqual(73);
        await page.context().close();
    });

    test('D2 - Usuários V2: ações na mesma linha e linha compacta em 1440px', async ({ browser }) => {
        const page = await loginV3(browser);
        await page.goto(`${V3}/v2/usuarios`, { waitUntil: 'load' });
        const primeiraLinha = page.locator('.tabela-usuarios-v2 tbody tr').first();
        const dados = await primeiraLinha.evaluate((tr) => {
            const formas = Array.from(tr.querySelectorAll('form')).map((f) => {
                const ys = Array.from(f.querySelectorAll<HTMLElement>('select,input,button'))
                    .filter((el) => getComputedStyle(el).display !== 'none')
                    .map((el) => el.getBoundingClientRect().y);
                return { filhos: ys.length, alturaLinha: Math.max(...ys) - Math.min(...ys) };
            });
            return {
                alturaLinha: tr.getBoundingClientRect().height,
                formas,
            };
        });
        expect(dados.alturaLinha).toBeLessThanOrEqual(60);
        for (const forma of dados.formas) {
            expect(forma.filhos).toBeGreaterThan(0);
            expect(forma.alturaLinha).toBeLessThanOrEqual(36);
        }
        await page.context().close();
    });

    test('E - Controle V1: sem scroll horizontal da página e inputs de representante alinhados', async ({ browser }) => {
        const page = await loginV3(browser);
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'load' });
        await page.evaluate(() => document.querySelectorAll('details').forEach((d) => d.setAttribute('open', '')));
        const overflow = await page.evaluate(() => document.body.scrollWidth > document.documentElement.clientWidth);
        expect(overflow).toBe(false);
        const primeiroDetails = page.locator('details').first();
        const xs = await primeiroDetails.locator('input[type="text"]').evaluateAll((els) => els.map((el) => el.getBoundingClientRect().x));
        expect(xs.length).toBeGreaterThanOrEqual(3);
        for (const x of xs.slice(1)) {
            expect(Math.abs(x - xs[0])).toBeLessThanOrEqual(1.5);
        }
        await page.context().close();
    });

    test('F - Dropdown Menu V2: itens com altura própria, sem sobreposição e navegáveis por TAB', async ({ browser }) => {
        const page = await loginV3(browser);
        await page.goto(`${V3}/v2/rma/create`, { waitUntil: 'load' });
        await page.locator('.nav-v2 .dropdown-toggle').click();
        const itens = await page.locator('.nav-v2 .dropdown-menu > li > a').evaluateAll((els) =>
            els.slice(0, 8).map((el) => {
                const r = el.getBoundingClientRect();
                return { y: r.y, bottom: r.bottom, h: r.height, visivel: getComputedStyle(el).display !== 'none' };
            }),
        );
        expect(itens.length).toBeGreaterThanOrEqual(8);
        for (const item of itens) {
            expect(item.visivel).toBe(true);
            expect(item.h).toBeLessThanOrEqual(26);
        }
        for (let i = 1; i < itens.length; i++) {
            expect(itens[i].y).toBeGreaterThanOrEqual(itens[i - 1].bottom - 1);
        }
        await page.locator('.nav-v2 .dropdown-toggle').focus();
        await page.keyboard.press('Tab');
        await expect(page.locator('.nav-v2 .dropdown-menu > li > a').first()).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(page.locator('.nav-v2 .dropdown-menu > li > a').nth(1)).toBeFocused();
        await page.context().close();
    });

    test('G - Foco por teclado alcança ações principais (parceiros e detalhe RMA)', async ({ browser }) => {
        for (const tema of ['v1', 'v2'] as const) {
            const page = await loginV3(browser);
            await page.goto(`${V3}/${tema}/parceiros/fornecedores`, { waitUntil: 'load' });
            const novo = page.locator('a.acao--primaria:has-text("Novo")').first();
            await novo.focus();
            await page.keyboard.press('Tab');
            await page.keyboard.press('Tab');
            const algumComOutline = await page.evaluate(() =>
                Array.from(document.querySelectorAll<HTMLElement>('a,button')).some((el) => getComputedStyle(el).outlineStyle === 'solid'),
            );
            expect(algumComOutline).toBe(true);
            await page.context().close();
        }
    });
});
