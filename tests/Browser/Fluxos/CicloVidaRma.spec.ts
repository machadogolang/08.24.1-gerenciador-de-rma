import { test, expect, type Browser, type Page } from '@playwright/test';

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(120_000);

async function login(browser: Browser, email = 'superadministrador@rma.local', pass = 'password'): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'load' });
    await page.fill('#email', email);
    await page.fill('#password', pass);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

async function garantirTema(page: Page, tema: 'v1' | 'v2'): Promise<void> {
    for (let tentativa = 0; tentativa < 4; tentativa++) {
        await page.goto(`${BASE_URL}/perfil`, { waitUntil: 'load' });
        const botao = page.locator('button:has-text("Alternar tema")').first();
        const texto = await botao.textContent();
        const atual = texto?.includes('atual: v1') ? 'v1' : texto?.includes('atual: v2') ? 'v2' : null;

        if (atual === tema) {
            return;
        }
        if (atual === null) {
            return;
        }

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            botao.click(),
        ]);
    }
}

test.describe('Quadrante 1 - Ciclo de Vida do RMA (V1 e V2)', () => {

    test('Fluxo V1: Criacao inline, edicao inline e arquivamento', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v1');

        // 1. Acessa criacao e submete RMA
        await page.goto(`${BASE_URL}/rmas/create`, { waitUntil: 'domcontentloaded' });
        const sn = `SN-V1-FLOW-${Date.now()}`;
        await page.fill('input[name="descricao"]', 'Notebook V1 Fluxo Test');
        await page.fill('input[name="defeito"]', 'Defeito para teste de ciclo V1');
        await page.fill('input[name="sn"]', sn);
        await page.fill('input[name="modelo"]', 'Inspiron V1');

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.formButtonEnviarNovo'),
        ]);

        expect(page.url()).toMatch(/\/rmas\/\d+$/);
        const rmaId = page.url().match(/\/rmas\/(\d+)$/)![1];

        // 2. Acessa detalhe V1 explicitamente
        await page.goto(`${BASE_URL}/v1/rma/${rmaId}`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#CONTEUDO input[name="sn"]')).toHaveValue(sn);

        // 3. Edita input no detalhe V1 e salva
        await page.fill('#CONTEUDO input[name="os"]', 'OS-FLOW-V1');
        const btnSalvar = page.locator('#CONTEUDO button.formButtonEnviarShow:has-text("SALVAR"), #CONTEUDO button:has-text("SALVAR")').first();
        if (await btnSalvar.isVisible()) {
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                btnSalvar.click(),
            ]);
            await expect(page.locator('#CONTEUDO input[name="os"]')).toHaveValue('OS-FLOW-V1');
        }

        // 4. Arquiva RMA via painel Controle V1
        await page.goto(`${BASE_URL}/rmas-controle`, { waitUntil: 'domcontentloaded' });
        const summaryArquivar = page.locator('summary.formTitlePanel:has-text("ARQUIVAR UMA SOLICITACAO DE RMA")');
        await expect(summaryArquivar).toBeVisible();
        await summaryArquivar.click();

        await page.fill('form[action$="/rmas-controle/arquivar"] input[name="numero"]', rmaId);
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('form[action$="/rmas-controle/arquivar"] button[type="submit"]'),
        ]);

        await expect(page.getByText(/RMA \d+ arquivado com sucesso/).first()).toBeVisible();
        await page.context().close();
    });

    test('Fluxo V2: Criacao inline na aba Novo, edicao operacional e persistencia', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v2');

        // 1. Acessa lista V2 e aba Novo
        await page.goto(`${BASE_URL}/v2/rma`, { waitUntil: 'domcontentloaded' });
        await page.click('a[href="#novo_rma"]');

        const formulario = page.locator('#novo_rma form');
        await expect(formulario.locator('button[type="submit"]')).toContainText('CRIAR BD');

        const snV2 = `SN-V2-FLOW-${Date.now()}`;
        await formulario.locator('select[name="origem"]').selectOption('Cliente');
        await formulario.locator('input[name="descricao"]').fill('Servidor V2 Fluxo Test');
        await formulario.locator('input[name="modelo"]').fill('PowerEdge V2');
        await formulario.locator('input[name="sn"]').fill(snV2);
        await formulario.locator('input[name="cliente_nome"]').fill('Cliente Fluxo V2');
        await formulario.locator('textarea[name="defeito"]').fill('Falha no disco 0');

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formulario.locator('button[type="submit"]').click(),
        ]);

        expect(page.url()).toMatch(/\/v2\/rma\/\d+$/);
        const rmaIdV2 = page.url().match(/\/v2\/rma\/(\d+)$/)![1];

        // 2. Verifica detalhe operacional V2
        await expect(page.locator('input[name="sn"]')).toHaveValue(snV2);

        // 3. Edita no formulario operacional V2 e salva
        await page.fill('form.detalhe-rma-v2__form textarea[name="observacao"]', 'Observacao persistida pelo fluxo V2.');
        await Promise.all([
            page.waitForResponse((res) => res.request().method() === 'POST' && res.url().includes(`/rma/${rmaIdV2}`)),
            page.click('.detalhe-rma-v2__acoes-finais button[type="submit"]'),
        ]);

        // 4. Confirma persistencia apos reload
        await page.reload({ waitUntil: 'domcontentloaded' });
        await expect(page.locator('form.detalhe-rma-v2__form textarea[name="observacao"]')).toContainText('Observacao persistida pelo fluxo V2.');
        await page.context().close();
    });

});
