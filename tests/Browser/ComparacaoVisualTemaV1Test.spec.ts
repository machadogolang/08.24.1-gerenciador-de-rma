import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';

/**
 * Fase 8 — TEMA V1 não tem NENHUM `@media` query no legado (`pattern/14.6.1.css`,
 * `#BASE{width:984px}` fixo). O teste correto aqui NÃO é "consertar" a
 * responsividade — é confirmar que o layout V3 continua fixo/não-responsivo nos 3
 * breakpoints de QA (390/768/1440, `checklist-master-v3.md` Parte 3/Fase 10),
 * exatamente como o legado. `$largura-fixa-tema-v1` (984px) é a única fonte da
 * verdade — não redigitado aqui.
 *
 * Nome do arquivo termina em `.blade.php.spec.ts` só para conviver ao lado do
 * `RenderizaTemaV1Test.php` no mesmo diretório temático do checklist; o runner é o
 * Playwright (`npx playwright test`), não o PHPUnit.
 */
const LARGURA_FIXA_TEMA_V1 = 984; // resources/sass/temas/v1.scss: $largura-fixa-tema-v1
const BREAKPOINTS_QA = [390, 768, 1440];

test.beforeAll(() => {
    execSync(
        `php artisan tinker --execute="
            use App\\\\Models\\\\User; use App\\\\Identidade\\\\Dominio\\\\{Papel,TemaPreferido};
            User::firstOrCreate(['email' => 'qa-v1@teste.local'], [
                'name' => 'QA V1', 'password' => bcrypt('password'),
                'papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V1,
            ]);
        "`,
        { cwd: process.cwd() },
    );
});

for (const largura of BREAKPOINTS_QA) {
    test(`TEMA V1 permanece fixo em ${largura}px (sem @media, ${LARGURA_FIXA_TEMA_V1}px sempre)`, async ({ page }) => {
        await page.setViewportSize({ width: largura, height: 900 });

        await page.goto('/login');
        await page.fill('#email', 'qa-v1@teste.local');
        await page.fill('#password', 'password');
        await page.click('button[type=submit]');
        await page.waitForURL('**/perfil');

        // `getComputedStyle().width` reflete a propriedade CSS `width` (984px) — a
        // caixa renderizada (`getBoundingClientRect`) é maior por causa do
        // `padding-left`/`padding-right` de 10px cada (`content-box`, mesmo modelo de
        // caixa do CSS original, sem `box-sizing:border-box`), então não é ela que
        // prova "fixo" — o que prova é a MESMA largura computada em qualquer viewport.
        const largurabase = await page.locator('#BASE').evaluate((el) => parseFloat(getComputedStyle(el).width));

        expect(Math.round(largurabase)).toBe(LARGURA_FIXA_TEMA_V1);
    });
}
