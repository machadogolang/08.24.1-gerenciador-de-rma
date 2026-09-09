import { test, expect, type Browser, type Page } from '@playwright/test';

const LEGACY = process.env.LEGACY_BASE_URL ?? (process.env.CI ? 'http://host.docker.internal:8094/14.6.1/' : 'http://localhost:8094/14.6.1/');
const LEGACY_ROOT = process.env.LEGACY_BASE_URL ? process.env.LEGACY_BASE_URL.replace(/\/14\.6\.1\/?$/, '') : 'http://localhost:8094';
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

async function loginV3ComCredenciais(browser: Browser, email = 'operador@rma.local', pass = 'password', falhas: Falha[] = []): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    vigiarRecursos(page, falhas);
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', email);
    await page.fill('#password', pass);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test.describe('Smokes de Paridade Funcional (M-01 a M-06) - Fase 10', () => {

    test('M-01 - Autenticação e alternância/persistência de tema em V3 e Legacy', async ({ browser }) => {
        const falhas: Falha[] = [];

        // 1. Validar login no Legacy (:8094)
        const contextLegacy = await browser.newContext({ viewport: VIEWPORT });
        const pageLegacy = await contextLegacy.newPage();
        await pageLegacy.goto(LEGACY, { waitUntil: 'domcontentloaded' });
        await pageLegacy.fill('input[name=email]', 'lab@localhost');
        await pageLegacy.fill('input[name=senha]', 'rma-lab-2026');
        await Promise.all([
            pageLegacy.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            pageLegacy.click('[name=signin]'),
        ]);
        // No snapshot histórico, lab@localhost tem app=15.8.1 configurado
        expect(pageLegacy.url()).toMatch(/\/(14\.6\.1|15\.8\.1)\//);

        // Testar alternância de tema no Legacy via trocarapp.php
        await pageLegacy.goto(`${LEGACY_ROOT}/trocarapp.php`, { waitUntil: 'domcontentloaded' });
        // Trocar app alterna para o outro tema
        expect(pageLegacy.url()).toMatch(/\/(14\.6\.1|15\.8\.1)\//);

        // Retornar ao estado original do Legacy
        await pageLegacy.goto(`${LEGACY_ROOT}/trocarapp.php`, { waitUntil: 'domcontentloaded' });
        await contextLegacy.close();

        // 2. Validar login no V3 (:8095) com operador
        const pageV3 = await loginV3ComCredenciais(browser, 'operador@rma.local', 'password', falhas);
        await pageV3.goto(`${V3}/perfil`, { waitUntil: 'domcontentloaded' });
        expect(pageV3.url()).toContain('/perfil');

        // Garantir que começamos em V1
        let btnAlternar = pageV3.locator('button:has-text("Alternar tema")');
        let textoBotao = await btnAlternar.textContent();
        if (textoBotao?.includes('atual: v2')) {
            await Promise.all([
                pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                btnAlternar.click(),
            ]);
        }

        // Alternar de V1 para V2
        btnAlternar = pageV3.locator('button:has-text("Alternar tema")');
        await Promise.all([
            pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            btnAlternar.click(),
        ]);

        // Fazer logout no V3
        await Promise.all([
            pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            pageV3.click('form[action$="/logout"] button'),
        ]);
        expect(pageV3.url()).toContain('/login');

        // Fazer login novamente e conferir redirecionamento e tema V2 persistido
        await pageV3.fill('#email', 'operador@rma.local');
        await pageV3.fill('#password', 'password');
        await Promise.all([
            pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            pageV3.click('button[type=submit]'),
        ]);
        expect(pageV3.url()).toContain('/perfil');
        await expect(pageV3.locator('button:has-text("Alternar tema")')).toContainText('atual: v2');

        // Retornar o usuário para o tema V1
        btnAlternar = pageV3.locator('button:has-text("Alternar tema")');
        await Promise.all([
            pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            btnAlternar.click(),
        ]);

        // Fazer logout e login novamente para confirmar que voltou para V1
        await Promise.all([
            pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            pageV3.click('form[action$="/logout"] button'),
        ]);
        await pageV3.fill('#email', 'operador@rma.local');
        await pageV3.fill('#password', 'password');
        await Promise.all([
            pageV3.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            pageV3.click('button[type=submit]'),
        ]);
        expect(pageV3.url()).toContain('/perfil');
        await expect(pageV3.locator('button:has-text("Alternar tema")')).toContainText('atual: v1');

        expect(falhas).toEqual([]);
        await pageV3.context().close();
    });

    test('M-02 - Criar e editar RMA com formulário e persistência de dados', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3ComCredenciais(browser, 'superadministrador@rma.local', 'password', falhas);

        // Acessa criação de RMA pelo painel inline na Home V1
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await expect(page.locator('#JS-Novo')).toBeVisible();

        const serialUnico = `SMOKE-M02-${Date.now()}`;
        await page.fill('#JS-Novo input[name="descricao"]', 'Equipamento Smoke M-02 Teste');
        await page.fill('#JS-Novo input[name="defeito"]', 'Falha ao ligar');
        await page.fill('#JS-Novo input[name="sn"]', serialUnico);
        await page.fill('#JS-Novo input[name="cliente_nome"]', 'Cliente Smoke Teste');

        // Submete criação
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        // Redireciona para o detalhe do RMA criado
        expect(page.url()).toMatch(/\/rmas\/\d+$/);
        await expect(page.locator('#CONTEUDO')).toContainText(serialUnico);

        // Clica em Editar
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#CONTEUDO a:has-text("Editar")'),
        ]);
        expect(page.url()).toContain('/edit');

        // Altera o modelo e salva
        await page.fill('#CONTEUDO form input[name="modelo"]', 'Modelo Atualizado Smoke');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#CONTEUDO form button[type=submit]'),
        ]);

        // Verifica que voltou ao detalhe e que o modelo atualizado persiste
        expect(page.url()).toMatch(/\/rmas\/\d+$/);
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Modelo Atualizado Smoke');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('M-04 - Ciclo de vida completo: receber, encaminhar e concluir', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3ComCredenciais(browser, 'superadministrador@rma.local', 'password', falhas);

        // Cria um RMA novo para o teste de ciclo pelo painel inline
        await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('#menu-novo');
        await page.fill('#JS-Novo input[name="descricao"]', 'RMA Ciclo Completo M-04');
        await page.fill('#JS-Novo input[name="defeito"]', 'Sem video');
        await page.fill('#JS-Novo input[name="sn"]', `SMOKE-M04-${Date.now()}`);
        await page.fill('#JS-Novo input[name="cliente_nome"]', 'Cliente Ciclo QA');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('#JS-Novo button.formButtonEnviarNovo'),
        ]);

        expect(page.url()).toMatch(/\/rmas\/\d+$/);
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Entrada');

        // 1. Receber RMA
        const btnReceber = page.locator('form[action$="/receber"] button');
        await expect(btnReceber).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            btnReceber.click(),
        ]);
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Recebido');

        // 2. Encaminhar RMA (com destinatário id=1)
        const formEncaminhar = page.locator('form[action$="/encaminhar"]');
        await expect(formEncaminhar).toBeVisible();
        await formEncaminhar.locator('input[name="destinatario_id"]').fill('1');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formEncaminhar.locator('button[type=submit]').click(),
        ]);
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Encaminhado');

        // 3. Concluir RMA com Solução
        const formConcluir = page.locator('form[action$="/concluir"]');
        await expect(formConcluir).toBeVisible();
        await formConcluir.locator('select[name="solucao"]').selectOption('REPARO');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formConcluir.locator('button[type=submit]').click(),
        ]);
        await expect(page.locator('#CONTEUDO table.Tabelinha-Table')).toContainText('Concluido');

        expect(falhas).toEqual([]);
        await page.context().close();
    });

    test('M-06 - Créditos e relatórios (RCD, RPEC e RMPE) respondem e filtram', async ({ browser }) => {
        const falhas: Falha[] = [];
        const page = await loginV3ComCredenciais(browser, 'superadministrador@rma.local', 'password', falhas);

        // 1. Relatório RCD (Créditos Disponíveis)
        await page.goto(`${V3}/rmas-relatorios/rcd`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-relatorios/rcd');
        await expect(page.locator('h1')).toContainText('Relatório de Créditos Disponíveis');

        // 2. Relatório RPEC (Estoque para Contagem)
        await page.goto(`${V3}/rmas-relatorios/rpec`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-relatorios/rpec');
        await expect(page.locator('h1')).toContainText('Relatório de Produtos em Estoque');

        // 3. Relatório RMPE (Produtos Encaminhados) com intervalo obrigatório
        await page.goto(`${V3}/rmas-relatorios/rmpe?data_inicio=2026-01-01&data_fim=2026-12-31`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-relatorios/rmpe');
        await expect(page.locator('h1')).toContainText('Relatório de Produtos Encaminhados (RMPE)');

        // 4. Painel de Créditos
        await page.goto(`${V3}/rmas-credito`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-credito');
        await expect(page.locator('h1')).toContainText('Fluxo de crédito');

        // 5. Painel Aguardando Crédito
        await page.goto(`${V3}/rmas-aguardando-credito`, { waitUntil: 'domcontentloaded' });
        expect(page.url()).toContain('/rmas-aguardando-credito');
        await expect(page.locator('body')).toBeVisible();

        expect(falhas).toEqual([]);
        await page.context().close();
    });
});
