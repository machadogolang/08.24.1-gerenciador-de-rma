import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

/**
 * CP15 — gerador reexecutável da matriz final do Tema V1 fase 2.
 *
 * Gera duas camadas:
 * - raw em screenshots-paridade-v1/ (gitignorado, pode conter dado Legacy real);
 * - sanitizada em screenshots-evidencias-v1-fase2/ (segura para versionar).
 *
 * Uso:
 *   node scripts/qa/paridade-v1-fase2.mjs
 * Bases podem ser sobrescritas por LEGACY_BASE_URL/PLAYWRIGHT_BASE_URL.
 */
const raiz = process.cwd();
const legadoBase = process.env.LEGACY_BASE_URL ?? 'http://localhost:8094/14.6.1/';
const v3Base = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const destinoRaw = join(raiz, 'docs/produto/screenshots-paridade-v1');
const destinoSanitizado = join(raiz, 'docs/produto/screenshots-evidencias-v1-fase2');
const destinoMedidas = join(raiz, 'docs/produto/evidencias-v1-fase2/cp15-medidas.json');
mkdirSync(destinoRaw, { recursive: true });
mkdirSync(destinoSanitizado, { recursive: true });

const viewports = [
    { width: 1440, height: 1000 },
    { width: 1562, height: 1400 },
    { width: 1700, height: 1000 },
];

const gruposExpandidos = [
    {
        chave: 'protocoloExpandido',
        arquivo: 'protocolo-expandido',
        mostrarLegacy: '#pmostrar_pabertonaoencaminhado',
        ocultarLegacy: '#pocultar_pabertonaoencaminhado',
        dadosLegacy: '#dados_pabertonaoencaminhado',
        grupoV3: '[data-alerta-tipo="protocolo-aberto-nao-encaminhado"]',
    },
    {
        chave: 'prioridadeExpandida',
        arquivo: 'prioridade-expandida',
        mostrarLegacy: '#pmostrar_prioridadealta',
        ocultarLegacy: '#pocultar_prioridadealta',
        dadosLegacy: '#dados_prioridadealta',
        grupoV3: '[data-alerta-tipo="prioridade-alta-sem-encaminhar"]',
    },
];

const elementos = [
    ['header', '#TOPO', '#TOPO'],
    ['base', '#BASE', '#BASE'],
    ['localizar', '#JS-Localizar', '#JS-Localizar'],
    ['select-localizar', '.JSformLocalizarSelect', '.JSformLocalizarSelect'],
    ['titulo-anotacao', '.panotacao', '.panotacao'],
    ['textarea-anotacao', '.textareaanotacao', '.textareaanotacao'],
    ['rotulo-contador', '.formLabelStats', '.formLabelStats'],
    ['valor-contador', '.formInputStats', '.formValorStats'],
    ['separador-principal', 'img[title="Separador"]', 'img[title="Separador"]'],
    ['titulo-centro-avisos', '.centrodeavisos h5', '.centro-de-avisos-titulo'],
    ['rodape', '#RODAPE', '#RODAPE'],
];

async function medir(page, seletor) {
    const alvo = page.locator(seletor).first();
    if (await alvo.count() === 0) return null;

    return alvo.evaluate(el => {
        const retangulo = el.getBoundingClientRect();
        const estilo = getComputedStyle(el);
        const arredondar = numero => Math.round(numero * 100) / 100;

        return {
            x: arredondar(retangulo.x),
            y: arredondar(retangulo.y),
            width: arredondar(retangulo.width),
            height: arredondar(retangulo.height),
            display: estilo.display,
            fontFamily: estilo.fontFamily,
            fontSize: estilo.fontSize,
            fontWeight: estilo.fontWeight,
            lineHeight: estilo.lineHeight,
            backgroundColor: estilo.backgroundColor,
            color: estilo.color,
        };
    });
}

async function entrarLegacy(browser, viewport) {
    const context = await browser.newContext({ viewport, deviceScaleFactor: 1 });
    const page = await context.newPage();
    await page.route(/fonts\.(googleapis|gstatic)\.com/, route => route.abort());
    await page.goto(legadoBase, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name=email]', 'lab@localhost');
    await page.fill('input[name=senha]', 'rma-lab-2026');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('[name=signin]'),
    ]);
    await page.evaluate(() => document.fonts.ready);

    return page;
}

