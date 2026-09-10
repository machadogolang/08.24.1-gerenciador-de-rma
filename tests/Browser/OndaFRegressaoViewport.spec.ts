import { test, expect, type Page } from '@playwright/test';

/**
 * ONDA F - viewport / overflow / print / regressao residual (V1 e V2 em serie).
 *
 * Fecha UI-09.10 (C7 + UI-08) e UI-05.4 no que depende de varredura de viewport:
 * cursor/controles ja foram provados por `ConsistenciaVisualControles` e o overflow
 * local do Controle V1 pelo UI-05; aqui a prova e a varredura de larguras, a
 * integridade do shell/rodape e a impressao dos relatorios.
 *
 * TEMA V2 tem shell de largura FIXA (1190px, contrato medido no Legacy 2048 e usado
 * pelo media.php) - abaixo disso o Legacy 15.8.1 tambem rola na horizontal (base
 * 1004px). Por isso "sem overflow" e asserido do desktop em diante; nas larguras
 * estreitas validamos que o shell se mantem inteiro e navegavel.
 */
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';

const DESKTOP = [1366, 1440, 1600, 1920];
const V2_ESTREITAS = [568, 768, 800, 992, 1080, 1280];

const ROTAS_V1 = [
    ['/v1/rma', '#BASE'],
    ['/v1/parceiros/clientes', '#BASE'],
    ['/v1/parceiros/fornecedores', '#BASE'],
    ['/v1/usuarios', '#BASE'],
    ['/v1/perfil', '#BASE'],
] as const;

const ROTAS_V2 = [
    ['/v2/rma', '.shell-v2'],
    ['/v2/parceiros/clientes', '.shell-v2'],
    ['/v2/parceiros/fornecedores', '.shell-v2'],
    ['/v2/usuarios', '.shell-v2'],
    ['/v2/perfil', '.shell-v2'],
    ['/v2/anotacoes', '.shell-v2'],
    ['/v2/perfil/senha', '.shell-v2'],
] as const;

const ROTAS_COMUNS = [
    '/rmas-entrada',
    '/rmas-concluidos',
    '/rmas-aguardando-credito',
    '/rmas-credito',
    '/rmas-relatorios/rcd',
    '/rmas-relatorios/rpec',
    '/rmas-relatorios/rmpe',
    '/rmas-controle',
    '/rmas-historico',
    '/historico-de-acesso',
    '/rmas-alertas',
] as const;

test.setTimeout(300_000);

async function login(page: Page): Promise<void> {
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
}

const overflow = (page: Page) => page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);

test('ONDA F - V1, V2 e telas comuns sem overflow horizontal no desktop', async ({ page }) => {
    await login(page);

    for (const largura of DESKTOP) {
        await page.setViewportSize({ width: largura, height: 900 });

        for (const [rota, seletor] of [...ROTAS_V1, ...ROTAS_V2]) {
            const resposta = await page.goto(`${V3}${rota}`, { waitUntil: 'domcontentloaded' });
            expect(resposta?.status(), `${rota} @${largura}`).toBeLessThan(400);
            await expect(page.locator(seletor), `${seletor} @${rota}`).toBeVisible();
            expect(await overflow(page), `overflow ${rota} @${largura}`).toBeLessThanOrEqual(2);
        }

        for (const rota of ROTAS_COMUNS) {
            await page.goto(`${V3}${rota}`, { waitUntil: 'domcontentloaded' });
            expect(await overflow(page), `overflow ${rota} @${largura}`).toBeLessThanOrEqual(2);
        }
    }
});

test('ONDA F - V2 mantem shell/rodape inteiros e navegaveis nas larguras estreitas', async ({ page }) => {
    await login(page);

    for (const largura of V2_ESTREITAS) {
        await page.setViewportSize({ width: largura, height: 900 });
        await page.goto(`${V3}/v2/rma`, { waitUntil: 'domcontentloaded' });

        await expect(page.locator('.header-v2')).toBeVisible();
        const shell = await page.locator('.shell-v2').boundingBox();
        expect(shell?.width, `shell @${largura}`).toBeGreaterThanOrEqual(980);
        await expect(page.locator('.shell-v2__sidebar')).toBeVisible();
        await expect(page.locator('p.designedby')).toHaveCount(2);

        // Sem overflow inesperado: o excedente e o do shell fixo (1190 - viewport).
        const excedente = await overflow(page);
        if (largura < 1190) {
            expect(Math.abs(excedente - (1190 - largura)), `shell fixo @${largura}`).toBeLessThanOrEqual(40);
        } else {
            expect(excedente).toBeLessThanOrEqual(2);
        }

        // Dropdown continua abrindo. Use dispatchEvent: nas larguras estreitas o
        // toggle fica fora da viewport (shell fixo) e o clique por coordenada nao
        // alcanca - o mecanismo Bootstrap continua o mesmo.
        const toggle = page.locator('.nav-v2 .dropdown-toggle');
        await expect(toggle).toHaveCount(1);
        await toggle.dispatchEvent('click');
        await expect(page.locator('.nav-v2 .dropdown-menu')).toBeVisible();
        await page.keyboard.press('Escape');
    }
});

test('ONDA F - relatorios imprimem a folha e escondem a moldura do shell', async ({ page }) => {
    await login(page);

    for (const rota of ['/rmas-relatorios/rcd', '/rmas-relatorios/rpec', '/rmas-relatorios/rmpe'] as const) {
        await page.goto(`${V3}${rota}`, { waitUntil: 'domcontentloaded' });
        await expect(page.locator('body')).toHaveClass(/relatorio-print/);
        await expect(page.locator('.relatorio-titulo')).toBeVisible();

        await page.emulateMedia({ media: 'print' });
        // PAR-RES-F-01 - o conteudo do relatorio vive dentro de `.shell-v2`; a
        // impressao limpa mantem a folha visivel e esconde header/sidebar/rodape.
        await expect(page.locator('.relatorio')).toBeVisible();
        await expect(page.locator('.header-v2')).toBeHidden();
        await expect(page.locator('.shell-v2__sidebar')).toBeHidden();
        await expect(page.locator('p.designedby').first()).toBeHidden();
        await page.emulateMedia({ media: 'screen' });
    }
});
