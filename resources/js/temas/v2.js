// v2.js — entry point Vite do TEMA V2. Importa jQuery (dependência do plugin Bootstrap
// 3) + o plugin de abas nativo do Bootstrap 3.3.5 (`data-toggle="tab"`) — é assim que
// o dashboard (`temas/v2/rma/index.blade.php`, 7 tab-panes) troca de aba client-side,
// sem AJAX/reload, reproduzindo o mecanismo confirmado no LEGACY-RUNTIME (ver
// design.md "Mecanismo de navegação por tema"). Só importamos o plugin `tab` isolado
// (não o bundle `bootstrap.js` inteiro) — é o único plugin usado pelas telas desta fase.
import '../../sass/temas/v2.scss';
import $ from 'jquery';

window.$ = window.jQuery = $;

import 'bootstrap/js/tab';

document.addEventListener('DOMContentLoaded', () => {
    // Equivalente a `.pmo` (pattern/15.9.7.js), mesmo comportamento do TEMA V1.
    document.querySelectorAll('[data-pmo-alvo]').forEach((gatilho) => {
        gatilho.addEventListener('click', () => {
            const alvo = document.querySelector(gatilho.getAttribute('data-pmo-alvo'));
            if (alvo) {
                alvo.style.display = alvo.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
});