async function entrarV3(browser, viewport) {
    const context = await browser.newContext({ viewport, deviceScaleFactor: 1 });
    const page = await context.newPage();
    await page.goto(`${v3Base}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type=submit]'),
    ]);
    await page.goto(`${v3Base}/v1/rma`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => document.fonts.ready);

    return page;
}

async function sanitizar(page, seletorTabela = null) {
    await page.evaluate(seletor => {
        const anotacao = document.querySelector('#anotacao');
        if (anotacao instanceof HTMLTextAreaElement) anotacao.value = 'EVIDENCIA SANITIZADA';

        document.querySelectorAll('.formInputStats').forEach(elemento => {
            if (elemento instanceof HTMLInputElement) elemento.value = '0';
        });
        document.querySelectorAll('.formValorStats').forEach(elemento => {
            elemento.textContent = '0';
        });

        const usuario = document.querySelector('#RODAPE .p-rodape');
        if (usuario) usuario.innerHTML = '<strong>Usuário:</strong> EVIDENCIA SANITIZADA';

        // Evidência estrutural versionável: preserva exatamente uma linha e a
        // geometria percentual da tabela, mas nunca leva dados reais do Legacy ao
        // Git. A captura raw continua disponível apenas no diretório ignorado.
        const tabela = seletor === null ? null : document.querySelector(seletor);
        if (tabela instanceof HTMLTableElement) {
            const linhas = [...tabela.querySelectorAll('tbody tr, tr')]
                .filter(linha => !linha.classList.contains('SuperTr'));
            linhas.slice(1).forEach(linha => linha.remove());

            const valores = [
                '01/01/2026', '1', 'LOJA', '100', '200', 'FORNECEDOR QA',
                'FABRICANTE QA', 'PRODUTO QA', 'MODELO QA', '5901',
            ];
            linhas[0]?.querySelectorAll('td').forEach((celula, indice) => {
                if (indice < valores.length) celula.textContent = valores[indice];
            });
        }
    }, seletorTabela);
}

async function capturarGrupoExpandido(legacy, v3, grupo) {
    const tabelaLegacy = `${grupo.dadosLegacy} .Tabelinha-Table`;
    const tabelaV3 = `${grupo.grupoV3} .tabela-alerta-abertos-nao-encaminhados`;
    const grupoV3 = v3.locator(grupo.grupoV3);

    await legacy.locator(grupo.mostrarLegacy).click();
    await grupoV3.locator('.pmo').click();
    await legacy.locator(grupo.dadosLegacy).waitFor({ state: 'visible' });
    await v3.locator(tabelaV3).waitFor({ state: 'visible' });

    await legacy.screenshot({ path: join(destinoRaw, `legacy-cp15-${grupo.arquivo}-1440x1000.png`), fullPage: true });
    await v3.screenshot({ path: join(destinoRaw, `v3-cp15-${grupo.arquivo}-1440x1000.png`), fullPage: true });

    const medidas = {
        tabela: {
            legacy: await medir(legacy, tabelaLegacy),
            v3: await medir(v3, tabelaV3),
        },
        cabecalho: {
            legacy: await medir(legacy, `${grupo.dadosLegacy} .SuperTr`),
            v3: await medir(v3, `${tabelaV3} .SuperTr`),
        },
        primeiraCelula: {
            legacy: await medir(legacy, `${grupo.dadosLegacy} tbody td, ${grupo.dadosLegacy} tr:not(.SuperTr) td`),
            v3: await medir(v3, `${tabelaV3} tbody td`),
        },
    };

    await sanitizar(legacy, tabelaLegacy);
    await sanitizar(v3, tabelaV3);
    await legacy.screenshot({ path: join(destinoSanitizado, `legacy-cp15-${grupo.arquivo}-1440x1000.png`), fullPage: true });
    await v3.screenshot({ path: join(destinoSanitizado, `v3-cp15-${grupo.arquivo}-1440x1000.png`), fullPage: true });

    await legacy.locator(grupo.ocultarLegacy).click();
    await grupoV3.locator('.pmo').click();

    return medidas;
}

const browser = await chromium.launch();
const resultado = [];

for (const viewport of viewports) {
    const legacy = await entrarLegacy(browser, viewport);
    const v3 = await entrarV3(browser, viewport);
    const sufixo = `${viewport.width}x${viewport.height}`;

    await legacy.screenshot({ path: join(destinoRaw, `legacy-cp15-final-home-${sufixo}.png`), fullPage: true });
    await v3.screenshot({ path: join(destinoRaw, `v3-cp15-final-home-${sufixo}.png`), fullPage: true });

    const medidas = {};
    for (const [nome, seletorLegacy, seletorV3] of elementos) {
        medidas[nome] = {
            legacy: await medir(legacy, seletorLegacy),
            v3: await medir(v3, seletorV3),
        };
    }


    const medidasDosGrupos = Object.fromEntries(gruposExpandidos.map(grupo => [grupo.chave, null]));
    if (viewport.width === 1440) {
        for (const grupo of gruposExpandidos) {
            medidasDosGrupos[grupo.chave] = await capturarGrupoExpandido(legacy, v3, grupo);
        }
    }

    // Os cliques de captura usam scrollIntoView. Voltar ao topo evita que o estado
    // de scroll altere a rasterização do header fixed na evidência base da Home.
    await legacy.evaluate(() => scrollTo(0, 0));
    await v3.evaluate(() => scrollTo(0, 0));
    await sanitizar(legacy);
    await sanitizar(v3);
    await legacy.screenshot({ path: join(destinoSanitizado, `legacy-cp15-home-${sufixo}.png`), fullPage: true });
    await v3.screenshot({ path: join(destinoSanitizado, `v3-cp15-home-${sufixo}.png`), fullPage: true });

    resultado.push({
        viewport,
        dpr: {
            legacy: await legacy.evaluate(() => devicePixelRatio),
            v3: await v3.evaluate(() => devicePixelRatio),
        },
        medidas,
        ...medidasDosGrupos,
    });

    await legacy.context().close();
    await v3.context().close();
}

await browser.close();
writeFileSync(destinoMedidas, `${JSON.stringify(resultado, null, 2)}\n`);
console.log(`Evidências geradas em ${destinoSanitizado}`);
console.log(`Medidas geradas em ${destinoMedidas}`);
