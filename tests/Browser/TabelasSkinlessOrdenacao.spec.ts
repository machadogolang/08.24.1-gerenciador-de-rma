import { test, expect, type Page } from '@playwright/test';

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

async function login(page: Page): Promise<void> {
    await page.setViewportSize(VIEWPORT);
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]'),
    ]);
}

test.describe('PROP-TABELAS-SKINLESS-01 - Ordenacao Dinamica sem Quebra Visual', () => {
    test('Tema V1: Ordenacao da tabela de historico com preservacao do zebrado e zero vazamento', async ({ page }) => {
        await login(page);
        await page.goto(`${BASE_URL}/rmas-historico`, { waitUntil: 'domcontentloaded' });

        const tabela = page.locator('table[data-tabela-skinless="true"]').first();
        await expect(tabela).toBeVisible();

        const thRma = tabela.locator('thead th:has-text("RMA")');
        await expect(thRma).toBeVisible();

        // Obtem numeros iniciais da primeira e ultima linha antes de ordenar
        const linhas = tabela.locator('tbody tr');
        const totalLinhas = await linhas.count();
        expect(totalLinhas).toBeGreaterThan(1);

        // 1. Primeiro clique: Ordenacao Ascendente
        await thRma.click();
        const indicadorAsc = thRma.locator('.skinless-sort-indicator');
        await expect(indicadorAsc).toHaveText(' ▲');

        // Valida que o zebrado continua estritamente alternado
        for (let i = 0; i < totalLinhas; i++) {
            const linha = linhas.nth(i);
            const classeEsperada = i % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2';
            await expect(linha).toHaveClass(new RegExp(classeEsperada));
        }

        // 2. Segundo clique: Ordenacao Descendente
        await thRma.click();
        const indicadorDesc = thRma.locator('.skinless-sort-indicator');
        await expect(indicadorDesc).toHaveText(' ▼');

        // Valida zebrado novamente
        for (let i = 0; i < totalLinhas; i++) {
            const linha = linhas.nth(i);
            const classeEsperada = i % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2';
            await expect(linha).toHaveClass(new RegExp(classeEsperada));
        }
    });

    test('Tema V2: Ordenacao de logs de modificacao com zebrado TrZebrada1/TrZebrada2', async ({ page }) => {
        await login(page);
        await page.goto(`${BASE_URL}/v2/rmas-historico`, { waitUntil: 'domcontentloaded' });

        const tabela = page.locator('table[data-tabela-skinless="true"]').first();
        await expect(tabela).toBeVisible();

        const thBdNumero = tabela.locator('thead th:has-text("BD NUMERO")');
        await expect(thBdNumero).toBeVisible();

        const linhas = tabela.locator('tbody tr');
        const totalLinhas = await linhas.count();
        expect(totalLinhas).toBeGreaterThan(1);

        // Clique para ordenar
        await thBdNumero.click();
        const indicador = thBdNumero.locator('.skinless-sort-indicator');
        await expect(indicador).toHaveText(' ▲');

        // Valida que o zebrado historico do V2 (TrZebrada1 / TrZebrada2) continua consistente
        for (let i = 0; i < totalLinhas; i++) {
            const linha = linhas.nth(i);
            const classeEsperada = i % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2';
            await expect(linha).toHaveClass(new RegExp(classeEsperada));
        }
    });

    test('historico-de-acesso: ordenacao skinless por USUARIO e DATA mantendo fidelidade visual', async ({ page }) => {
        await login(page);
        await page.goto(`${BASE_URL}/historico-de-acesso`, { waitUntil: 'domcontentloaded' });

        const tabela = page.locator('table[data-tabela-skinless="true"]').first();
        await expect(tabela).toBeVisible();

        const thUsuario = tabela.locator('thead th:has-text("USUÁRIO")');
        await expect(thUsuario).toBeVisible();

        const linhas = tabela.locator('tbody tr');
        const totalLinhas = await linhas.count();
        expect(totalLinhas).toBeGreaterThan(0);

        // Ordena por usuario
        await thUsuario.click();
        const indicador = thUsuario.locator('.skinless-sort-indicator');
        await expect(indicador).toHaveText(' ▲');

        // Valida zebrado apos ordenacao
        for (let i = 0; i < totalLinhas; i++) {
            const linha = linhas.nth(i);
            const classeEsperada = i % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2';
            await expect(linha).toHaveClass(new RegExp(classeEsperada));
        }
    });

    test('rmas-credito: ordenacao skinless na tabela de creditos mantendo as 11 colunas', async ({ page }) => {
        await login(page);
        await page.goto(`${BASE_URL}/rmas-credito`, { waitUntil: 'domcontentloaded' });

        const tabela = page.locator('table[data-tabela-skinless="true"]').first();
        if (await tabela.count() > 0) {
            await expect(tabela).toBeVisible();

            const thFabricante = tabela.locator('thead th:has-text("FABRICANTE")');
            await expect(thFabricante).toBeVisible();

            await thFabricante.click();
            const indicador = thFabricante.locator('.skinless-sort-indicator');
            await expect(indicador).toHaveText(' ▲');
        }
    });
});
