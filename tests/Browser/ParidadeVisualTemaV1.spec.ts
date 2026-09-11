import { test, expect, type Browser, type Page } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { join } from 'node:path';

const DESTINO = join(process.cwd(), 'docs/produto/screenshots-paridade-v1');
const LEGACY = process.env.LEGACY_BASE_URL ?? (process.env.CI ? 'http://host.docker.internal:8094/14.6.1/' : 'http://localhost:8094/14.6.1/');
const V3 = process.env.PLAYWRIGHT_BASE_URL ?? (process.env.CI ? 'http://localhost' : 'http://localhost:8095');
const VIEWPORT = { width: 1440, height: 1000 };

test.setTimeout(180_000);

type Falha = { tipo: string; url: string; status?: number; erro?: string };

function vigiarRecursos(page: Page, falhas: Falha[]): void {
    page.on('response', response => {
        if (response.status() >= 400) {
            falhas.push({ tipo: 'http', url: response.url(), status: response.status() });
        }
    });
    page.on('requestfailed', request => {
        falhas.push({ tipo: 'requestfailed', url: request.url(), erro: request.failure()?.errorText });
    });
}

async function fontesRasterizadas(page: Page, seletor: string) {
    const sessao = await page.context().newCDPSession(page);
    await sessao.send('DOM.enable');
    await sessao.send('CSS.enable');
    const { root } = await sessao.send('DOM.getDocument');
    const { nodeId } = await sessao.send('DOM.querySelector', {
        nodeId: root.nodeId,
        selector: seletor,
    });
    const { fonts } = await sessao.send('CSS.getPlatformFontsForNode', { nodeId });
    await sessao.detach();

    return fonts;
}

function usaOpenSansLocal(fontes: Awaited<ReturnType<typeof fontesRasterizadas>>): boolean {
    return fontes.some(fonte => fonte.familyName === 'Open Sans' && fonte.isCustomFont);
}

async function loginLegacy(browser: Browser): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    await page.goto(LEGACY, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    return page;
}

async function loginV3(browser: Browser, falhas: Falha[] = []): Promise<Page> {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();
    vigiarRecursos(page, falhas);
    await page.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    return page;
}

test.beforeAll(() => mkdirSync(DESTINO, { recursive: true }));

test('captura os gateways de login para registrar a decisão de paridade', async ({ browser }) => {
    const context = await browser.newContext({ viewport: VIEWPORT });
    const legacy = await context.newPage();
    const v3 = await context.newPage();
    await legacy.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());

    await legacy.goto(LEGACY, { waitUntil: 'domcontentloaded' });
    await legacy.screenshot({ path: join(DESTINO, 'legacy-login-1440.png'), fullPage: true });
    await v3.goto(`${V3}/login`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-login-1440.png'), fullPage: true });

    await context.close();
});

test('TEMA V1 carrega estrutura fixa, menu histórico, cascata e assets locais sem erro', async ({ browser }) => {
    const falhas: Falha[] = [];
    const page = await loginV3(browser, falhas);
    await page.goto(`${V3}/v1/usuarios`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);

    expect(await page.locator('#BASE').evaluate(el => getComputedStyle(el).width)).toBe('984px');
    expect(await page.locator('.menu-up').count()).toBe(8);
    expect(await page.locator('.menu-up').first().evaluate(el => getComputedStyle(el).float)).toBe('left');
    expect(await page.locator('.menuDivSession').evaluate(el => getComputedStyle(el).width)).toBe('982px');
    expect(await page.locator('.JS-SessaoLEFT').evaluate(el => getComputedStyle(el).width)).toBe('838px');
    expect(await page.locator('.JS-SessaoRIGHT').evaluate(el => getComputedStyle(el).width)).toBe('144px');
    expect((await page.evaluate(() => document.fonts.load('400 12px "Fira Mono"'))).length).toBeGreaterThan(0);
    expect((await page.evaluate(() => document.fonts.load('400 12px "Open Sans"'))).length).toBeGreaterThan(0);

    await page.goto(`${V3}/rmas-concluidos`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    const familia = async (seletor: string) => page.locator(seletor).first()
        .evaluate(el => getComputedStyle(el).fontFamily);

    expect(await familia('body')).toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await familia('.menu-up')).toBe('"Open Sans", Arial, Roboto');
    expect(await familia('#CONTEUDO .title-comicone')).toBe('"Open Sans", Arial, Roboto');
    expect(await familia('.TableListarFPEF-TR th')).toBe('"Open Sans", Arial, Roboto');
    expect(await familia('.Tabelinha-TD')).toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await familia('.Tabelinha-TD:last-child')).toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await familia('#RODAPE')).toBe('"Open Sans", Arial, Roboto');

    const concluido = page.locator('.menu-up').filter({ hasText: 'Concluido!' });
    await expect(concluido).toHaveClass(/active/);
    expect(await concluido.evaluate(el => getComputedStyle(el).fontWeight)).toBe('700');
    expect(await concluido.evaluate(el => getComputedStyle(el).color)).toBe('rgb(255, 215, 0)');

    // `fontFamily` acima prova a cascata declarada; CDP prova a fonte que efetivamente
    // rasterizou os glifos. Isso evita o falso positivo de uma pilha "Open Sans" que
    // caiu silenciosamente em Arial por arquivo inválido ou falha de rede.
    expect(usaOpenSansLocal(await fontesRasterizadas(page, '.menu-up'))).toBe(true);
    expect(usaOpenSansLocal(await fontesRasterizadas(page, '#CONTEUDO .title-comicone'))).toBe(true);
    expect(usaOpenSansLocal(await fontesRasterizadas(page, '.TableListarFPEF-TR th'))).toBe(true);

    const essenciais = falhas.filter(falha =>
        /\/build\/|\/images\/tema-v1\//.test(falha.url)
    );
    expect(essenciais).toEqual([]);
    await page.context().close();
});

