import { test, expect, type Page } from '@playwright/test';

const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

async function garantirTemaV1(page: Page): Promise<void> {
    await page.goto(`${V3}/perfil`, { waitUntil: 'load' });
    for (let tentativa = 0; tentativa < 4; tentativa++) {
        const texto = await page.locator('button:has-text("Alternar tema")').textContent();
        if (texto?.includes('atual: v1')) {
            return;
        }
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.locator('button:has-text("Alternar tema")').click(),
        ]);
        await page.goto(`${V3}/perfil`, { waitUntil: 'load' });
    }
}

async function loginAdminV1(page: Page): Promise<void> {
    await page.setViewportSize(VIEWPORT);
    await page.goto(`${V3}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]'),
    ]);
    await garantirTemaV1(page);
}

test.describe('ControleV1Navegacao - Refinamento do Painel Controle V1 (UI-V1-CONTROLE-01)', () => {
    test('Cenário 1: Click-through pelo menu lateral V1 até o painel Controle', async ({ page }) => {
        await loginAdminV1(page);

        await page.goto(`${V3}/rmas`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('#menu-sessao')).toBeVisible();

        // Expande o menu lateral
        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();

        // Clica no item Controle
        const linkControle = page.locator('#JS-Sessao a.lisessao', { hasText: 'Controle' });
        await expect(linkControle).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkControle.click(),
        ]);

        await expect(page).toHaveURL(/rmas-controle/);
        await expect(page.locator('.JS-DivLEFT')).toBeVisible();
        await expect(page.locator('summary.formTitlePanel', { hasText: 'ADICIONAR REPRESENTANTE' })).toBeVisible();
    });

    test('Cenário 2: Click-through real nos links promovidos dentro de Controle V1', async ({ page }) => {
        await loginAdminV1(page);
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });

        // 1. Procedimento de RMA -> Central de Ajuda
        const detailsAjuda = page.locator('details:has(summary:has-text("INFORMACAO DO PROCEDIMENTO DE RMA"))');
        await detailsAjuda.locator('summary').click();
        const linkAjuda = detailsAjuda.locator('a:has-text("ABRIR CENTRAL DE AJUDA")');
        await expect(linkAjuda).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkAjuda.click(),
        ]);
        await expect(page).toHaveURL(/ajuda/);
        await expect(page.locator('h1.title-comicone')).toContainText('Central de Ajuda');

        // Volta ao Controle
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });

        // 2. Logs de Autenticação
        const detailsAuth = page.locator('details:has(summary:has-text("LOGS DE AUTENTICAÇÃO"))');
        await detailsAuth.locator('summary').click();
        const linkAuth = detailsAuth.locator('a:has-text("ABRIR LOGS DE AUTENTICAÇÃO")');
        await expect(linkAuth).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkAuth.click(),
        ]);
        await expect(page).toHaveURL(/historico-de-acesso/);

        // Volta ao Controle
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });

        // 3. Logs de Modificação
        const detailsMod = page.locator('details:has(summary:has-text("LOGS DE MODIFICAÇÃO DE RMA"))');
        await detailsMod.locator('summary').click();
        const linkMod = detailsMod.locator('a:has-text("ABRIR LOGS DE MODIFICAÇÃO")');
        await expect(linkMod).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkMod.click(),
        ]);
        await expect(page).toHaveURL(/rmas-historico/);

        // Volta ao Controle
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });

        // 4. Cadastrar Novo Usuário
        const detailsNovoUsr = page.locator('details:has(summary:has-text("CADASTRAR NOVO USUÁRIO"))');
        await detailsNovoUsr.locator('summary').click();
        const linkNovoUsr = detailsNovoUsr.locator('a:has-text("ABRIR FORMULÁRIO DE NOVO USUÁRIO")');
        await expect(linkNovoUsr).toBeVisible();
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            linkNovoUsr.click(),
        ]);
        await expect(page).toHaveURL(/usuarios\/novo/);
    });

    test('Cenário 3: Interações funcionais de Adicionar Representante e Arquivar sem inline JS', async ({ page }) => {
        await loginAdminV1(page);
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });

        // 1. Adicionar Representante único (Nome + Tipo + Adicionar)
        const detailsRep = page.locator('details:has(summary:has-text("ADICIONAR REPRESENTANTE"))');
        await detailsRep.locator('summary').click();

        const formRep = detailsRep.locator('form');
        await expect(formRep).toHaveAttribute('action', /rmas-controle\/representante/);
        await formRep.locator('input[name="nome"]').fill('Fornecedor Teste Playwright');
        await formRep.locator('select[name="tipo"]').selectOption('fornecedor');

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formRep.locator('button[type="submit"]').click(),
        ]);

        // Valida flash de sucesso e permanência do painel aberto
        await expect(page.locator('.centrodeavisos')).toContainText('Fornecedor cadastrado com sucesso.');
        await expect(detailsRep).toHaveAttribute('open', '');

        // 2. Arquivar RMA sem inline JS (submete número inválido e valida erro amigável)
        const detailsArq = page.locator('details:has(summary:has-text("ARQUIVAR UMA SOLICITACAO DE RMA"))');
        await detailsArq.locator('summary').click();

        const formArq = detailsArq.locator('form');
        await expect(formArq).toHaveAttribute('action', /rmas-controle\/arquivar/);
        // Garante ausência de inline JS no form
        expect(await formArq.getAttribute('onsubmit')).toBeNull();

        await formArq.locator('input[name="numero"]').fill('888888');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            formArq.locator('button[type="submit"]').click(),
        ]);

        // Valida mensagem de erro no padrão V1 e permanência do painel aberto
        await expect(page.locator('.centrodeavisos--erro')).toContainText('RMA 888888 não encontrado.');
        await expect(detailsArq).toHaveAttribute('open', '');
    });

    test('Cenário 4: Fidelidade visual, alinhamento e supressão de documentação interna', async ({ page }) => {
        await loginAdminV1(page);
        await page.goto(`${V3}/rmas-controle`, { waitUntil: 'domcontentloaded' });

        // A. Supressão de documentação interna
        await expect(page.locator('body')).not.toContainText('VIS-V1-011');
        await expect(page.locator('body')).not.toContainText('VIS-V1-012');
        await expect(page.locator('body')).not.toContainText('docs/produto');

        // B. Painéis de exclusão exibem mensagem discreta
        const detailsDelRma = page.locator('details:has(summary:has-text("DELETAR UMA SOLICITACAO DE RMA"))');
        await detailsDelRma.locator('summary').click();
        await expect(detailsDelRma).toContainText('Operação indisponível nesta versão.');

        const detailsDelUsr = page.locator('details:has(summary:has-text("DELETAR UM USUARIO"))');
        await detailsDelUsr.locator('summary').click();
        await expect(detailsDelUsr).toContainText('Operação indisponível nesta versão.');

        // C. Mudar Senha com alinhamento das 3 linhas
        const detailsSenha = page.locator('details:has(summary:has-text("MUDAR SENHA"))');
        await detailsSenha.locator('summary').click();
        await expect(detailsSenha.locator('input[name="senha_atual"]')).toBeVisible();
        await expect(detailsSenha.locator('input[name="nova_senha"]')).toBeVisible();
        await expect(detailsSenha.locator('input[name="nova_senha_confirmation"]')).toBeVisible();

        // Medir alinhamento no eixo X dos inputs de senha
        const box1 = await detailsSenha.locator('input[name="senha_atual"]').boundingBox();
        const box2 = await detailsSenha.locator('input[name="nova_senha"]').boundingBox();
        const box3 = await detailsSenha.locator('input[name="nova_senha_confirmation"]').boundingBox();

        expect(box1).not.toBeNull();
        expect(box2).not.toBeNull();
        expect(box3).not.toBeNull();
        expect(box1!.x).toBe(box2!.x);
        expect(box2!.x).toBe(box3!.x);
    });
});
