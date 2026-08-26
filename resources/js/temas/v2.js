// v2.js — entry point Vite do TEMA V2. Importa jQuery (dependência do plugin Bootstrap
// 3) + o plugin de abas nativo do Bootstrap 3.3.5 (`data-toggle="tab"`) — é assim que
// o dashboard (`temas/v2/rma/index.blade.php`, 7 tab-panes) troca de aba client-side,
// sem AJAX/reload, reproduzindo o mecanismo confirmado no LEGACY-RUNTIME (ver
// design.md "Mecanismo de navegação por tema"). Só importamos o plugin `tab` isolado
// (não o bundle `bootstrap.js` inteiro) — é o único plugin usado pelas telas desta fase.
import '../../sass/temas/v2.scss';
// Precisa ser importado ANTES dos plugins Bootstrap — ver comentário em
// `_jquery-global.js` (achado desta sessão: sem isso, `bootstrap/js/tab` e
// `bootstrap/js/dropdown` lançam `jQuery is not defined` ao carregar, silenciosamente
// quebrando as abas Início/Pesquisar/Entrada/etc. — bug pré-existente à correção de
// paridade V2, não introduzido por ela).
import $ from './_jquery-global';

import 'bootstrap/js/tab';
import 'bootstrap/js/dropdown';

document.addEventListener('DOMContentLoaded', () => {
    // Equivalente a `.pmo` (pattern/15.9.7.js), mesmo comportamento do TEMA V1.
    document.querySelectorAll('[data-pmo-alvo]').forEach((gatilho) => {
        gatilho.addEventListener('click', () => {
            const alvo = document.querySelector(gatilho.getAttribute('data-pmo-alvo'));
            if (alvo) {
                const abrir = alvo.style.display === 'none';
                alvo.style.display = abrir ? 'block' : 'none';
                gatilho.textContent = abrir ? 'Ocultar' : 'Mostrar';
                gatilho.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            }
        });
    });

    // CP17 — fora de `v2.rmas.index` o header aponta de volta para lá com uma âncora
    // (`#entrada`, `#pesquisar` etc., ver `temas/v2/layout.blade.php`); ao chegar,
    // abre a aba correspondente sem precisar de reload nem de rota própria por aba.
    if (window.location.hash) {
        const aba = document.querySelector(`.nav-tabs a[href="${window.location.hash}"]`);
        if (aba) {
            $(aba).tab('show');
        }
    }
});
