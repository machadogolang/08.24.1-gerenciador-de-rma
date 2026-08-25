import { expect, test } from '@playwright/test';
import { execSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import { join } from 'node:path';

/**
 * VIS-V1-002/003/004 — prova end-to-end de que "Novo" expande `#JS-Novo` inline sem
 * navegar (equivalente a `NovoMaximize()`, `pattern/14.6.1.js`), preservando o
 * conteúdo da tela onde o operador estava. Não é o mesmo teste de
 * `ControlePainelTest`/`PainelNovoTemaV1Test` (PHPUnit, sem JS real) — aqui o clique é
 * um evento de browser de verdade.
 */
const DESTINO = join(process.cwd(), 'docs/produto/screenshots-vis-v1-001');

test.beforeAll(() => {
    mkdirSync(DESTINO, { recursive: true });
    execSync(
        `php artisan tinker --execute="
            use App\\\\Models\\\\User; use App\\\\Identidade\\\\Dominio\\\\{Papel,TemaPreferido};
            User::firstOrCreate(['email' => 'shot-novo-v1@teste.local'], [
                'name' => 'Shot Novo V1', 'password' => bcrypt('password'),
                'papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V1,
            ]);
        "`,
        { cwd: process.cwd() },
    );
});

test('clicar em Novo expande o painel inline sem navegar, mantendo o conteúdo da tela atual', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1400 });

    await page.goto('/login');
    await page.fill('#email', 'shot-novo-v1@teste.local');
    await page.fill('#password', 'password');
    await page.click('button[type=submit]');
    await page.waitForURL('**/perfil');

    // Estava em "Aguardando credito" (comportamento descrito pelo usuário: clicar
    // "Novo" a partir de qualquer superfície, não só da Home).
    await page.goto('/rmas-aguardando-credito');
    const urlAntes = page.url();

    await expect(page.locator('#JS-Novo')).toBeHidden();

    await page.click('#menu-novo a');

    // URL não muda — não houve navegação.
    expect(page.url()).toBe(urlAntes);

    // Painel expandiu.
    await expect(page.locator('#JS-Novo')).toBeVisible();

    // Conteúdo anterior (título da tela de Aguardando crédito) continua no DOM/visível.
    await expect(page.locator('.title-comicone').first()).toBeVisible();

    // Formulário aparece acima do conteúdo anterior (ordem no DOM).
    const ordemDom = await page.evaluate(() => {
        const painelNovo = document.querySelector('#JS-Novo');
        const conteudo = document.querySelector('#CONTEUDO');
        if (!painelNovo || !conteudo) return null;
        // DOCUMENT_POSITION_FOLLOWING (4) em `conteudo` relativo a `painelNovo`
        // significa que `conteudo` vem DEPOIS de `painelNovo` no DOM.
        return Boolean(painelNovo.compareDocumentPosition(conteudo) & Node.DOCUMENT_POSITION_FOLLOWING);
    });
    expect(ordemDom).toBe(true);

    // Geometria mínima do runtime.
    await expect(page.locator('.tablenovo')).toBeVisible();
    await expect(page.locator('button.formButtonEnviarNovo')).toHaveText('CRIAR BD');
    await expect(page.locator('#marcarestoque')).toBeChecked();

    await page.screenshot({ path: join(DESTINO, '11-v3-novo-inline-sobre-aguardando-credito.png'), fullPage: true });
});

test('submit do painel Novo persiste os campos do Grupo A e PN/SNID', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1400 });

    await page.goto('/login');
    await page.fill('#email', 'shot-novo-v1@teste.local');
    await page.fill('#password', 'password');
    await page.click('button[type=submit]');
    await page.waitForURL('**/perfil');

    await page.goto('/rmas-entrada');
    await page.click('#menu-novo a');
    await expect(page.locator('#JS-Novo')).toBeVisible();

    await page.fill('#JS-Novo input[name=descricao]', 'HD externo com pane');
    await page.fill('#JS-Novo input[name=defeito]', 'Nao liga');
    await page.fill('#JS-Novo input[name=nfcompra]', '999888');
    await page.fill('#JS-Novo input[name=nfcompra_emissao]', '2026-03-01');
    await page.fill('#JS-Novo input[name=nfvenda]', '777666');
    await page.fill('#JS-Novo input[name=nfvenda_emissao]', '2026-04-02');
    await page.fill('#JS-Novo input[name=pn]', 'PN-PLAYWRIGHT');
    await page.fill('#JS-Novo input[name=snid]', 'SNID-PLAYWRIGHT');
    // Desmarca o checkbox pré-marcado — prova que "desmarcado" persiste false.
    await page.uncheck('#marcarestoque');

    await page.click('#JS-Novo button.formButtonEnviarNovo');
    await page.waitForURL('**/rmas/*');

    await expect(page.locator('body')).toContainText('PN-PLAYWRIGHT');
    await expect(page.locator('body')).toContainText('SNID-PLAYWRIGHT');
    await expect(page.locator('body')).toContainText('999888');
    await expect(page.locator('body')).toContainText('777666');
    await expect(page.locator('body')).toContainText('Não'); // "Item de estoque: Não"
});
