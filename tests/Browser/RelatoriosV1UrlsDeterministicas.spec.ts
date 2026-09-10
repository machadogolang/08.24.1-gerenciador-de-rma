import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * ADENDO P0/AD-10/AD-14 - URLs de QA deterministas dos relatorios fiscais do TEMA
 * V1: `/v1/relatorios/{rcd,rpec,rmpe}`.
 *
 * Prova, pelo browser, que:
 * - as tres rotas respondem 200 no runtime real;
 * - `ResolverTemaAtivo` forca o shell V1 (`#TOPO`) mesmo com usuario cuja
 *   preferencia persistida e V1/V2 (aqui o usuario de QA entra com a preferencia
 *   que estiver salva - o ponto e que NAO ha troca de tema por causa da URL);
 * - a folha historica do V1 (titulo do relatorio) e o que aparece;
 * - `/v2/relatorios` continua sendo o painel estatistico do 15.8.1 e NAO vira o
 *   relatorio V1.
 */
const BASE = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };

test.setTimeout(120_000);

async function loginQa(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test('AD-10/AD-14 - /v1/relatorios/{rcd,rpec,rmpe} abrem no shell V1', async ({ browser }) => {
    const page = await loginQa(browser);

    const casos: Array<[string, string]> = [
        ['/v1/relatorios/rcd', 'RCD - RELATORIO DE CREDITOS DISPONIVEIS'],
        ['/v1/relatorios/rpec', 'RPEC - RELACAO DOS PRODUTOS EM ESTOQUE PARA CONTAGEM'],
        ['/v1/relatorios/rmpe', 'RMPE - RELACAO DOS PRODUTOS ENCAMINHADOS PELO RMA'],
    ];

    for (const [url, titulo] of casos) {
        const resposta = await page.goto(`${BASE}${url}`, { waitUntil: 'domcontentloaded' });

        expect(resposta?.status(), `status de ${url}`).toBe(200);
        await expect(page.locator('#TOPO'), `shell V1 em ${url}`).toHaveCount(1);
        await expect(page.locator('.header-v2'), `sem shell V2 em ${url}`).toHaveCount(0);
        await expect(page.locator('h1', { hasText: titulo }), `titulo historico em ${url}`).toBeVisible();
        // O rodape do relatorio tem o <h3> e o botao com o mesmo texto; o
        // locator precisa ser especifico para nao casar os dois.
        await expect(page.locator('h3', { hasText: 'INFORMACAO ADICIONAL' })).toBeVisible();
    }
});

test('AD-12/AD-14 - /v2/relatorios continua o painel estatistico do 15.8.1', async ({ browser }) => {
    const page = await loginQa(browser);

    const resposta = await page.goto(`${BASE}/v2/relatorios`, { waitUntil: 'domcontentloaded' });

    expect(resposta?.status()).toBe(200);
    await expect(page.locator('.header-v2')).toHaveCount(1);
    await expect(page.locator('#TOPO')).toHaveCount(0);
    await expect(page.locator('text=Situacao')).toBeVisible();
    await expect(page.locator('text=RCD - RELATORIO DE CREDITOS DISPONIVEIS')).toHaveCount(0);
});
