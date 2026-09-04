import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

/**
 * Auditoria navegacional e visual integral do Tema V1 — Lotes NAV-00 e NAV-01.
 *
 * Gera duas camadas:
 * - raw em screenshots-paridade-v1/ (gitignorado, pode conter dado real);
 * - sanitizada em screenshots-auditoria-v1/ (segura para versionar).
 * - manifesto em evidencias-auditoria-v1/manifesto-navegacional-v1.json.
 *
 * Uso:
 *   node scripts/qa/auditoria-navegacional-v1.mjs
 */
const raiz = process.cwd();
const legadoBase = process.env.LEGACY_BASE_URL ?? 'http://localhost:8094/14.6.1/';
const v3Base = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8095';
const destinoRaw = join(raiz, 'docs/produto/screenshots-paridade-v1');
const destinoSanitizado = join(raiz, 'docs/produto/screenshots-auditoria-v1');
const destinoEvidencias = join(raiz, 'docs/produto/evidencias-auditoria-v1');
const destinoManifesto = join(destinoEvidencias, 'manifesto-navegacional-v1.json');

mkdirSync(destinoRaw, { recursive: true });
mkdirSync(destinoSanitizado, { recursive: true });
mkdirSync(destinoEvidencias, { recursive: true });

const VIEWPORT = { width: 1440, height: 1000 };

