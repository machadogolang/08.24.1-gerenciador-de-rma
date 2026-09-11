import { test, expect } from '@playwright/test';

test.describe('CapabilityNavigationRma - Jornada Real de RMA Operacional (V1 e V2)', () => {
    test('Cenário A (V1): Topo fixo, painéis inline (Novo/Localizar) e abas de status', async ({ page }) => {
        // 1. Login supervisor (V1)
        await page.goto('/login');
        await page.fill('#email', 'supervisor@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await page.goto('/rmas');
        await expect(page.locator('#FIXADO')).toBeVisible();

        // 2. Testar clique em "Novo" no topo fixo (aciona JS-Novo inline)
        const btnTopoNovo = page.locator('#menu-novo a, #menu-novo');
        await btnTopoNovo.first().click();
        await expect(page.locator('#JS-Novo')).toBeVisible();
        await expect(page.locator('#JS-Novo input[name="descricao"]')).toBeVisible();

        // 3. Testar clique em "Localizar" no topo fixo (aciona JS-Localizar inline)
        const btnTopoLocalizar = page.locator('#menu-localizar a, #menu-localizar');
        await btnTopoLocalizar.first().click();
        await expect(page.locator('#JS-Localizar')).toBeVisible();
        await expect(page.locator('#JS-Localizar input[name="valor"]')).toBeVisible();

        // 4. Navegar pelas 5 abas de status do topo fixo
        const abasStatusV1 = [
            { texto: 'Entrada', urlEsperada: '/rmas-entrada' },
            { texto: 'Recebido', urlEsperada: '/rmas-recebidos' },
            { texto: 'Encaminhado', urlEsperada: '/rmas-encaminhados' },
            { texto: 'Aguardando credito', urlEsperada: '/rmas-aguardando-credito' },
            { texto: 'Concluido!', urlEsperada: '/rmas-concluidos' },
        ];

        for (const aba of abasStatusV1) {
            const linkAba = page.locator('#TOPO ul li a', { hasText: aba.texto });
            await expect(linkAba).toBeVisible();
            await linkAba.click();
            await expect(page).toHaveURL(new RegExp(aba.urlEsperada));
            await expect(page.locator('#FIXADO')).toBeVisible();
            await expect(page.locator('#MEIO')).toBeVisible();
        }
    });

    test('Cenário B (V2): Navbar com abas nav-tabs (Novo, Pesquisar, Status)', async ({ page }) => {
        // 1. Login superadministrador (V2)
        await page.goto('/login');
        await page.fill('#email', 'superadministrador@rma.local');
        await page.fill('#password', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'));

        await page.goto('/v2/rma');
        await expect(page.locator('ul.nav-v2')).toBeVisible();

        // 2. Clicar na aba "Novo" e verificar painel #novo_rma
        const tabNovo = page.locator('ul.nav-v2 a', { hasText: 'Novo' });
        await expect(tabNovo).toBeVisible();
        await tabNovo.click();
        await expect(page.locator('#novo_rma')).toBeVisible();
        await expect(page.locator('#novo_rma input[name="descricao"]')).toBeVisible();

        // 3. Clicar na aba "Pesquisar" e verificar painel #pesquisar
        const tabPesquisar = page.locator('ul.nav-v2 a', { hasText: 'Pesquisar' });
        await expect(tabPesquisar).toBeVisible();
        await tabPesquisar.click();
        await expect(page.locator('#pesquisar')).toBeVisible();

        // 4. Navegar pelas abas de status
        const abasStatus = ['Entrada', 'Recebido', 'Encaminhado', 'Concluido'];
        for (const aba of abasStatus) {
            const linkAba = page.locator('ul.nav-v2 a', { hasText: aba });
            await expect(linkAba).toBeVisible();
            await linkAba.click();
            const idPainel = aba.toLowerCase();
            await expect(page.locator(`#${idPainel}`)).toBeVisible();
        }
    });
});
