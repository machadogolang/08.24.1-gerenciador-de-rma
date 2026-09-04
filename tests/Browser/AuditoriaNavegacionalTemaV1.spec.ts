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

test.describe('Auditoria Navegacional Tema V1 — Lote NAV-03 (Pagina Inicial e Centro de Avisos)', () => {
    test('NAV-03-01 — 16 contadores laterais: links e navegacao corretos', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        const contadores = page.locator('.contadores-do-painel a');
        expect(await contadores.count()).toBe(16);

        // Testa navegação de um contador de status (ex: ENTRADA)
        const entradaCounter = page.locator('.contadores-do-painel .formLabelStats:text-is("ENTRADA")');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            entradaCounter.click(),
        ]);
        expect(page.url()).toContain('/rmas-entrada');

        // Volta e testa um contador de solução
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        const solCounter = page.locator('.contadores-do-painel .formLabelStats:text-is("SEM GARANTIA")');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            solCounter.click(),
        ]);
        expect(decodeURIComponent(page.url())).toContain('solucao=SEM GARANTIA');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-03-03 — Centro de Avisos: alternancia Mostrar/Ocultar nos 10 grupos', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        const grupos = page.locator('.regra-de-alerta');
        expect(await grupos.count()).toBe(10);

        for (let i = 0; i < 10; i++) {
            const grupo = grupos.nth(i);
            const pmo = grupo.locator('.pmo');
            expect(await pmo.textContent()).toContain('Mostrar');

            await pmo.click();
            expect(await pmo.textContent()).toContain('Ocultar');

            await pmo.click();
            expect(await pmo.textContent()).toContain('Mostrar');
        }

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-03-04 — resultado de Localizar exibe tabela e acoes Ver e Editar', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.fill('#JS-Localizar input[name="valor"]', 'A');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Localizar .JSformLocalizarButton'),
        ]);

        expect(page.url()).toContain('valor=A');
        await expect(page.locator('#CONTEUDO')).toBeVisible();

        const tabela = page.locator('#CONTEUDO table.Tabelinha-Table');
        if (await tabela.count() > 0) {
            const primeiraLinha = tabela.locator('tbody tr').first();
            await expect(primeiraLinha.locator('a:has-text("Ver")')).toBeVisible();
            await expect(primeiraLinha.locator('a:has-text("Editar")')).toBeVisible();
        }

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-03-05 — autosave de Anotacoes: persistencia com status 200', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        const anotacao = page.locator('#anotacao');
        await expect(anotacao).toBeVisible();

        const reqPromise = page.waitForResponse(resp => resp.url().includes('/perfil/anotacao') && resp.status() === 200);
        await anotacao.type(' update autosave test');
        const resp = await reqPromise;
        expect(resp.status()).toBe(200);
        const json = await resp.json();
        expect(json).toEqual({ status: 'ok' });

        expect(falhas).toEqual([]);
        await page.context().close();
    });
});

