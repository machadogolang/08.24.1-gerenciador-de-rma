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

test.describe('Quadrante 4 - Identidade, Auditoria e Controle (V1 e V2)', () => {

    test('Fluxo V1: Gestao de usuarios, painel Controle e logs integrados', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v1');

        // 1. Acesso a tela de usuarios no Tema V1
        await page.goto(`${BASE_URL}/v1/usuarios`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#FIXADO')).toBeVisible();
        await expect(page.locator('table.tabela-usuarios-v1, table.Tabelinha-Table')).toBeVisible();

        // 2. Acesso ao painel Controle V1
        await page.goto(`${BASE_URL}/rmas-controle`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('h1.titulo-v1')).toHaveText(/Controle/i);

        // Painel #01: Adicionar Representante - formulário compacto
        const summaryRep = page.locator('#panel1-details summary.formTitlePanel');
        await expect(summaryRep).toBeVisible();
        await summaryRep.click();
        await expect(page.locator('#panel1-details input[name="nome"]')).toBeVisible();
        await expect(page.locator('#panel1-details select[name="tipo"]')).toBeVisible();

        // Painel #04: Arquivar RMA
        const summaryArq = page.locator('#panel4-details summary.formTitlePanel');
        await expect(summaryArq).toBeVisible();
        await summaryArq.click();
        await expect(page.locator('#panel4-details input[name="numero"]')).toBeVisible();

        // Painéis históricos #05 e #06: formulários preservados
        await expect(page.locator('#panel5-details summary.formTitlePanel')).toBeVisible();
        await expect(page.locator('#panel6-details summary.formTitlePanel')).toBeVisible();

        // Painel #09: Mudar Senha - layout compacto com 3 campos
        const summarySenha = page.locator('#panel9-details summary.formTitlePanel');
        await expect(summarySenha).toBeVisible();
        await summarySenha.click();
        await expect(page.locator('#panel9-details input[name="senha_atual"]')).toBeVisible();
        await expect(page.locator('#panel9-details input[name="nova_senha"]')).toBeVisible();
        await expect(page.locator('#panel9-details input[name="nova_senha_confirmation"]')).toBeVisible();

        // Verifica presenca e navegacao das secoes de auditoria e cadastro no Controle V1
        await expect(page.locator('#panel-logs-autenticacao summary.formTitlePanel')).toBeVisible();
        await expect(page.locator('#panel-logs-modificacao summary.formTitlePanel')).toBeVisible();
        await expect(page.locator('#panel-novo-usuario summary.formTitlePanel')).toBeVisible();

        // 3. Acesso ao formulario de novo usuario V1
        await page.goto(`${BASE_URL}/v1/usuarios/novo`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('input[name="name"], input[name="nome"]').first()).toBeVisible();
        await expect(page.locator('input[name="email"]')).toBeVisible();

        await page.context().close();
    });

    test('Fluxo V2: Gestao de usuarios V2, anotacoes com autosave e troca de senha', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v2');

        // 1. Acesso a tela de usuarios no Tema V2
        await page.goto(`${BASE_URL}/v2/usuarios`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('ul.nav-v2')).toBeVisible();
        await expect(page.locator('table')).toBeVisible();

        // 2. Acesso e teste de autosave de anotacoes no Tema V2
        await page.goto(`${BASE_URL}/v2/anotacoes`, { waitUntil: 'domcontentloaded' });
        const textarea = page.locator('textarea#anotacao, textarea[data-anotacao-autosave]');
        await expect(textarea).toBeVisible();

        const notaUnica = `Nota Fluxo V2 ${Date.now()}`;
        await textarea.fill(notaUnica);
        await expect(page.locator('#status-autosave')).toContainText('Salvo automaticamente', { timeout: 10000 });

        // 3. Acesso a tela de troca de senha no Tema V2
        await page.goto(`${BASE_URL}/v2/perfil/senha`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('input[name="senha_atual"]')).toBeVisible();
        await expect(page.locator('input[name="nova_senha"]')).toBeVisible();

        await page.context().close();
    });

});
