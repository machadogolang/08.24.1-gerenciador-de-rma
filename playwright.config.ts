import { defineConfig } from '@playwright/test';

/**
 * Fase 8 — QA visual (`tests/Browser/`). Roda contra a app V3 já de pé (`sail up`,
 * `:8095`) — NÃO sobe o servidor sozinho (o app V3 depende do MySQL/Sail, fora do
 * escopo do runner do Playwright). Compara com o LEGACY-RUNTIME (`:8094`) quando
 * necessário, lido diretamente por `page.goto('http://host.docker.internal:8094/...')`
 * ou pela rede do host, conforme o teste.
 */
export default defineConfig({
    testDir: './tests/Browser',
    timeout: 30_000,
    fullyParallel: false,
    reporter: [['list']],
    use: {
        // Roda DENTRO do container `laravel.test` (`sail exec laravel.test npx
        // playwright test`) — a app escuta em `localhost:80` dentro do container
        // (`:8095` é só o mapeamento externo do host, ver `.env` APP_PORT).
        baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'chromium', use: { browserName: 'chromium' } },
    ],
});
