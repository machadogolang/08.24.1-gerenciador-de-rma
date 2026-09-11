import { test, expect } from '@playwright/test';

test.describe('CapabilityNavigationParceiros - Jornada Real de Parceiros (V1 e V2)', () => {
    test('Cenário A (V1): Navegação e descoberta dos 4 tipos de parceiros via menu lateral', async ({ page }) => {
        // 1. Login como supervisor (preferência V1)
        await page.goto('/login');
        await page.fill('#email', 'supervisor@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await page.goto('/rmas');
        await page.click('#menu-sessao');
        await expect(page.locator('#JS-Sessao')).toBeVisible();

        const parceiros = [
            { texto: 'Fornecedores', urlParte: '/parceiros/fornecedores' },
            { texto: 'Fabricantes', urlParte: '/parceiros/fabricantes' },
            { texto: 'Assistências', urlParte: '/parceiros/assistencias-tecnicas' },
            { texto: 'Clientes', urlParte: '/parceiros/clientes' },
        ];

        for (const p of parceiros) {
            const link = page.locator('#JS-Sessao a.lisessao', { hasText: p.texto });
            await expect(link).toBeVisible();
            await link.click();
            await expect(page).toHaveURL(new RegExp(p.urlParte));
            await expect(page.locator('#JS-Sessao')).toBeVisible();
            await expect(page.locator('.JS-DivLEFT')).toBeVisible();
        }
    });

    test('Cenário B (V1): Ciclo de vida de parceiro - Criar, Visualizar Detalhe e Editar', async ({ page }) => {
        // 1. Login
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        // 2. Acessar clientes V1 e clicar Novo
        await page.goto('/v1/parceiros/clientes');
        const btnNovo = page.locator('a.acao--primaria', { hasText: 'Novo' });
        await expect(btnNovo).toBeVisible();
        await btnNovo.click();

        await expect(page).toHaveURL(/\/v1\/parceiros\/clientes\/create/);
        const nomeCliente = `Cliente Teste Playwright V1 ${Date.now()}`;
        await page.fill('input[name="nome"]', nomeCliente);
        await page.fill('input[name="cpf_cnpj"]', '11.222.333/0001-44');
        await page.fill('input[name="email"]', 'teste.v1@empresa.com');

        // 3. Submeter formulário dentro do conteúdo da página
        await page.locator('.JS-DivLEFT button.buttonSave, .JS-DivLEFT button[type="submit"]').click();
        await expect(page).toHaveURL(/\/v1\/parceiros\/clientes/);
        await expect(page.locator(`text=${nomeCliente}`)).toBeVisible();

        // 4. Acessar Detalhe do parceiro criado
        const linhaParceiro = page.locator('tr', { hasText: nomeCliente });
        const linkVer = linhaParceiro.locator('a', { hasText: 'Ver' });
        await linkVer.click();

        await expect(page).toHaveURL(/\/v1\/parceiros\/clientes\/\d+/);
        await expect(page.locator('.detalhe-parceiro-v1')).toBeVisible();
        await expect(page.locator('.detalhe-parceiro-v1').getByText(nomeCliente)).toBeVisible();
        await expect(page.locator('a.acao--primaria', { hasText: 'EDITAR' })).toBeVisible();

        // 5. Clicar Editar
        await page.click('a.acao--primaria:has-text("EDITAR")');
        await expect(page).toHaveURL(/\/v1\/parceiros\/clientes\/\d+\/edit/);
        await expect(page.locator('input[name="nome"]')).toHaveValue(nomeCliente);
    });

    test('Cenário C (V2): Navegação aos 4 tipos de parceiros via dropdown Menu da navbar', async ({ page }) => {
        // 1. Login
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await page.goto('/v2/rma');

        const parceirosV2 = [
            { texto: 'Clientes', urlParte: '/v2/parceiros/clientes' },
            { texto: 'Fornecedores', urlParte: '/v2/parceiros/fornecedores' },
            { texto: 'Fabricantes', urlParte: '/v2/parceiros/fabricantes' },
            { texto: 'Assistencias', urlParte: '/v2/parceiros/assistencias-tecnicas' },
        ];

        for (const p of parceirosV2) {
            const menuDropdown = page.locator('li.dropdown > a.dropdown-toggle', { hasText: 'Menu' });
            await menuDropdown.click();

            const link = page.locator('ul.dropdown-menu a', { hasText: p.texto });
            await expect(link).toBeVisible();
            await link.click();

            await expect(page).toHaveURL(new RegExp(p.urlParte));
            await expect(page.locator('.Tabelinha-Table')).toBeVisible();
        }
    });

    test('Cenário D (V2): Ciclo de vida de parceiro - Cadastro com layout Novo e Edição com RG/IE', async ({ page }) => {
        // 1. Login
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        // 2. Ir para lista de clientes V2 e clicar em "Novo"
        await page.goto('/v2/parceiros/clientes');
        const btnNovo = page.locator('a.acao--primaria', { hasText: 'Novo' });
        await expect(btnNovo).toBeVisible();
        await btnNovo.click();

        await expect(page).toHaveURL(/\/v2\/parceiros\/clientes\/create/);
        await expect(page.locator('text=Quem voce quer cadastrar?')).toBeVisible();
        await expect(page.locator('button[type="submit"]', { hasText: 'Cadastrar' })).toBeVisible();

        const nomeCliente = `Cliente Teste Playwright V2 ${Date.now()}`;
        await page.fill('input[name="nome"]', nomeCliente);
        await page.fill('input[name="cpf_cnpj"]', '99.888.777/0001-66');

        await page.click('button[type="submit"]:has-text("Cadastrar")');
        await expect(page).toHaveURL(/\/v2\/parceiros\/clientes/);
        await expect(page.locator(`text=${nomeCliente}`)).toBeVisible();

        // 3. Abrir Detalhe V2
        const linhaParceiro = page.locator('tr', { hasText: nomeCliente });
        const linkVer = linhaParceiro.locator('a', { hasText: 'Ver' });
        await linkVer.click();

        await expect(page).toHaveURL(/\/v2\/parceiros\/clientes\/\d+/);
        await expect(page.locator('.detalhe-parceiro-v2')).toBeVisible();
        await expect(page.locator('.detalhe-parceiro-v2').getByText(nomeCliente).first()).toBeVisible();
        await expect(page.locator('text=RMAs associados')).toBeVisible();

        // 4. Clicar em Editar e preencher RG/IE (PAR15-PART-002..005)
        const btnEditar = page.locator('a', { hasText: 'Editar' });
        await btnEditar.click();

        await expect(page).toHaveURL(/\/v2\/parceiros\/clientes\/\d+\/edit/);
        await expect(page.locator('input[name="rgie"]')).toBeVisible();
        await page.fill('input[name="rgie"]', 'ISENTO-V2-1234');
        await page.click('button[type="submit"]:has-text("Salvar")');

        await expect(page).toHaveURL(/\/v2\/parceiros\/clientes/);
    });
});
