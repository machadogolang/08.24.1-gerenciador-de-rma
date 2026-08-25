import { test, expect, type Browser, type Page } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { join } from 'node:path';

const DESTINO = join(process.cwd(), 'docs/produto/screenshots-paridade-v1');
const LEGACY = process.env.LEGACY_BASE_URL ?? 'http://host.docker.internal:8094/14.6.1/';
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(180_000);

type Falha = { tipo: string; url: string; status?: number; erro?: string };

function vigiarRecursos(page: Page, falhas: Falha[]): void {
    page.on('response', response => {
        if (response.status() >= 400) {
            falhas.push({ tipo: 'http', url: response.url(), status: response.status() });
        }
    });
    page.on('requestfailed', request => {
        falhas.push({ tipo: 'requestfailed', url: request.url(), erro: request.failure()?.errorText });
    });
}

async function loginLegacy(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    await page.goto(LEGACY, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    return page;
}

async function loginV3(browser: Browser, falhas: Falha[] = []): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    vigiarRecursos(page, falhas);
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test.beforeAll(() => mkdirSync(DESTINO, { recursive: true }));

test('captura os gateways de login para registrar a decisão de paridade', async ({ browser }) => {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const legacy = await context.newPage();
    const v3 = await context.newPage();
    await legacy.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());

    await legacy.goto(LEGACY, { waitUntil: 'domcontentloaded' });
    await legacy.screenshot({ path: join(DESTINO, 'legacy-login-1440.png'), fullPage: true });
    await v3.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-login-1440.png'), fullPage: true });

    await context.close();
});

test('TEMA V1 carrega estrutura fixa, menu histórico e assets locais sem erro', async ({ browser }) => {
    const falhas: Falha[] = [];
    const page = await loginV3(browser, falhas);
    await page.goto(`${V3}/v1/usuarios`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);

    expect(await page.locator('#BASE').evaluate(el => getComputedStyle(el).width)).toBe('984px');
    expect(await page.locator('.menu-up').count()).toBe(3);
    expect(await page.locator('.menu-up').first().evaluate(el => getComputedStyle(el).float)).toBe('left');
    expect(await page.locator('.menuDivSession').evaluate(el => getComputedStyle(el).width)).toBe('982px');
    expect(await page.locator('.JS-SessaoLEFT').evaluate(el => getComputedStyle(el).width)).toBe('838px');
    expect(await page.locator('.JS-SessaoRIGHT').evaluate(el => getComputedStyle(el).width)).toBe('144px');
    expect((await page.evaluate(() => document.fonts.load('400 12px "Fira Mono"'))).length).toBeGreaterThan(0);

    const essenciais = falhas.filter(falha =>
        /\/build\/|\/images\/tema-v1\//.test(falha.url)
    );
    expect(essenciais).toEqual([]);
    await page.context().close();
});

test('captura matriz comparável Legacy V1 e V3 em 1440px', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    const v3 = await loginV3(browser);

    await legacy.screenshot({ path: join(DESTINO, 'legacy-home-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-home-1440.png'), fullPage: true });

    await legacy.click('#menu-sessao');
    const paineis = [
        ['usuarios', '#menu-usuarios', '#JS-Usuarios', '/v1/usuarios'],
        ['clientes', '#menu-clientes', '#JS-Clientes', '/v1/parceiros/clientes'],
        ['fabricantes', '#menu-fabricantes', '#JS-Fabricantes', '/v1/parceiros/fabricantes'],
        ['fornecedores', '#menu-fornecedores', '#JS-Fornecedores', '/v1/parceiros/fornecedores'],
        ['assistencias', '#menu-assistencia_tecnicas', '#JS-Assistencia_tecnicas', '/v1/parceiros/assistencias-tecnicas'],
    ] as const;

    for (const [nome, botao, painel, rota] of paineis) {
        await legacy.click(botao);
        await expect(legacy.locator(painel)).toBeVisible();
        await legacy.screenshot({ path: join(DESTINO, `legacy-${nome}-1440.png`), fullPage: true });
        await v3.goto(`${V3}${rota}`, { waitUntil: 'domcontentloaded' });
        await v3.screenshot({ path: join(DESTINO, `v3-${nome}-1440.png`), fullPage: true });
    }

    await legacy.goto(`${LEGACY}index.php?page=entrada`, { waitUntil: 'domcontentloaded' });
    await legacy.screenshot({ path: join(DESTINO, 'legacy-rma-listagem-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma?tipo=texto&valor=QA`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-rma-listagem-1440.png'), fullPage: true });

    const detalheLegacy = await legacy.locator('a[href*="page=detalhes"]').first().getAttribute('href');
    expect(detalheLegacy).not.toBeNull();
    await legacy.goto(new URL(detalheLegacy!, legacy.url()).href, { waitUntil: 'domcontentloaded' });
    await legacy.screenshot({ path: join(DESTINO, 'legacy-rma-detalhe-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma/1`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-rma-detalhe-1440.png'), fullPage: true });

    await legacy.goto(LEGACY, { waitUntil: 'domcontentloaded' });
    await legacy.click('#menu-novo');
    await expect(legacy.locator('#JS-Novo')).toBeVisible();
    await legacy.screenshot({ path: join(DESTINO, 'legacy-rma-novo-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma/create`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-rma-novo-1440.png'), fullPage: true });

    await legacy.context().close();
    await v3.context().close();
});