test('TEMA V1 preserva as primitivas históricas das tabelas altas e compactas', async ({ browser }) => {
    const page = await loginV3(browser);
    await page.goto(`${V3}/rmas-concluidos`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => {
        document.querySelector('#CONTEUDO')?.insertAdjacentHTML('beforeend', `
            <table id="fixture-primitivas-v1" class="Tabelinha-Table">
                <tbody>
                    <tr class="TableListarFPEF-TR"><th>HEADER</th></tr>
                    <tr class="Tabelinha-TR1"><td class="Tabelinha-TD"><a href="#">TR1</a></td></tr>
                    <tr class="Tabelinha-TR2"><td class="Tabelinha-TD">TR2</td></tr>
                    <tr class="Tabelinha-TR3"><td class="Tabelinha-TD">TR3</td></tr>
                    <tr class="TrZebrada1"><td>COMPACTA 1</td></tr>
                    <tr class="TrZebrada2"><td>COMPACTA 2</td></tr>
                    <tr class="TrInconformidade"><td>INCONFORMIDADE</td></tr>
                    <tr class="TrUrgente"><td>URGENTE</td></tr>
                </tbody>
            </table>
        `);
    });

    const tabela = page.locator('#fixture-primitivas-v1');
    const header = tabela.locator('.TableListarFPEF-TR');
    const td = tabela.locator('.Tabelinha-TD').first();

    expect(await tabela.evaluate(el => getComputedStyle(el).backgroundColor)).toBe('rgb(54, 51, 51)');
    expect(await tabela.evaluate(el => getComputedStyle(el).width)).toBe('984px');
    expect(await header.evaluate(el => getComputedStyle(el).height)).toBe('35px');
    expect(await header.evaluate(el => getComputedStyle(el).backgroundColor)).toBe('rgb(42, 42, 42)');
    expect(await header.evaluate(el => getComputedStyle(el).color)).toBe('rgb(248, 193, 139)');
    expect(Math.round((await header.boundingBox())!.height)).toBe(35);

    expect(await td.evaluate(el => getComputedStyle(el).fontSize)).toBe('11px');
    expect(await td.evaluate(el => getComputedStyle(el).letterSpacing)).toBe('1px');
    expect(await td.evaluate(el => getComputedStyle(el).textTransform)).toBe('uppercase');
    expect(await td.locator('a').evaluate(el => getComputedStyle(el).display)).toBe('block');

    for (const [classe, cor] of [
        ['Tabelinha-TR1', 'rgb(63, 63, 63)'],
        ['Tabelinha-TR2', 'rgb(59, 59, 62)'],
        ['Tabelinha-TR3', 'rgb(35, 35, 32)'],
    ] as const) {
        const linha = tabela.locator(`.${classe}`);
        expect(await linha.evaluate(el => getComputedStyle(el).height)).toBe('30px');
        expect(await linha.evaluate(el => getComputedStyle(el).backgroundColor)).toBe(cor);
        expect(Math.round((await linha.boundingBox())!.height)).toBe(30);
    }

    for (const classe of ['TrZebrada1', 'TrZebrada2', 'TrInconformidade', 'TrUrgente']) {
        const linha = tabela.locator(`.${classe}`);
        expect(await linha.locator('td').evaluate(el => getComputedStyle(el).height)).toBe('18px');
        expect(Math.round((await linha.boundingBox())!.height)).toBe(21);
    }

    await page.context().close();
});

