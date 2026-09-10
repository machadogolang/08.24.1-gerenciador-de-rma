import { test, expect, type Browser, type Page } from '@playwright/test';

/**
 * PAR-RES-006 - Centro de Avisos do TEMA V2 comparado ao Legacy 15.8.1
 * (`page/inicio.php` + `subp/listar_*.php`).
 *
 * A auditoria residual registrava "lista generica compartilhada"; o runtime mostra
 * que cada grupo ja tinha cabecalho, "Mostrar" e tabela propria, na ordem historica.
 * O que faltava era paridade de espacamento no V2 (o `line-height:20px` global e o
 * reset `td,th{padding:0}` do tema davam passo 96px e linhas de 26px). Este spec
 * fixa titulos, tabelas por grupo, passo recolhido e altura de linha contra o Legacy.
 */
const LEGACY_V2 = process.env.LEGACY_V2_BASE_URL ?? 'http://localhost:8094/15.8.1/';
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(180_000);

async function loginLegacy(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, (rota) => rota.abort());
    await page.goto(LEGACY_V2, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    return page;
}

async function loginV3(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });
    return page;
}

const normalizar = (texto: string) => texto.replace(/:$/, '').trim().toUpperCase();

test('PAR-RES-006 - Centro de Avisos V2 repete os 10 grupos historicos do 15.8.1, na ordem', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    const v3 = await loginV3(browser);

    const titulosLegacy = (await legacy.locator('.submenutitulo li:nth-child(2)').allInnerTexts()).map(normalizar);
    const titulosV3 = (await v3.locator('.regra-de-alerta .regra-de-alerta-titulo').allInnerTexts()).map(normalizar);

    expect(titulosLegacy).toHaveLength(10);
    expect(titulosV3).toEqual(titulosLegacy);

    await legacy.context().close();
    await v3.context().close();
});

test('PAR-RES-006 - cada grupo V2 abre tabela propria (nao lista generica)', async ({ browser }) => {
    const v3 = await loginV3(browser);

    const grupos = v3.locator('.regra-de-alerta');
    await expect(grupos).toHaveCount(10);

    for (let indice = 0; indice < 10; indice++) {
        const grupo = grupos.nth(indice);
        await expect(grupo.locator('.regra-de-alerta-titulo')).not.toHaveText('');

        const gatilho = grupo.locator('.pmo');
        await expect(gatilho).toHaveText('Mostrar');
        await gatilho.click();
        await expect(gatilho).toHaveText('Ocultar');
        await expect(grupo.locator('.regra-de-alerta-dados')).toBeVisible();

        const vazio = grupo.locator('.nenhumencontrado');
        if (await vazio.count() > 0) {
            await expect(vazio).toContainText('Nenhum item foi encontrado');
        } else {
            await expect(grupo.locator('.regra-de-alerta-dados table.Tabelinha-Table')).toHaveCount(1);
            await expect(grupo.locator('.regra-de-alerta-dados .SuperTr')).toHaveCount(1);
        }

        await gatilho.click();
        await expect(gatilho).toHaveText('Mostrar');
    }

    await v3.context().close();
});

test('PAR-RES-006 - passo recolhido e altura de linha acompanham o Legacy 15.8.1', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    const v3 = await loginV3(browser);
    await legacy.evaluate(() => document.fonts.ready);
    await v3.evaluate(() => document.fonts.ready);

    const posicoesLegacy = await legacy.locator('.submenutitulo').evaluateAll((elementos) =>
        elementos.map((elemento) => Math.round(elemento.getBoundingClientRect().y)),
    );
    const posicoesV3 = await v3.locator('.regra-de-alerta').evaluateAll((elementos) =>
        elementos.map((elemento) => Math.round(elemento.getBoundingClientRect().y)),
    );

    expect(posicoesLegacy).toHaveLength(10);
    expect(posicoesV3).toHaveLength(10);

    const passos = (posicoes: number[]) => posicoes.slice(1).map((posicao, indice) => posicao - posicoes[indice]);
    const passoLegacy = passos(posicoesLegacy);
    const passoV3 = passos(posicoesV3);

    expect(new Set(passoLegacy).size).toBe(1);
    passoV3.forEach((passo, indice) => {
        expect(Math.abs(passo - passoLegacy[indice])).toBeLessThanOrEqual(3);
    });

    // Linha expandida da tabela de avisos: o reset `td,th{padding:0}` e o
    // `img{vertical-align:middle}` do Bootstrap deixavam o V2 em 26-28px contra os
    // 30px do 15.8.1.
    await legacy.locator('#pmostrar_pabertonaoencaminhado').click();
    await v3.locator('[data-alerta-tipo="protocolo-aberto-nao-encaminhado"] .pmo').click();

    const linhasLegacy = legacy.locator('#dados_pabertonaoencaminhado tr:not(.SuperTr)');
    if (await linhasLegacy.count() > 0) {
        const alturaLegacy = Math.round((await linhasLegacy.first().boundingBox())!.height);
        const alturaV3 = Math.round((await v3.locator('[data-alerta-tipo="protocolo-aberto-nao-encaminhado"] tbody tr').first().boundingBox())!.height);
        expect(Math.abs(alturaV3 - alturaLegacy)).toBeLessThanOrEqual(2);
    }

    await legacy.context().close();
    await v3.context().close();
});
