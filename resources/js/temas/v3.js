import '../../sass/temas/v3.scss';

document.addEventListener('DOMContentLoaded', () => {
    const corpo = document.body;
    const botaoDrawer = document.querySelector('[data-v3-drawer-abrir]');
    const drawer = document.querySelector('[data-v3-drawer]');
    const fecharDrawer = document.querySelector('[data-v3-drawer-fechar]');
    const backdrop = document.querySelector('[data-v3-drawer-backdrop]');
    const botaoRail = document.querySelector('[data-v3-rail]');

    function alternarDrawer(aberto) {
        if (!drawer || !botaoDrawer) return;
        drawer.classList.toggle('is-open', aberto);
        botaoDrawer.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        if (aberto) {
            corpo.classList.add('v3-drawer-aberto');
            const alvo = fecharDrawer ?? drawer.querySelector('a, button');
            alvo?.focus();
        } else {
            corpo.classList.remove('v3-drawer-aberto');
            botaoDrawer.focus();
        }
    }

    botaoDrawer?.addEventListener('click', () => {
        alternarDrawer(drawer?.classList.contains('is-open') === false);
    });
    fecharDrawer?.addEventListener('click', () => alternarDrawer(false));
    backdrop?.addEventListener('click', () => alternarDrawer(false));

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && drawer?.classList.contains('is-open')) {
            alternarDrawer(false);
        }
    });

    botaoRail?.addEventListener('click', () => {
        const rail = document.querySelector('[data-v3-rail-conteudo]');
        rail?.classList.toggle('is-collapsed');
    });
});