async function entrarLegacy(browser) {
    const context = await browser.newContext({ viewport: VIEWPORT, deviceScaleFactor: 1 });
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

async function entrarV3(browser) {
    const context = await browser.newContext({ viewport: VIEWPORT, deviceScaleFactor: 1 });
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

async function sanitizar(page) {
    await page.evaluate(() => {
        const usuario = document.querySelector('#RODAPE .p-rodape');
        if (usuario) usuario.innerHTML = '<strong>Usuário:</strong> EVIDENCIA SANITIZADA';

        const anotacao = document.querySelector('#anotacao');
        if (anotacao instanceof HTMLTextAreaElement) anotacao.value = 'EVIDENCIA SANITIZADA';

        document.querySelectorAll('.formInputStats').forEach(el => {
            if (el instanceof HTMLInputElement) el.value = '0';
        });
        document.querySelectorAll('.formValorStats').forEach(el => {
            el.textContent = '0';
        });

        // Limpa dados de tabelas preservando layout/cabeçalhos
        document.querySelectorAll('table.Tabelinha-Table').forEach(tabela => {
            const linhas = [...tabela.querySelectorAll('tbody tr, tr')]
                .filter(linha => !linha.classList.contains('SuperTr') && !linha.querySelector('th'));
            linhas.slice(1).forEach(linha => linha.remove());
            if (linhas[0]) {
                const celulas = linhas[0].querySelectorAll('td');
                const valores = ['01/01/2026', '1', 'ORIGEM QA', 'NF100', 'NF200', 'FORNECEDOR QA', 'FABRICANTE QA', 'PRODUTO QA', 'MODELO QA', 'OS10', 'A'];
                celulas.forEach((td, i) => {
                    if (i < valores.length && td.textContent.trim() !== 'Ver' && td.textContent.trim() !== 'Editar') {
                        td.textContent = valores[i];
                    }
                });
            }
        });
    });
}

async function medirElemento(page, seletor) {
    const el = page.locator(seletor).first();
    if (await el.count() === 0) return null;
    return el.evaluate(node => {
        const r = node.getBoundingClientRect();
        const cs = getComputedStyle(node);
        return {
            x: Math.round(r.x * 100) / 100,
            y: Math.round(r.y * 100) / 100,
            width: Math.round(r.width * 100) / 100,
            height: Math.round(r.height * 100) / 100,
            display: cs.display,
            visibility: cs.visibility,
        };
    });
}

async function auditarAlvo(legPage, v3Page, alvo) {
    const falhasLeg = [];
    const falhasV3 = [];
    const onRespLeg = resp => { if (resp.status() >= 400) falhasLeg.push({ status: resp.status(), url: resp.url() }); };
    const onRespV3 = resp => { if (resp.status() >= 400) falhasV3.push({ status: resp.status(), url: resp.url() }); };

    legPage.on('response', onRespLeg);
    v3Page.on('response', onRespV3);

    let statusLeg = 200;
    let statusV3 = 200;

    if (alvo.tipo === 'navegacao') {
        if (alvo.acaoLeg) {
            const resp = await alvo.acaoLeg(legPage);
            if (resp) statusLeg = resp.status();
        }
        if (alvo.acaoV3) {
            const resp = await alvo.acaoV3(v3Page);
            if (resp) statusV3 = resp.status();
        }
    } else if (alvo.tipo === 'inline') {
        if (alvo.acaoLeg) await alvo.acaoLeg(legPage);
        if (alvo.acaoV3) await alvo.acaoV3(v3Page);
    }

    await legPage.evaluate(() => document.fonts.ready);
    await v3Page.evaluate(() => document.fonts.ready);

    const linkAtivoLeg = await legPage.locator('.menu-up.active, .formButtonMENU.active, #inicio.active').textContent().catch(() => null);
    const linkAtivoV3 = await v3Page.locator('.menu-up.active, .formButtonMENU.active').textContent().catch(() => null);

    const infoLeg = {
        url: legPage.url(),
        titulo: await legPage.title(),
        status: statusLeg,
        linkAtivo: linkAtivoLeg ? linkAtivoLeg.trim() : null,
        falhas: [...falhasLeg],
        medidas: {
            topo: await medirElemento(legPage, '#TOPO'),
            conteudo: await medirElemento(legPage, '#CONTEUDO'),
            rodape: await medirElemento(legPage, '#RODAPE'),
            painel: alvo.painel ? await medirElemento(legPage, alvo.painel) : null,
        }
    };

    const infoV3 = {
        url: v3Page.url(),
        titulo: await v3Page.title(),
        status: statusV3,
        linkAtivo: linkAtivoV3 ? linkAtivoV3.trim() : null,
        falhas: [...falhasV3],
        medidas: {
            topo: await medirElemento(v3Page, '#TOPO'),
            conteudo: await medirElemento(v3Page, '#CONTEUDO'),
            rodape: await medirElemento(v3Page, '#RODAPE'),
            painel: alvo.painel ? await medirElemento(v3Page, alvo.painel) : null,
        }
    };

    // Screenshots raw
    await legPage.screenshot({ path: join(destinoRaw, `legacy-${alvo.id}-raw.png`), fullPage: true });
    await v3Page.screenshot({ path: join(destinoRaw, `v3-${alvo.id}-raw.png`), fullPage: true });

    // Sanitizar e gerar versionado
    await sanitizar(legPage);
    await sanitizar(v3Page);

    await legPage.screenshot({ path: join(destinoSanitizado, `legacy-${alvo.id}.png`), fullPage: true });
    await v3Page.screenshot({ path: join(destinoSanitizado, `v3-${alvo.id}.png`), fullPage: true });

    legPage.off('response', onRespLeg);
    v3Page.off('response', onRespV3);

    return {
        id: alvo.id,
        nome: alvo.nome,
        descricao: alvo.descricao,
        legacy: infoLeg,
        v3: infoV3,
    };
}

const alvosNav01 = [
    {
        id: 'NAV-01-01',
        nome: 'Logo -> Pagina Inicial',
        descricao: 'Clique no logotipo do topo navega para a Pagina Inicial',
        tipo: 'navegacao',
        acaoLeg: async page => {
            return page.goto(`${legadoBase}index.php`, { waitUntil: 'domcontentloaded' });
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('#TOPO a.image-up'),
            ]);
            return resp;
        },
    },
    {
        id: 'NAV-01-02',
        nome: 'Pagina Inicial',
        descricao: 'Link Pag. Inicial no menu superior',
        tipo: 'navegacao',
        acaoLeg: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('#inicio a'),
            ]);
            return resp;
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('li.menu-up:has-text("Pag. Inicial") a'),
            ]);
            return resp;
        },
    },
    {
        id: 'NAV-01-03',
        nome: 'Novo (Painel Inline)',
        descricao: 'Clique em Novo expande o painel inline #JS-Novo',
        tipo: 'inline',
        painel: '#JS-Novo',
        acaoLeg: async page => {
            await page.click('#menu-novo');
            await page.locator('#JS-Novo').waitFor({ state: 'visible' });
        },
        acaoV3: async page => {
            await page.click('#menu-novo');
            await page.locator('#JS-Novo').waitFor({ state: 'visible' });
        },
    },
    {
        id: 'NAV-01-04',
        nome: 'Localizar (Painel Inline)',
        descricao: 'Clique em Localizar expande o painel inline #JS-Localizar',
        tipo: 'inline',
        painel: '#JS-Localizar',
        acaoLeg: async page => {
            await page.click('#menu-localizar');
            await page.locator('#JS-Localizar').waitFor({ state: 'visible' });
        },
        acaoV3: async page => {
            await page.click('#menu-localizar');
            await page.locator('#JS-Localizar').waitFor({ state: 'visible' });
        },
    },
    {
        id: 'NAV-01-05',
        nome: 'Entrada',
        descricao: 'Listagem de Entrada',
        tipo: 'navegacao',
        acaoLeg: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('#entrada a'),
            ]);
            return resp;
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('li.menu-up:has-text("Entrada") a'),
            ]);
            return resp;
        },
    },
    {
        id: 'NAV-01-06',
        nome: 'Encaminhado',
        descricao: 'Listagem de Encaminhados',
        tipo: 'navegacao',
        acaoLeg: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('#encaminhados a'),
            ]);
            return resp;
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('li.menu-up:has-text("Encaminhado") a'),
            ]);
            return resp;
        },
    },
    {
        id: 'NAV-01-07',
        nome: 'Aguardando credito',
        descricao: 'Listagem de Aguardando credito',
        tipo: 'navegacao',
        acaoLeg: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('#aguardandocredito a'),
            ]);
            return resp;
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('li.menu-up:has-text("Aguardando credito") a'),
            ]);
            return resp;
        },
    },
    {
        id: 'NAV-01-08',
        nome: 'Concluido!',
        descricao: 'Listagem de Concluidos',
        tipo: 'navegacao',
        acaoLeg: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('#concluidos a'),
            ]);
            return resp;
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('li.menu-up:has-text("Concluido!") a'),
            ]);
            return resp;
        },
    },
    {
        id: 'NAV-01-09',
        nome: 'Menu de Sessao (Painel Inline)',
        descricao: 'Clique no botao MENU expande #JS-Sessao',
        tipo: 'inline',
        painel: '#JS-Sessao',
        acaoLeg: async page => {
            await page.click('#menu-sessao');
            await page.locator('#JS-Sessao').waitFor({ state: 'visible' });
        },
        acaoV3: async page => {
            await page.click('#menu-sessao');
            await page.locator('#JS-Sessao').waitFor({ state: 'visible' });
        },
    },
    {
        id: 'NAV-01-10',
        nome: 'Sign Out (Logout)',
        descricao: 'Botao SIGN OUT executa logout e redireciona para a tela de autenticacao',
        tipo: 'navegacao',
        acaoLeg: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('.formButtonSIGNOUT'),
            ]);
            return resp;
        },
        acaoV3: async page => {
            const [resp] = await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
                page.click('.formButtonSIGNOUT'),
            ]);
            return resp;
        },
    },
];

async function main() {
    console.log('Iniciando auditoria navegacional do Tema V1 (Lote NAV-01)...');
    const browser = await chromium.launch();

    let legPage = await entrarLegacy(browser);
    let v3Page = await entrarV3(browser);

    const resultados = [];

    for (const alvo of alvosNav01) {
        console.log(`Auditando [${alvo.id}] ${alvo.nome}...`);
        
        // Se a ação anterior foi logout, precisamos relogar
        if (alvo.id === 'NAV-01-10') {
            const res = await auditarAlvo(legPage, v3Page, alvo);
            resultados.push(res);
            break;
        }

        const res = await auditarAlvo(legPage, v3Page, alvo);
        resultados.push(res);
    }

    await browser.close();

    writeFileSync(destinoManifesto, JSON.stringify(resultados, null, 2), 'utf-8');
    console.log(`Auditoria concluída com sucesso! Manifesto gerado em ${destinoManifesto}`);
}

main().catch(err => {
    console.error('Falha na execução da auditoria navegacional:', err);
    process.exit(1);
});
