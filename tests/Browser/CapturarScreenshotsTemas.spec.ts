import { test } from '@playwright/test';
import { execSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import { join } from 'node:path';

/**
 * Fase 8 — captura de screenshots reais (PNG) das telas principais dos dois temas, em
 * `docs/produto/screenshots-fase8/`, para inspeção manual/anexo ao checklist. NÃO é um
 * teste de asserção visual (isso é `ComparacaoVisualTemaV{1,2}Test.spec.ts`) — só
 * grava evidência.
 */
const DESTINO = join(process.cwd(), 'docs/produto/screenshots-fase8');

test.beforeAll(() => {
    mkdirSync(DESTINO, { recursive: true });
    execSync(
        `php artisan tinker --execute="
            use App\\\\Models\\\\User; use App\\\\Identidade\\\\Dominio\\\\{Papel,TemaPreferido};
            User::firstOrCreate(['email' => 'shot-v1@teste.local'], [
                'name' => 'Shot V1', 'password' => bcrypt('password'),
                'papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V1,
            ]);
            User::firstOrCreate(['email' => 'shot-v2@teste.local'], [
                'name' => 'Shot V2', 'password' => bcrypt('password'),
                'papel' => Papel::Operador, 'tema_preferido' => TemaPreferido::V2,
            ]);
        "`,
        { cwd: process.cwd() },
    );
});

test('captura TEMA V1 (login, perfil, RMAs, clientes)', async ({ page }) => {
    await page.setViewportSize({ width: 1366, height: 900 });

    await page.goto('/login');
    await page.screenshot({ path: join(DESTINO, 'v1-login.png'), fullPage: true });

    await page.fill('#email', 'shot-v1@teste.local');
    await page.fill('#password', 'password');
    await page.click('button[type=submit]');
    await page.waitForURL('**/perfil');
    await page.screenshot({ path: join(DESTINO, 'v1-perfil.png'), fullPage: true });

    await page.goto('/v1/rma?tipo=texto&valor=a');
    await page.screenshot({ path: join(DESTINO, 'v1-rmas.png'), fullPage: true });

    await page.goto('/v1/parceiros/clientes');
    await page.screenshot({ path: join(DESTINO, 'v1-clientes.png'), fullPage: true });
});

test('captura TEMA V2 (login, perfil, RMAs com 7 abas, clientes)', async ({ page }) => {
    await page.setViewportSize({ width: 1366, height: 900 });

    await page.goto('/login');
    await page.screenshot({ path: join(DESTINO, 'v2-login.png'), fullPage: true });

    await page.fill('#email', 'shot-v2@teste.local');
    await page.fill('#password', 'password');
    await page.click('button[type=submit]');
    await page.waitForURL('**/perfil');
    await page.screenshot({ path: join(DESTINO, 'v2-perfil.png'), fullPage: true });

    await page.goto('/v2/rma?tipo=texto&valor=a');
    await page.screenshot({ path: join(DESTINO, 'v2-rmas-inicio.png'), fullPage: true });

    await page.click('a[href="#entrada"]');
    await page.screenshot({ path: join(DESTINO, 'v2-rmas-entrada.png'), fullPage: true });

    await page.goto('/v2/parceiros/clientes');
    await page.screenshot({ path: join(DESTINO, 'v2-clientes.png'), fullPage: true });
});