test('Mostrar do protocolo abre tabela compacta com a familia Arial do legado', async ({ browser }) => {
    const page = await loginV3(browser);
    const legacy = await loginLegacy(browser);
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    await legacy.locator('#pmostrar_pabertonaoencaminhado').click();

    const grupo = page.locator('[data-alerta-tipo="protocolo-aberto-nao-encaminhado"]');
    await expect(grupo.locator('.regra-de-alerta-dados')).toBeHidden();
    await grupo.locator('.pmo').click();
    await expect(grupo.locator('.pmo')).toHaveText('Ocultar');
    await expect(grupo.locator('.pmo')).toHaveAttribute('aria-expanded', 'true');

    const tabela = grupo.locator('.tabela-alerta-abertos-nao-encaminhados');
    await expect(tabela).toBeVisible();
    await expect(tabela.locator('thead th')).toHaveCount(11);
    await expect(tabela.locator('tbody tr')).not.toHaveCount(0);
    expect(await tabela.locator('.SuperTr').evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await tabela.locator('tbody td').first().evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    const alturaLinhaLegacy = Math.round((await legacy.locator(
        '#dados_pabertonaoencaminhado tr:not(.SuperTr)',
    ).first().boundingBox())!.height);
    const alturaLinhaV3 = Math.round((await tabela.locator('tbody tr').first().boundingBox())!.height);
    expect(Math.abs(alturaLinhaV3 - alturaLinhaLegacy)).toBeLessThanOrEqual(1);
    await expect(tabela.locator('img[title="Ver"]').first()).toBeVisible();

    await legacy.context().close();
    await page.context().close();
});

test('Mostrar da prioridade abre a tabela ENTRADA compacta do legado', async ({ browser }) => {
    const page = await loginV3(browser);
    const legacy = await loginLegacy(browser);
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    await legacy.locator('#pmostrar_prioridadealta').click();

    const grupo = page.locator('[data-alerta-tipo="prioridade-alta-sem-encaminhar"]');
    await grupo.locator('.pmo').click();
    await expect(grupo.locator('.pmo')).toHaveText('Ocultar');

    const tabela = grupo.locator('.tabela-alerta-abertos-nao-encaminhados');
    await expect(tabela).toBeVisible();
    await expect(tabela.locator('thead th')).toHaveCount(11);
    await expect(tabela.locator('thead th').first()).toHaveText('ENTRADA');
    await expect(tabela.locator('tbody tr')).not.toHaveCount(0);
    expect(await tabela.locator('.SuperTr').evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await tabela.locator('tbody td').first().evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');

    const alturaV3 = Math.round((await tabela.locator('tbody tr').first().boundingBox())!.height);
    const linhasLegacy = legacy.locator('#dados_prioridadealta tr:not(.SuperTr)');
    if (await linhasLegacy.count() > 0) {
        const alturaLegacy = Math.round((await linhasLegacy.first().boundingBox())!.height);
        expect(Math.abs(alturaV3 - alturaLegacy)).toBeLessThanOrEqual(1);
    } else {
        // O banco Legacy do laboratório pode legitimamente não ter prioridade alta.
        // Nesse caso registramos o empty-state ao vivo e provamos a estrutura pelo
        // arquivo-fonte + partial compartilhado já medido no grupo de protocolo.
        await expect(legacy.locator('#dados_prioridadealta')).toContainText('Nenhum item foi encontrado');
        expect(alturaV3).toBeGreaterThanOrEqual(30);
        expect(alturaV3).toBeLessThanOrEqual(31);
    }
    await expect(tabela.locator('img[title="Ver"]').first()).toBeVisible();

    await legacy.context().close();
    await page.context().close();
});

test('Mostrar de sem S/N abre a tabela RECEBIDO compacta do legado', async ({ browser }) => {
    const page = await loginV3(browser);
    const legacy = await loginLegacy(browser);
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    await legacy.locator('#pmostrar_semsn').click();

    const grupo = page.locator('[data-alerta-tipo="sem-numero-de-serie"]');
    await grupo.locator('.pmo').click();
    await expect(grupo.locator('.pmo')).toHaveText('Ocultar');

    const tabela = grupo.locator('.tabela-alerta-abertos-nao-encaminhados');
    await expect(tabela).toBeVisible();
    await expect(tabela.locator('thead th')).toHaveCount(11);
    await expect(tabela.locator('thead th').first()).toHaveText('RECEBIDO');
    await expect(tabela.locator('tbody tr')).not.toHaveCount(0);
    expect(await tabela.locator('.SuperTr').evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await tabela.locator('tbody td').first().evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');

    const alturaV3 = Math.round((await tabela.locator('tbody tr').first().boundingBox())!.height);
    const linhasLegacy = legacy.locator('#dados_semsn tr:not(.SuperTr)');
    if (await linhasLegacy.count() > 0) {
        const alturaLegacy = Math.round((await linhasLegacy.first().boundingBox())!.height);
        expect(Math.abs(alturaV3 - alturaLegacy)).toBeLessThanOrEqual(1);
    } else {
        await expect(legacy.locator('#dados_semsn')).toContainText('Nenhum item foi encontrado');
        expect(alturaV3).toBeGreaterThanOrEqual(30);
        expect(alturaV3).toBeLessThanOrEqual(31);
    }
    await expect(tabela.locator('img[title="Ver"]').first()).toBeVisible();

    await legacy.context().close();
    await page.context().close();
});

test('Mostrar de sem nota fiscal abre a tabela RECEBIDO compacta de 10 colunas do legado', async ({ browser }) => {
    const page = await loginV3(browser);
    const legacy = await loginLegacy(browser);
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    await legacy.locator('#pmostrar_semnota').click();

    const grupo = page.locator('[data-alerta-tipo="sem-nota-fiscal"]');
    await grupo.locator('.pmo').click();
    await expect(grupo.locator('.pmo')).toHaveText('Ocultar');

    const tabela = grupo.locator('.tabela-alerta-sem-nota');
    await expect(tabela).toBeVisible();
    await expect(tabela.locator('thead th')).toHaveCount(10);
    await expect(tabela.locator('thead th').first()).toHaveText('RECEBIDO');
    await expect(tabela.locator('tbody tr')).not.toHaveCount(0);
    expect(await tabela.locator('.SuperTr').evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await tabela.locator('tbody td').first().evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');

    const alturaV3 = Math.round((await tabela.locator('tbody tr').first().boundingBox())!.height);
    expect(alturaV3).toBeGreaterThanOrEqual(30);
    expect(alturaV3).toBeLessThanOrEqual(31);

    const linhasLegacy = legacy.locator('#dados_semnota tr:not(.SuperTr)');
    if (await linhasLegacy.count() > 0) {
        // As linhas normais sem quebra de texto medem 30-31px no Legacy.
        const alturasLegacy = await linhasLegacy.evaluateAll(trs =>
            trs.map(tr => Math.round(tr.getBoundingClientRect().height))
        );
        const alturaBaseLegacy = Math.min(...alturasLegacy);
        expect(Math.abs(alturaV3 - alturaBaseLegacy)).toBeLessThanOrEqual(1);
    } else {
        await expect(legacy.locator('#dados_semnota')).toContainText('Nenhum item foi encontrado');
    }
    await expect(tabela.locator('img[title="Ver"]').first()).toBeVisible();

    await legacy.context().close();
    await page.context().close();
});

test('Mostrar de prazo do destinatário estourado abre a tabela ENCAMINHADO compacta de 10 colunas do legado', async ({ browser }) => {
    const page = await loginV3(browser);
    const legacy = await loginLegacy(browser);
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    await legacy.locator('#pmostrar_prazodestinatario').click();

    const grupo = page.locator('[data-alerta-tipo="prazo-destinatario-estourado"]');
    await grupo.locator('.pmo').click();
    await expect(grupo.locator('.pmo')).toHaveText('Ocultar');

    const tabela = grupo.locator('.tabela-alerta-prazo-destinatario');
    await expect(tabela).toBeVisible();
    await expect(tabela.locator('thead th')).toHaveCount(10);
    await expect(tabela.locator('thead th').first()).toHaveText('ENCAMINHADO');
    await expect(tabela.locator('tbody tr')).not.toHaveCount(0);
    expect(await tabela.locator('.SuperTr').evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await tabela.locator('tbody td').first().evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');

    const alturaV3 = Math.round((await tabela.locator('tbody tr').first().boundingBox())!.height);
    expect(alturaV3).toBeGreaterThanOrEqual(30);
    expect(alturaV3).toBeLessThanOrEqual(31);

    const linhasLegacy = legacy.locator('#dados_prazodestinatario tr:not(.SuperTr)');
    if (await linhasLegacy.count() > 0) {
        const alturasLegacy = await linhasLegacy.evaluateAll(trs =>
            trs.map(tr => Math.round(tr.getBoundingClientRect().height))
        );
        const alturaBaseLegacy = Math.min(...alturasLegacy);
        expect(Math.abs(alturaV3 - alturaBaseLegacy)).toBeLessThanOrEqual(1);
    } else {
        await expect(legacy.locator('#dados_prazodestinatario')).toContainText('Nenhum item foi encontrado');
    }
    await expect(tabela.locator('img[title="Ver"]').first()).toBeVisible();

    await legacy.context().close();
    await page.context().close();
});

test('Mostrar de recebidos a mais de 30 dias abre a tabela RECEBIDO compacta de 10 colunas do legado', async ({ browser }) => {
    const page = await loginV3(browser);
    const legacy = await loginLegacy(browser);
    await page.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);
    await legacy.locator('#pmostrar_naoencaminhadoprazoestourado').click();

    const grupo = page.locator('[data-alerta-tipo="recebidos-sem-encaminhar-30-dias"]');
    await grupo.locator('.pmo').click();
    await expect(grupo.locator('.pmo')).toHaveText('Ocultar');

    const tabela = grupo.locator('.tabela-alerta-sem-nota');
    await expect(tabela).toBeVisible();
    await expect(tabela.locator('thead th')).toHaveCount(10);
    await expect(tabela.locator('thead th').first()).toHaveText('RECEBIDO');
    await expect(tabela.locator('tbody tr')).not.toHaveCount(0);
    expect(await tabela.locator('.SuperTr').evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');
    expect(await tabela.locator('tbody td').first().evaluate(el => getComputedStyle(el).fontFamily))
        .toBe('Arial, "Open Sans", "Fira Mono"');

    const alturaV3 = Math.round((await tabela.locator('tbody tr').first().boundingBox())!.height);
    expect(alturaV3).toBeGreaterThanOrEqual(30);
    expect(alturaV3).toBeLessThanOrEqual(31);

    const linhasLegacy = legacy.locator('#dados_naoencaminhadoprazoestourado tr:not(.SuperTr)');
    if (await linhasLegacy.count() > 0) {
        const alturasLegacy = await linhasLegacy.evaluateAll(trs =>
            trs.map(tr => Math.round(tr.getBoundingClientRect().height))
        );
        const alturaBaseLegacy = Math.min(...alturasLegacy);
        expect(Math.abs(alturaV3 - alturaBaseLegacy)).toBeLessThanOrEqual(1);
    } else {
        await expect(legacy.locator('#dados_naoencaminhadoprazoestourado')).toContainText('Nenhum item foi encontrado');
    }
    await expect(tabela.locator('img[title="Ver"]').first()).toBeVisible();

    await legacy.context().close();
    await page.context().close();
});

test('Centro de Avisos recolhido preserva o passo vertical histórico entre grupos', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    const v3 = await loginV3(browser);
    await v3.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await legacy.evaluate(() => document.fonts.ready);
    await v3.evaluate(() => document.fonts.ready);

    const posicoesLegacy = await legacy.locator('.pmo').evaluateAll(elementos => elementos
        .filter(elemento => getComputedStyle(elemento).display !== 'none')
        .map(elemento => elemento.getBoundingClientRect().y));
    const posicoesV3 = await v3.locator('.regra-de-alerta .pmo').evaluateAll(elementos => elementos
        .map(elemento => elemento.getBoundingClientRect().y));

    expect(posicoesV3).toHaveLength(10);
    expect(posicoesLegacy).toHaveLength(10);

    const passos = (posicoes: number[]) => posicoes.slice(1).map((posicao, indice) =>
        Math.round(posicao - posicoes[indice])
    );

    // include histórico (50px) + `separador.png` (40px), em todas as regras.
    // A rasterização da fonte no Legacy varia 1–3px entre contextos Chromium; o
    // contrato é passo uniforme e diferença máxima de 3px por grupo, não o valor
    // acidental de uma execução isolada.
    const passosLegacy = passos(posicoesLegacy);
    const passosV3 = passos(posicoesV3);
    expect(new Set(passosLegacy).size).toBe(1);
    expect(new Set(passosV3).size).toBe(1);
    passosV3.forEach((passo, indice) => {
        expect(Math.abs(passo - passosLegacy[indice])).toBeLessThanOrEqual(3);
    });

    await legacy.context().close();
    await v3.context().close();
});

test('rodapé preserva a altura de linha histórica', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    const v3 = await loginV3(browser);
    await v3.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await legacy.evaluate(() => document.fonts.ready);
    await v3.evaluate(() => document.fonts.ready);

    const medir = async (page: Page, seletor: string) => page.locator(seletor).first().evaluate(elemento => {
        const retangulo = elemento.getBoundingClientRect();
        return { height: Math.round(retangulo.height) };
    });

    expect(await medir(legacy, '#RODAPE .p-rodape:not([style*="display:none"])'))
        .toEqual(await medir(v3, '#RODAPE .p-rodape'));
    expect(await medir(legacy, '#RODAPE .designedby:first-of-type'))
        .toEqual(await medir(v3, '#RODAPE .designedby:first-of-type'));
    expect(await medir(legacy, '#RODAPE')).toEqual(await medir(v3, '#RODAPE'));

    await legacy.context().close();
    await v3.context().close();
});

test('captura matriz comparável Legacy V1 e V3 em 1440px', async ({ browser }) => {
    const legacy = await loginLegacy(browser);
    const v3 = await loginV3(browser);

    await legacy.screenshot({ path: join(DESTINO, 'legacy-home-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-home-1440.png'), fullPage: true });

    await legacy.click('#menu-sessao');
    const paineis = [
        ['usuarios', '#menu-usuarios', '#JS-Usuarios', '/v1/usuarios'],
        ['clientes', '#menu-clientes', '#JS-Clientes', '/v1/parceiros/clientes'],
        ['fabricantes', '#menu-fabricantes', '#JS-Fabricantes', '/v1/parceiros/fabricantes'],
        ['fornecedores', '#menu-fornecedores', '#JS-Fornecedores', '/v1/parceiros/fornecedores'],
        ['assistencias', '#menu-assistencia_tecnicas', '#JS-Assistencia_tecnicas', '/v1/parceiros/assistencias-tecnicas'],
    ] as const;

    for (const [nome, botao, painel, rota] of paineis) {
        await legacy.click(botao);
        await expect(legacy.locator(painel)).toBeVisible();
        await legacy.screenshot({ path: join(DESTINO, `legacy-${nome}-1440.png`), fullPage: true });
        await v3.goto(`${V3}${rota}`, { waitUntil: 'domcontentloaded' });
        await v3.screenshot({ path: join(DESTINO, `v3-${nome}-1440.png`), fullPage: true });
    }

    await legacy.goto(`${LEGACY}index.php?page=entrada`, { waitUntil: 'domcontentloaded' });
    await legacy.screenshot({ path: join(DESTINO, 'legacy-rma-listagem-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma?tipo=texto&valor=QA`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-rma-listagem-1440.png'), fullPage: true });

    const detalheLegacy = await legacy.locator('a[href*="page=detalhes"]').first().getAttribute('href');
    expect(detalheLegacy).not.toBeNull();
    await legacy.goto(new URL(detalheLegacy!, legacy.url()).href, { waitUntil: 'domcontentloaded' });
    await legacy.screenshot({ path: join(DESTINO, 'legacy-rma-detalhe-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma/1`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-rma-detalhe-1440.png'), fullPage: true });

    await legacy.goto(LEGACY, { waitUntil: 'domcontentloaded' });
    await legacy.click('#menu-novo');
    await expect(legacy.locator('#JS-Novo')).toBeVisible();
    await legacy.screenshot({ path: join(DESTINO, 'legacy-rma-novo-1440.png'), fullPage: true });
    await v3.goto(`${V3}/v1/rma/create`, { waitUntil: 'domcontentloaded' });
    await v3.screenshot({ path: join(DESTINO, 'v3-rma-novo-1440.png'), fullPage: true });

    await legacy.context().close();
    await v3.context().close();
});
