import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { join } from 'node:path';

const breakpoints = JSON.parse(
    readFileSync(join(process.cwd(), 'tests/Browser/Support/breakpoints-tema-v2.json'), 'utf-8'),
) as {
    breakpoints: Record<string, number>;
    largurasContainer: Record<string, number>;
};

/**
 * Fase 8 — TEMA V2 tem breakpoints PRÓPRIOS (`15.8.1/css/media.php`,
 * `resources/sass/temas/v2.scss:$breakpoints-tema-v2`/`$larguras-container-tema-v2`).
 * A asserção usa a largura de `.container` esperada pela regra `min-width` mais
 * próxima ABAIXO do breakpoint de QA (390/768/1440) — nenhum dos 6 valores é
 * redigitado aqui, todos vêm de `Support/breakpoints-tema-v2.json` (gerado/mantido a
 * partir do mesmo mapa Sass).
 */
const BREAKPOINTS_QA = [390, 768, 1440];

function larguraContainerEsperada(viewport: number): number | null {
    const ordenados = Object.entries(breakpoints.breakpoints).sort((a, b) => a[1] - b[1]);
    let atual: string | null = null;
    for (const [nome, largura] of ordenados) {
        if (viewport >= largura) {
            atual = nome;
        }
    }
    return atual ? breakpoints.largurasContainer[atual as keyof typeof breakpoints.largurasContainer] : null;
}

test.beforeAll(() => {
    execSync(
        `php artisan tinker --execute="
            use App\\\\Models\\\\User; use App\\\\Identidade\\\\Dominio\\\\{Papel,TemaPreferido};
            User::firstOrCreate(['email' => 'qa-v2@teste.local'], [
                'name' => 'QA V2', 'password' => bcrypt('password'),
                'papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V2,
            ]);
        "`,
        { cwd: process.cwd() },
    );
});

for (const largura of BREAKPOINTS_QA) {
    test(`TEMA V2 usa a largura de .container esperada em ${largura}px`, async ({ page }) => {
        await page.setViewportSize({ width: largura, height: 900 });

        await page.goto('/login');
        await page.fill('#email', 'qa-v2@teste.local');
        await page.fill('#password', 'password');
        await page.click('button[type=submit]');
        await page.waitForURL('**/perfil');

        const esperada = larguraContainerEsperada(largura);
        test.skip(esperada === null, `Viewport ${largura}px abaixo do menor breakpoint do tema.`);

        const largura_container = await page.locator('.container').first().evaluate((el) => el.getBoundingClientRect().width);

        expect(Math.round(largura_container)).toBe(esperada);
    });
}
