// v1.js — entry point Vite do TEMA V1. Sem framework — só o JS autoral equivalente a
// `pattern/14.6.1.js` + `pattern/15.9.7.js` (toggle "mostrar/ocultar" de anotação
// pessoal via `.pmo`, painel "Novo RMA" via show/hide). TEMA V1 não importa jQuery nem
// Bootstrap.
import '../../sass/temas/v1.scss';

document.addEventListener('DOMContentLoaded', () => {
    // Equivalente a `.pmo` (pattern/15.9.7.js) — alterna a exibição de um bloco de
    // anotação/observação sem reload de página.
    document.querySelectorAll('[data-pmo-alvo]').forEach((gatilho) => {
        gatilho.addEventListener('click', () => {
            const alvo = document.querySelector(gatilho.getAttribute('data-pmo-alvo'));
            if (alvo) {
                alvo.style.display = alvo.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
});

const botaoSessao = document.querySelector('#menu-sessao');
const painelSessao = document.querySelector('#JS-Sessao');

if (botaoSessao && painelSessao) {
    botaoSessao.addEventListener('click', () => {
        const aberto = painelSessao.style.display !== 'none';
        painelSessao.style.display = aberto ? 'none' : 'block';
        botaoSessao.setAttribute('aria-expanded', aberto ? 'false' : 'true');
    });
}

// VIS-V1-002 — equivalente a `NovoMaximize()` (`pattern/14.6.1.js`): expande
// `#JS-Novo` sobre a superfície atual sem navegar. `href` continua apontando para
// `/rmas/create` (fallback funcional sem JS); com JS, o clique normal é interceptado
// e a navegação, cancelada — o conteúdo da página atual permanece visível abaixo do
// formulário expandido.
const botaoNovo = document.querySelector('#menu-novo');
const painelNovo = document.querySelector('#JS-Novo');

if (botaoNovo && painelNovo) {
    botaoNovo.addEventListener('click', (evento) => {
        evento.preventDefault();
        painelNovo.style.display = 'block';
        botaoNovo.style.fontWeight = 'bold';
    });
}
