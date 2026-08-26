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
                const abrir = alvo.style.display === 'none';
                alvo.style.display = abrir ? 'block' : 'none';
                gatilho.textContent = abrir ? 'Ocultar' : 'Mostrar';
                gatilho.setAttribute('aria-expanded', abrir ? 'true' : 'false');
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

// CP7 (fase 2 V1) — equivalente a `LocalizarMaximize()` (`pattern/14.6.1.js`): não
// chama nenhum "minimize" (o legado não fecha o painel Novo se os dois estiverem
// abertos ao mesmo tempo) — mesma omissão preservada aqui.
const botaoLocalizar = document.querySelector('#menu-localizar a');
const painelLocalizar = document.querySelector('#JS-Localizar');

if (botaoLocalizar && painelLocalizar) {
    botaoLocalizar.addEventListener('click', (evento) => {
        evento.preventDefault();
        painelLocalizar.style.display = 'block';
        document.querySelector('#menu-localizar').style.fontWeight = 'bold';
    });
}

// CP9 (fase 2 V1) — Quadro de Anotações da Página Inicial: `startpage.php` salva a
// cada `onkeyup` via AJAX próprio, sem botão "Salvar". Equivalente moderno: debounce
// de 800ms + `fetch` pro mesmo endpoint do formulário tradicional do perfil
// (`identidade.perfil.anotacao.update`) — sem reimplementar o polling antigo.
const campoAnotacao = document.querySelector('[data-anotacao-autosave]');

if (campoAnotacao) {
    let temporizador = null;

    campoAnotacao.addEventListener('input', () => {
        clearTimeout(temporizador);
        campoAnotacao.classList.remove('textareaanotacao--erro');
        temporizador = setTimeout(() => {
            fetch(campoAnotacao.dataset.anotacaoUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ anotacao: campoAnotacao.value }),
            })
                .then((resposta) => {
                    if (!resposta.ok) {
                        campoAnotacao.classList.add('textareaanotacao--erro');
                    }
                })
                // Tratamento discreto (CP9-05): sem alert/modal — só marca o campo
                // pra indicar visualmente que a última alteração não foi salva.
                .catch(() => campoAnotacao.classList.add('textareaanotacao--erro'));
        }, 800);
    });
}
