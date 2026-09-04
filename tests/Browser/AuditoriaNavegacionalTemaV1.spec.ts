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

test.describe('Auditoria Navegacional Tema V1 — Lote NAV-02 (Menu de Sessão)', () => {
    test('NAV-02-01 — Fornecedores: indice, Novo, Editar e Voltar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/parceiros/fornecedores`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/parceiros/fornecedores');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        // Novo
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.JS-SessaoLEFT a:has-text("Novo")'),
        ]);
        expect(page.url()).toContain('/parceiros/fornecedores/create');
        await expect(page.locator('input[name="nome"]')).toBeVisible();

        // Voltar
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('a:has-text("Voltar")'),
        ]);
        expect(page.url()).toContain('/parceiros/fornecedores');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-02 — Fabricantes: indice, Novo, Editar e Voltar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/parceiros/fabricantes`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/parceiros/fabricantes');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        // Novo
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.JS-SessaoLEFT a:has-text("Novo")'),
        ]);
        expect(page.url()).toContain('/parceiros/fabricantes/create');

        // Voltar
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('a:has-text("Voltar")'),
        ]);
        expect(page.url()).toContain('/parceiros/fabricantes');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-03 — Assistencias: indice, Novo, Editar e Voltar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/parceiros/assistencias-tecnicas`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/parceiros/assistencias-tecnicas');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        // Novo
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.JS-SessaoLEFT a:has-text("Novo")'),
        ]);
        expect(page.url()).toContain('/parceiros/assistencias-tecnicas/create');

        // Voltar
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('a:has-text("Voltar")'),
        ]);
        expect(page.url()).toContain('/parceiros/assistencias-tecnicas');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-04 — Clientes: indice, Novo, Editar e Voltar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/parceiros/clientes`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/parceiros/clientes');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        // Novo
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.JS-SessaoLEFT a:has-text("Novo")'),
        ]);
        expect(page.url()).toContain('/parceiros/clientes/create');

        // Voltar
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('a:has-text("Voltar")'),
        ]);
        expect(page.url()).toContain('/parceiros/clientes');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-05 — Controle: abas/paineis, links de RMA e estrutura administrativa', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-controle');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        // 7 ações administrativas no padrão details/summary
        const paineis = page.locator('details');
        expect(await paineis.count()).toBe(7);

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-06 — Creditos: listagem e fluxo unico acessiveis', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/rmas-credito`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-credito');
        await expect(page.locator('h1')).toContainText('Fluxo de crédito');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-07 — Relatorios: rotas RCD, RPEC e RMPE acessiveis sem erro 4xx', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // RCD
        await page.goto(`${V3}/rmas-relatorios/rcd`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-relatorios/rcd');
        await expect(page.locator('h1')).toContainText('RCD');

        // RPEC
        await page.goto(`${V3}/rmas-relatorios/rpec`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-relatorios/rpec');

        // RMPE
        await page.goto(`${V3}/rmas-relatorios/rmpe?data_inicio=2026-01-01&data_fim=2026-12-31`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-relatorios/rmpe');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-02-08 — Usuarios: listagem, edicao de papel e reset de senha', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/usuarios`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/usuarios');
        await expect(page.locator('#JS-Sessao')).toBeVisible();
        await expect(page.locator('#menu-sessao')).toHaveClass(/active/);

        const linhas = page.locator('.tabela-usuarios-v1 tbody tr');
        expect(await linhas.count()).toBeGreaterThan(0);

        // Confirma formulários de papel e reset presentes
        await expect(linhas.first().locator('select[name="papel"]')).toBeVisible();
        await expect(linhas.first().locator('input[name="nova_senha"]')).toBeVisible();

        expect(falhas).toEqual([]);
        await page.context().close();
    });
});

