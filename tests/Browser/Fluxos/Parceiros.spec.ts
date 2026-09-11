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

test.describe('Quadrante 2 - Gestao de Parceiros e Contrapartes (V1 e V2)', () => {

    test('Fluxo V1: Cadastro, edicao, detalhe e RMAs associados de parceiro', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v1');

        // 1. Acessa lista de fornecedores no Tema V1
        await page.goto(`${BASE_URL}/v1/parceiros/fornecedores`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#FIXADO')).toBeVisible();

        // 2. Clica em Novo na pagina de fornecedores
        const btnNovo = page.locator('a[href*="/parceiros/fornecedores/create"]').first();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            btnNovo.click(),
        ]);
        await expect(page).toHaveURL(/\/parceiros\/fornecedores\/create/);

        const nomeFornecedor = `Fornecedor Fluxo V1 ${Date.now()}`;
        await page.fill('input[name="nome"]', nomeFornecedor);
        await page.fill('input[name="cpf_cnpj"]', '12.345.678/0001-90');
        await page.fill('input[name="email"]', 'contato@fornecedor-v1.test');

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('button.buttonSave'),
        ]);

        // 3. Localiza o parceiro criado na tabela e abre o detalhe
        const linha = page.locator('tr', { hasText: nomeFornecedor });
        await expect(linha).toBeVisible();
        const linkVer = linha.locator('a:has-text("Ver")');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkVer.click(),
        ]);

        // 4. Valida secao de RMAs associados no detalhe V1
        await expect(page.locator('text=RMAs associados')).toBeVisible();

        // 5. Clica em Editar e atualiza telefone/contato
        const linkEditar = page.locator('a:has-text("EDITAR")').first();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkEditar.click(),
        ]);

        await page.fill('input[name="telefone"]', '(51) 98765-4321');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('button.buttonSave'),
        ]);

        // Confirma mensagem de sucesso na listagem apos update
        await expect(page.getByText('Fornecedor atualizado.').first()).toBeVisible();

        // Abre o detalhe para confirmar o telefone atualizado persistido
        const linhaAtualizada = page.locator('tr', { hasText: nomeFornecedor });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linhaAtualizada.locator('a:has-text("Ver")').click(),
        ]);
        await expect(page.locator('text=(51) 98765-4321')).toBeVisible();

        await page.context().close();
    });

    test('Fluxo V2: Cadastro, edicao, detalhe e RMAs associados no padrao 15.8.1', async ({ browser }) => {
        const page = await login(browser);
        await garantirTema(page, 'v2');

        // 1. Acessa clientes no Tema V2
        await page.goto(`${BASE_URL}/v2/parceiros/clientes`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('ul.nav-v2')).toBeVisible();

        // 2. Clica em Novo na pagina de clientes
        const btnNovoV2 = page.locator('a[href*="/parceiros/clientes/create"]').first();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            btnNovoV2.click(),
        ]);
        await expect(page).toHaveURL(/\/parceiros\/clientes\/create/);

        const nomeClienteV2 = `Cliente Fluxo V2 ${Date.now()}`;
        await page.fill('input[name="nome"]', nomeClienteV2);
        await page.fill('input[name="cpf_cnpj"]', '98.765.432/0001-10');
        await page.fill('input[name="email"]', 'contato@cliente-v2.test');

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.container form button[type="submit"]'),
        ]);

        // 3. Localiza e abre o detalhe
        const linhaV2 = page.locator('tr', { hasText: nomeClienteV2 });
        await expect(linhaV2).toBeVisible();
        const linkVerV2 = linhaV2.locator('a:has-text("Ver")');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkVerV2.click(),
        ]);

        // 4. Valida secao de RMAs associados no detalhe V2
        await expect(page.locator('text=RMAs associados')).toBeVisible();

        // 5. Clica em Editar
        const linkEditarV2 = page.locator('.detalhe-parceiro a:has-text("Editar")').first();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkEditarV2.click(),
        ]);

        await page.fill('input[name="cidade"]', 'Porto Alegre');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('.container form button[type="submit"]'),
        ]);

        // Confirma que voltou ao detalhe e mostra cidade atualizada
        await expect(page.getByRole('cell', { name: 'Porto Alegre' }).first()).toBeVisible();
        await page.context().close();
    });

});