test.describe('Auditoria Navegacional Tema V1 — Lote NAV-04 (Ciclo de Vida e Links Internos)', () => {
    test('NAV-04-01 — detalhe do RMA e Editar: navegacao e estrutura', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/rmas-entrada`, { waitUntil: 'domcontentloaded' });
        const primeiroLinkRma = page.locator('#CONTEUDO table.Tabelinha-Table a[href*="/rmas/"]').first();
        await expect(primeiroLinkRma).toBeVisible();

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            primeiroLinkRma.click(),
        ]);

        expect(page.url()).toMatch(/\/rmas\/\d+$/);
        await expect(page.locator('#TOPO')).toBeVisible();
        await expect(page.locator('#CONTEUDO')).toBeVisible();
        await expect(page.locator('#RODAPE')).toBeVisible();

        const linkEditar = page.locator('#CONTEUDO a:has-text("Editar")');
        await expect(linkEditar).toBeVisible();

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkEditar.click(),
        ]);

        expect(page.url()).toMatch(/\/rmas\/\d+\/edit$/);
        await expect(page.locator('button.buttonSave')).toBeVisible();
        await expect(page.locator('a:has-text("Voltar")')).toBeVisible();

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-02 — editar/salvar/voltar em RMA QA', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/rmas-entrada`, { waitUntil: 'domcontentloaded' });
        const primeiroLinkRma = page.locator('#CONTEUDO table.Tabelinha-Table a[href*="/rmas/"]').first();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            primeiroLinkRma.click(),
        ]);

        const urlDetalhe = page.url();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#CONTEUDO a:has-text("Editar")'),
        ]);

        // Testa o link "Voltar"
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('a:has-text("Voltar")'),
        ]);
        expect(page.url()).toBe(urlDetalhe);

        // Edita e salva
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#CONTEUDO a:has-text("Editar")'),
        ]);

        await page.fill('#CONTEUDO form input[name="modelo"]', 'MODELO QA AUDITORIA');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('button.buttonSave'),
        ]);

        expect(page.url()).toBe(urlDetalhe);
        await expect(page.locator('.centrodeavisos')).toContainText('RMA atualizado.');
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('MODELO QA AUDITORIA');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-03 — receber RMA QA: transicao Entrada -> Recebido', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Cria RMA descartável pelo painel inline #JS-Novo
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await page.fill('#JS-Novo input[name="descricao"]', 'DISPOSITIVO QA RECEBER');
        await page.fill('#JS-Novo input[name="defeito"]', 'DEFEITO QA TESTE');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        expect(page.url()).toMatch(/\/rmas\/\d+$/);
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Entrada');

        // Executa ação "Receber"
        const formReceber = page.locator('form[action$="/receber"] button');
        await expect(formReceber).toBeVisible();

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formReceber.click(),
        ]);

        await expect(page.locator('.centrodeavisos')).toContainText('RMA recebido.');
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Recebido');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-04 — encaminhar RMA QA: transicao Recebido -> Encaminhado', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Cria e recebe RMA descartável
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await page.fill('#JS-Novo input[name="descricao"]', 'DISPOSITIVO QA ENCAMINHAR');
        await page.fill('#JS-Novo input[name="defeito"]', 'DEFEITO QA TESTE');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('form[action$="/receber"] button'),
        ]);

        // Executa ação "Encaminhar"
        const formEncaminhar = page.locator('form[action$="/encaminhar"]');
        await expect(formEncaminhar).toBeVisible();

        await formEncaminhar.locator('input[name="destinatario_id"]').fill('1');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formEncaminhar.locator('button[type="submit"]').click(),
        ]);

        await expect(page.locator('.centrodeavisos')).toContainText('RMA encaminhado.');
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Encaminhado');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-05 — concluir RMA QA: transicao Encaminhado -> Concluido', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Cria, recebe e encaminha RMA descartável
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await page.fill('#JS-Novo input[name="descricao"]', 'DISPOSITIVO QA CONCLUIR');
        await page.fill('#JS-Novo input[name="defeito"]', 'DEFEITO QA TESTE');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('form[action$="/receber"] button'),
        ]);

        const formEncaminhar = page.locator('form[action$="/encaminhar"]');
        await formEncaminhar.locator('input[name="destinatario_id"]').fill('1');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formEncaminhar.locator('button[type="submit"]').click(),
        ]);

        // Executa ação "Concluir"
        const formConcluir = page.locator('form[action$="/concluir"]');
        await expect(formConcluir).toBeVisible();

        await formConcluir.locator('select[name="solucao"]').selectOption('REPARO');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formConcluir.locator('button[type="submit"]').click(),
        ]);

        await expect(page.locator('.centrodeavisos')).toContainText('RMA concluído.');
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Concluido');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-06 — reverter RMA QA para Entrada', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Cria e recebe RMA descartável
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await page.fill('#JS-Novo input[name="descricao"]', 'DISPOSITIVO QA REVERTER');
        await page.fill('#JS-Novo input[name="defeito"]', 'DEFEITO QA TESTE');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('form[action$="/receber"] button'),
        ]);

        // Executa ação "Reverter para Entrada"
        const formReverter = page.locator('form[action$="/reverter"] button');
        await expect(formReverter).toBeVisible();

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formReverter.click(),
        ]);

        await expect(page.locator('.centrodeavisos')).toContainText('RMA revertido para Entrada.');
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Entrada');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-07 — arquivar RMA QA e listar em Controle', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Cria RMA descartável
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await page.fill('#JS-Novo input[name="descricao"]', 'DISPOSITIVO QA ARQUIVAR');
        await page.fill('#JS-Novo input[name="defeito"]', 'DEFEITO QA TESTE');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        const match = page.url().match(/\/rmas\/(\d+)$/);
        expect(match).not.toBeNull();
        const rmaId = match![1];

        // Executa "Arquivar"
        const formArquivar = page.locator('form[action$="/arquivar"] button');
        await expect(formArquivar).toBeVisible();

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formArquivar.click(),
        ]);

        await expect(page.locator('.centrodeavisos')).toContainText('RMA arquivado.');
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Arquivado');

        // Abre /rmas-controle e verifica listagem de arquivados
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });
        const summaryArquivados = page.locator('summary:has-text("LISTAR SOLICITACOES DE RMA ARQUIVADAS")');
        await summaryArquivados.click();

        const itemArquivado = page.locator(`.tdcontrole1 a[href$="/rmas/${rmaId}"]`);
        await expect(itemArquivado.first()).toBeVisible();

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-08 — histórico de modificações e histórico de acessos', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        // Histórico de modificações
        await page.goto(`${V3}/rmas-historico`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-historico');
        await expect(page.locator('h1')).toContainText('Histórico de modificações');
        await expect(page.locator('table')).toBeVisible();

        // Histórico de acessos
        await page.goto(`${V3}/historico-de-acesso`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/historico-de-acesso');
        await expect(page.locator('h1')).toContainText('Histórico de acesso');
        await expect(page.locator('table')).toBeVisible();

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-09 — perfil: alternar tema, trocar senha e anotação em usuário QA', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/perfil`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/perfil');

        // 1. Testa alternar tema e voltar
        const botaoTema = page.locator('button:has-text("Alternar tema")');
        await expect(botaoTema).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            botaoTema.click(),
        ]);
        // Alterna de volta para V1
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.locator('button:has-text("Alternar tema")').click(),
        ]);

        // 2. Formulário de troca de senha presente
        await expect(page.locator('input[name="senha_atual"]')).toBeVisible();
        await expect(page.locator('input[name="nova_senha"]')).toBeVisible();
        await expect(page.locator('input[name="nova_senha_confirmation"]')).toBeVisible();

        // 3. Salvar anotação pessoal
        const textarea = page.locator('#anotacao-textarea');
        await expect(textarea).toBeVisible();
        await textarea.fill('Anotação de teste perfil QA');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('form[action$="/perfil/anotacao"] button'),
        ]);
        await expect(page.locator('#anotacao-textarea')).toHaveValue('Anotação de teste perfil QA');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('NAV-04-10 — link externo do rodapé: validar href e atributos de seguranca', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3(browser, falhas);

        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        const linkRodape = page.locator('#RODAPE .designedby a');
        await expect(linkRodape).toBeVisible();

        expect(await linkRodape.getAttribute('href')).toBe('http://scripting.com.br');
        expect(await linkRodape.getAttribute('target')).toBe('_blank');
        expect(await linkRodape.getAttribute('rel')).toContain('noopener');

        expect(falhas).toEqual([]);
        await page.context().close();
    });
});



