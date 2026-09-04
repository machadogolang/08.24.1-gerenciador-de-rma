import { test, expect, type Browser, type Page } from '@playwright/test';

const LEGACY = process.env.LEGACY_BASE_URL ?? (process.env.CI ? 'http://host.docker.internal:8094/14.6.1/' : 'http://localhost:8094/14.6.1/');
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? (process.env.CI ? 'http://localhost' : 'http://localhost:8095');
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(120_000);

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

test.describe('Auditoria Navegacional Tema V1 — Lote NAV-01 (Menu Superior)', () => {
    test('NAV-01-01 — logo navega para a Pagina Inicial sem erro de recurso', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Vai para outra página para testar clique no logo
        await page.goto(`${V3}/rmas-entrada`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#TOPO a.image-up'),
        ]);

        expect(page.url()).toMatch(/\/(v1\/rma|rmas)$/);
        await expect(page.locator('#TOPO')).toBeVisible();
        await expect(page.locator('#CONTEUDO')).toBeVisible();
        await expect(page.locator('#RODAPE')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-02 — link Pag. Inicial navega e marca classe active no menu', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/rmas-entrada`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('li.menu-up:has-text("Pag. Inicial") a'),
        ]);

        expect(page.url()).toMatch(/\/(v1\/rma|rmas)$/);
        await expect(page.locator('li.menu-up:has-text("Pag. Inicial")')).toHaveClass(/active/);
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-03 — Novo expande o painel inline #JS-Novo sem navegar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        const urlAntes = page.url();

        await page.click('#menu-novo');
        await expect(page.locator('#JS-Novo')).toBeVisible();
        expect(page.url()).toBe(urlAntes);
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-04 — Localizar expande o painel inline #JS-Localizar sem navegar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Na Entrada, #JS-Localizar começa oculto
        await page.goto(`${V3}/rmas-entrada`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#JS-Localizar')).toBeHidden();

        await page.click('#menu-localizar');
        await expect(page.locator('#JS-Localizar')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-05 — Entrada navega com classe active e estrutura completa', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('li.menu-up:has-text("Entrada") a'),
        ]);

        expect(page.url()).toContain('/rmas-entrada');
        await expect(page.locator('li.menu-up:has-text("Entrada")')).toHaveClass(/active/);
        await expect(page.locator('#TOPO')).toBeVisible();
        await expect(page.locator('#CONTEUDO')).toBeVisible();
        await expect(page.locator('#RODAPE')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-06 — Encaminhado navega com classe active e estrutura completa', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('li.menu-up:has-text("Encaminhado") a'),
        ]);

        expect(page.url()).toContain('/rmas-encaminhados');
        await expect(page.locator('li.menu-up:has-text("Encaminhado")')).toHaveClass(/active/);
        await expect(page.locator('#TOPO')).toBeVisible();
        await expect(page.locator('#CONTEUDO')).toBeVisible();
        await expect(page.locator('#RODAPE')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-07 — Aguardando credito navega com classe active e estrutura completa', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('li.menu-up:has-text("Aguardando credito") a'),
        ]);

        expect(page.url()).toContain('/rmas-aguardando-credito');
        await expect(page.locator('li.menu-up:has-text("Aguardando credito")')).toHaveClass(/active/);
        await expect(page.locator('#TOPO')).toBeVisible();
        await expect(page.locator('#CONTEUDO')).toBeVisible();
        await expect(page.locator('#RODAPE')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-08 — Concluido! navega com classe active e estrutura completa', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('li.menu-up:has-text("Concluido!") a'),
        ]);

        expect(page.url()).toContain('/rmas-concluidos');
        await expect(page.locator('li.menu-up:has-text("Concluido!")')).toHaveClass(/active/);
        await expect(page.locator('#TOPO')).toBeVisible();
        await expect(page.locator('#CONTEUDO')).toBeVisible();
        await expect(page.locator('#RODAPE')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-09 — botao MENU alterna painel de sessao #JS-Sessao e classe active', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#JS-Sessao')).toBeHidden();

        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeHidden();
        expect(falhas).toEqual([]);

        await page.context().close();
    });

    test('NAV-01-10 — botao SIGN OUT executa logout e redireciona para login', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.formButtonSIGNOUT'),
        ]);

        expect(page.url()).toContain('/login');
        await expect(page.locator('input[name=email]')).toBeVisible();
        expect(falhas).toEqual([]);

        await page.context().close();
    });
});
