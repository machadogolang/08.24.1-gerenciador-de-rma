import { test, expect, type Page } from '@playwright/test';

/**
 * PAR-V2-NAV-02/PAR-V2-DROPDOWN-02/PAR-V2-CURSOR-02 - geometria e cursor da
 * navbar/dropdown V2 comparados ao contrato 15.8.1.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

test.setTimeout(180_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('PAR-V2-NAV-02 - textos da navbar centralizados como no Legacy (1440px)', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });

    const centros = await page.locator('.nav-tabs.nav-v2 > li').evaluateAll((lis) =>
        lis.map((li) => {
            const alvo = li.querySelector<HTMLElement>('a, button.link-como-item-dropdown');
            const item = li.getBoundingClientRect();
            if (! alvo) return null;
            const range = document.createRange();
            range.selectNodeContents(alvo);
            const texto = range.getBoundingClientRect();
            return {
                texto: (alvo.textContent || '').trim(),
                itemCentroY: (item.top + item.bottom) / 2,
                textoCentroY: (texto.top + texto.bottom) / 2,
                itemTop: item.top,
                itemBottom: item.bottom,
            };
        }),
    );

    const comTexto = centros.filter((item): item is NonNullable<typeof item> => item !== null);
    expect(comTexto.length).toBeGreaterThanOrEqual(9);
    for (const item of comTexto) {
        expect(Math.abs(item.textoCentroY - item.itemCentroY)).toBeLessThanOrEqual(2);
        expect(item.texto).not.toBe('');
    }

    // Logout (botao POST) e Menu (ancora) nao podem ficar desalinhados entre si.
    const menu = comTexto.find((item) => item.texto === 'Menu');
    const logout = comTexto.find((item) => item.texto === 'Logout');
    expect(menu).toBeDefined();
    expect(logout).toBeDefined();
    expect(Math.abs(menu!.textoCentroY - logout!.textoCentroY)).toBeLessThanOrEqual(2);
});

test('PAR-V2-NAV-02 - larguras de nav/itens por faixa seguem o media.php', async ({ page }) => {
    await login(page);
    const larguras = [568, 768, 800, 992, 1080, 1280, 1366, 1440, 1600, 2048];

    for (const largura of larguras) {
        await page.setViewportSize({ width: largura, height: 900 });
        await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
        const medido = await page.evaluate(() => {
            const nav = document.querySelector('.nav-tabs.nav-v2')?.getBoundingClientRect();
            const li = document.querySelector<HTMLElement>('.nav-tabs.nav-v2 > li');
            const r = li?.getBoundingClientRect();
            return {
                navLargura: nav ? nav.width : 0,
                navX: nav ? nav.x : 0,
                liLargura: r ? r.width : 0,
            };
        });

        const esperadoNav = largura >= 1280 ? 1190 : largura >= 992 ? 990 : 1190;
        const esperadoItem = largura >= 1280 ? (1190 * 11.1) / 100 : (esperadoNav * 12.5) / 100;
        expect(Math.abs(medido.navLargura - esperadoNav)).toBeLessThanOrEqual(2);
        expect(Math.abs(medido.liLargura - esperadoItem)).toBeLessThanOrEqual(2);
    }
});

test('PAR-V2-DROPDOWN-02 - sem moldura/faixa branca e geometria unica de 25px', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    await page.locator('.nav-v2 .dropdown-toggle').click();

    const menu = await page.locator('.nav-v2 .dropdown-menu').evaluate((el) => {
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return {
            x: r.x, largura: r.width, altura: r.height,
            padding: s.padding, border: s.borderWidth, fundo: s.backgroundColor,
        };
    });
    expect(menu.padding).toBe('0px');
    expect(menu.border).toBe('0px');

    const itens = await page.locator('.nav-v2 .dropdown-menu > li').evaluateAll((lis) =>
        lis.map((li) => {
            const r = li.getBoundingClientRect();
            const alvo = li.querySelector<HTMLElement>('a, button.link-como-item-dropdown');
            return {
                x: r.x, largura: r.width, y: r.y, altura: r.height,
                fundo: getComputedStyle(li).backgroundColor,
                alvoY: alvo?.getBoundingClientRect().y ?? null,
                alvoAltura: alvo?.getBoundingClientRect().height ?? null,
            };
        }),
    );

    expect(itens.length).toBeGreaterThanOrEqual(9);
    for (const item of itens) {
        expect(Math.abs(item.x - menu.x)).toBeLessThanOrEqual(1);
        expect(Math.abs(item.largura - menu.largura)).toBeLessThanOrEqual(1);
        expect(Math.abs(item.altura - 25)).toBeLessThanOrEqual(1);
        expect(item.fundo).not.toBe('rgb(255, 255, 255)');
        expect(Math.abs(item.alvoY! - item.y)).toBeLessThanOrEqual(1);
        expect(Math.abs(item.alvoAltura! - 25)).toBeLessThanOrEqual(1);
    }

    // Item POST "Trocar p/..." com a mesma geometria dos links comuns.
    const primeiroLink = page.locator('.nav-v2 .dropdown-menu > li > a').first();
    const botaoTroca = page.locator('.nav-v2 .dropdown-menu > li > form button.link-como-item-dropdown');
    const caixaLink = await primeiroLink.boundingBox();
    const caixaBotao = await botaoTroca.boundingBox();
    expect(caixaLink).not.toBeNull();
    expect(caixaBotao).not.toBeNull();
    expect(Math.abs(caixaLink!.height - caixaBotao!.height)).toBeLessThanOrEqual(1);
    expect(Math.abs(caixaLink!.width - caixaBotao!.width)).toBeLessThanOrEqual(1);
});

test('PAR-V2-CURSOR-02 - navbar/dropdown clicaveis com pointer; inputs seguem text', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await login(page);
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(200);

    const resultado = await page.evaluate(() => {
        const visivel = (el: Element) => {
            const s = getComputedStyle(el);
            const r = el.getBoundingClientRect();
            return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 0 && r.height > 0;
        };
        const links = Array.from(document.querySelectorAll<HTMLAnchorElement>('.nav-v2 a[href]')).filter(visivel);
        const botoes = Array.from(document.querySelectorAll<HTMLButtonElement>('.nav-v2 button')).filter(visivel);
        return {
            linksComPointer: links.filter((el) => getComputedStyle(el).cursor === 'pointer').length,
            linksTotal: links.length,
            botoesComPointer: botoes.filter((el) => getComputedStyle(el).cursor === 'pointer').length,
            botoesTotal: botoes.length,
        };
    });
    expect(resultado.linksTotal).toBeGreaterThan(0);
    expect(resultado.linksComPointer).toBe(resultado.linksTotal);
    expect(resultado.botoesTotal).toBeGreaterThan(0);
    expect(resultado.botoesComPointer).toBe(resultado.botoesTotal);

    const input = page.locator('#pesquisa, input[type="text"]').first();
    if (await input.count() > 0) {
        await expect(input).toHaveCSS('cursor', 'text');
    }
});
