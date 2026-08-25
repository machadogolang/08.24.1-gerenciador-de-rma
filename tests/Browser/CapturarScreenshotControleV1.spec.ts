import { test } from '@playwright/test';
import { execSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import { join } from 'node:path';

/**
 * VIS-V1-010 — captura de evidência do painel "Controle" do TEMA V1 já corrigido
 * (`rmas.controle.index`), para comparação com os prints já commitados do Legacy
 * (`06-legacy-menu-controle-v1-colapsado.png`, `07-legacy-menu-controle-v1-ajuda-expandida.png`).
 * Não é teste de asserção (isso é `ControlePainelTest.php`) — só grava evidência. Sem
 * dado de negócio real (banco de QA fictício, sem RMA arquivado) — commitável, mesma
 * regra de `screenshots-vis-v1-001/`.
 */
const DESTINO = join(process.cwd(), 'docs/produto/screenshots-vis-v1-001');

test.beforeAll(() => {
    mkdirSync(DESTINO, { recursive: true });
    execSync(
        `php artisan tinker --execute="
            use App\\\\Models\\\\User; use App\\\\Identidade\\\\Dominio\\\\{Papel,TemaPreferido};
            User::firstOrCreate(['email' => 'shot-controle-v1@teste.local'], [
                'name' => 'Shot Controle V1', 'password' => bcrypt('password'),
                'papel' => Papel::Supervisor, 'tema_preferido' => TemaPreferido::V1,
            ]);
        "`,
        { cwd: process.cwd() },
    );
});

test('captura painel Controle do TEMA V1 corrigido (VIS-V1-010)', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1400 });

    await page.goto('/login');
    await page.fill('#email', 'shot-controle-v1@teste.local');
    await page.fill('#password', 'password');
    await page.click('button[type=submit]');
    // Supervisor loga direto em "Usuários" (`podeGerenciarUsuarios()`, ver
    // `SessaoController::store`), não em `/perfil` — segue direto para o painel.
    await page.waitForURL('**/usuarios');

    await page.goto('/rmas-controle');

    // Expande todos os painéis <details> para provar a composição completa (7 ações).
    await page.evaluate(() => {
        document.querySelectorAll('details').forEach((d) => d.setAttribute('open', ''));
    });

    await page.screenshot({ path: join(DESTINO, '10-v3-controle-painel-corrigido.png'), fullPage: true });
});
