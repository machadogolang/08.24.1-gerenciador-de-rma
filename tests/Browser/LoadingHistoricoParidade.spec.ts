import { test, expect, Page } from '@playwright/test';

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:8095';

const VIEWPORT = { width: 1440, height: 900 };

async function login(page: Page): Promise<void> {
    await page.setViewportSize(VIEWPORT);
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'load' });
    await page.fill('#email', 'superadministrador@rma.local');
    await page.fill('#password', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]'),
    ]);
}

test.describe('PAR-LOADER - Paridade do Loading Historico e loaderpp.gif', () => {
    test('Tema V1: container #loader com loaderpp.gif e funcoes defer/mostrarLoader', async ({ page }) => {
        await login(page);
        await page.goto(`${BASE_URL}/v1/rma`, { waitUntil: 'domcontentloaded' });

        // Verifica presenca no DOM
        const loader = page.locator('#loader');
        await expect(loader).toHaveCount(1);
        await expect(loader).toHaveClass(/loader/);

        // Verifica a imagem do loader
        const img = loader.locator('img');
        await expect(img).toHaveCount(1);
        const src = await img.getAttribute('src');
        expect(src).toContain('loaderpp.gif');

        // Valida que a imagem e valida (carrega e possui largura natural)
        const naturalWidth = await img.evaluate((el: HTMLImageElement) => el.naturalWidth);
        expect(naturalWidth).toBe(100);

        // Testa controle programmatico do loader (window.mostrarLoader e window.defer)
        await page.evaluate(() => {
            if (typeof (window as any).mostrarLoader === 'function') {
                (window as any).mostrarLoader();
            }
        });
        await expect(loader).toBeVisible();

        // Valida largura historica (1004px) definida em 14.6.1.css
        const loaderWidth = await loader.evaluate((el: HTMLElement) => getComputedStyle(el).width);
        expect(loaderWidth).toBe('1004px');

        // Testa ocultacao via defer() historico
        await page.evaluate(() => {
            if (typeof (window as any).defer === 'function') {
                (window as any).defer();
            }
        });
        await expect(loader).toBeHidden();
    });

    test('Tema V2: containers #loader e #loader_r com loaderpp.gif e dimensoes historicas', async ({ page }) => {
        await login(page);
        await page.goto(`${BASE_URL}/v2/rma`, { waitUntil: 'domcontentloaded' });

        // Verifica presenca de #loader e #loader_r no DOM
        const loader = page.locator('#loader');
        const loaderR = page.locator('#loader_r');
        await expect(loader).toHaveCount(1);
        await expect(loaderR).toHaveCount(1);

        // Verifica as imagens do loader
        const img = loader.locator('img');
        await expect(img).toHaveCount(1);
        const naturalWidth = await img.evaluate((el: HTMLImageElement) => el.naturalWidth);
        expect(naturalWidth).toBe(100);

        // Testa controle programmatico no V2
        await page.evaluate(() => {
            if (typeof (window as any).mostrarLoader === 'function') {
                (window as any).mostrarLoader();
            }
        });
        await expect(loader).toBeVisible();

        // Valida largura historica (900px) definida em 15.8.1.css
        const loaderWidth = await loader.evaluate((el: HTMLElement) => getComputedStyle(el).width);
        expect(loaderWidth).toBe('900px');

        // Testa ocultacao
        await page.evaluate(() => {
            if (typeof (window as any).ocultarLoader === 'function') {
                (window as any).ocultarLoader();
            }
        });
        await expect(loader).toBeHidden();
    });
});
