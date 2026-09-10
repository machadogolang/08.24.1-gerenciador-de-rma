import { test, expect, type Page } from '@playwright/test';

/**
 * UX-003/P7 - encaminhamento por selecao validada.
 *
 * O detalhe V1 deixa de pedir "tipo + id cru" e passa a mostrar UMA selecao
 * `destinatario` com `value="tipo:id"`, populada so com entidades do tenant ativo.
 * O servidor revalida (Feature `EncaminharRmaTest`); aqui e a prova browser de que a
 * superficie mudou e de que o encaminhamento funciona.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 900 };
const DETAILS = '.detalhe-bd-acoes-avancadas > summary';
const SELETOR_ENCAMINHAR = 'form[action$="/encaminhar"]';
const SELETOR_CONCLUIR = 'form[action$="/concluir"]';

test.setTimeout(120_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

test('UX-003 - encaminhar no V1 usa selecao validada de destinatario', async ({ page }) => {
    await page.setViewportSize(VIEWPORT);
    await login(page);

    // Cria um RMA descartavel no painel inline do V1.
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.click('#menu-novo');
    await page.fill('#JS-Novo input[name="descricao"]', `ENCAMINHAR UX-003 ${Date.now()}`);
    await page.fill('#JS-Novo input[name="defeito"]', 'Falha de teste UX-003');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('#JS-Novo button.formButtonEnviarNovo'),
    ]);

    // As acoes de ciclo de vida ficam no bloco recolhivel do detalhe V1.
    await page.click(DETAILS);
    await expect(page.locator('.acoes-de-transicao')).toBeVisible();

    // Entrada -> Recebido (habilita o Encaminhar).
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('form[action$="/receber"] button'),
    ]);

    // O reload fecha o bloco recolhivel de novo.
    await page.click(DETAILS);

    const formEncaminhar = page.locator(SELETOR_ENCAMINHAR);
    await expect(formEncaminhar).toBeVisible();

    // Superficie nova: select validado, sem id cru.
    const select = formEncaminhar.locator('select[name="destinatario"]');
    await expect(select).toBeVisible();
    await expect(formEncaminhar.locator('input[name="destinatario_id"]')).toHaveCount(0);
    await expect(formEncaminhar.locator('select[name="destinatario_tipo"]')).toHaveCount(0);

    const valores = await select.locator('option').evaluateAll((opcoes) =>
        opcoes.map((opcao) => (opcao as HTMLOptionElement).value).filter((valor) => valor !== ''),
    );
    expect(valores.length).toBeGreaterThan(0);
    for (const valor of valores) {
        expect(valor).toMatch(/^(assistencia_tecnica|fabricante|fornecedor):\d+$/);
    }

    await select.selectOption({ index: 1 });
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        formEncaminhar.locator('button[type=submit]').click(),
    ]);

    await expect(page.locator('.centrodeavisos')).toContainText('RMA encaminhado.');

    // Prova da transicao: Encaminhar some e Concluir aparece (o status do detalhe
    // V1 vive em controle, nao em texto de tabela).
    await page.click(DETAILS);
    await expect(page.locator(SELETOR_ENCAMINHAR)).toHaveCount(0);
    await expect(page.locator(SELETOR_CONCLUIR)).toHaveCount(1);
});
