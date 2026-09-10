// v2.js - entry point Vite do TEMA V2. Importa jQuery (dependência do plugin Bootstrap
// 3) + o plugin de abas nativo do Bootstrap 3.3.5 (`data-toggle="tab"`) - é assim que
// o dashboard (`temas/v2/rma/index.blade.php`, 7 tab-panes) troca de aba client-side,
// sem AJAX/reload, reproduzindo o mecanismo confirmado no LEGACY-RUNTIME (ver
// design.md "Mecanismo de navegação por tema"). Só importamos o plugin `tab` isolado
// (não o bundle `bootstrap.js` inteiro) - é o único plugin usado pelas telas desta fase.
import '../../sass/temas/v2.scss';
// Precisa ser importado ANTES dos plugins Bootstrap - ver comentário em
// `_jquery-global.js` (achado desta sessão: sem isso, `bootstrap/js/tab` e
// `bootstrap/js/dropdown` lançam `jQuery is not defined` ao carregar, silenciosamente
// quebrando as abas Início/Pesquisar/Entrada/etc. - bug pré-existente à correção de
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
                // PAR-RES-A-02 - so reescreve o rotulo quando o proprio gatilho e um `.pmo`
                // (itens "Mostrar/Ocultar"). O cabecalho do painel lateral V2 usa
                // `data-pmo-alvo` para expandir, mas no legado o TITULO permanece fixo.
                if (gatilho.classList.contains('pmo')) {
                    gatilho.textContent = abrir ? 'Ocultar' : 'Mostrar';
                }
                gatilho.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            }
        });
    });

    // PAR-V2-NOVO-01 - condicional do 15.8.1 (page/novo_rma.php + JS do legado):
    // Origem = Cliente mostra o bloco NF Venda/Cliente; qualquer outra origem mostra
    // NF Compra/Fornecedor. Funciona na aba #novo_rma e na rota /create (mesmo
    // partial), sem codigo legado inline.
    document.querySelectorAll('.form-novo-v2').forEach((formulario) => {
        const origem = formulario.querySelector('select[name="origem"]');
        const blocoCliente = formulario.querySelector('#cli');
        const blocoOutraOrigem = formulario.querySelector('#outraorigem');
        if (! origem || ! blocoCliente || ! blocoOutraOrigem) return;

        const atualizarBloco = () => {
            const ehCliente = origem.value === 'Cliente';
            blocoCliente.style.display = ehCliente ? 'block' : 'none';
            blocoOutraOrigem.style.display = ehCliente ? 'none' : 'block';
        };

        origem.addEventListener('change', atualizarBloco);
        atualizarBloco();
    });

    // CP17 - fora de `v2.rmas.index` o header aponta de volta para lá com uma âncora
    // (`#entrada`, `#pesquisar` etc., ver `temas/v2/layout.blade.php`); ao chegar,
    // abre a aba correspondente sem precisar de reload nem de rota própria por aba.
    if (window.location.hash) {
        const aba = document.querySelector(`.nav-tabs a[href="${window.location.hash}"]`);
        if (aba) {
            $(aba).tab('show');
        }
    }

    // UX-002 (P8) - confirmacao de remocao de parceiro sem inline JS.
    document.addEventListener('submit', (evento) => {
        const formulario = evento.target instanceof Element
            ? evento.target.closest('[data-confirmar-remocao]')
            : null;

        if (formulario && ! window.confirm(formulario.dataset.confirmarRemocao)) {
            evento.preventDefault();
        }
    });

    // UX-004 (P8) - prevencao de duplo envio (ver comentario em v1.js).
    document.addEventListener('submit', (evento) => {
        const formulario = evento.target;

        if (! (formulario instanceof HTMLFormElement) || evento.defaultPrevented) {
            return;
        }

        formulario.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((botao) => {
            botao.disabled = true;
        });
    });
});
