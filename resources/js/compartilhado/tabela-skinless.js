/**
 * tabela-skinless.js
 *
 * Motor headless/skinless de ordenacao client-side para listagens dos Temas V1 e V2.
 * Permite ordenar colunas por clique no cabecalho (th) mantendo a apresentacao visual
 * 100% identica ao legado historico 14.6.1 e 15.8.1:
 * - Zero estilizacao intrusiva ou bibliotecas externas pesadas.
 * - Rezebracao automatica de linhas (Tabelinha-TR1 / Tabelinha-TR2 / TrZebrada1 / TrZebrada2).
 * - Deteccao automatica de datas (dd/mm/yyyy hh:ii:ss), numeros e texto.
 * - Indicadores tipograficos discretos de ordenacao (▲ / ▼).
 */

function extrairValorCelula(td) {
    if (!td) return '';
    const texto = td.innerText || td.textContent || '';
    return texto.trim();
}

function detectarTipoValor(str) {
    // Data brasileira: dd/mm/yyyy ou dd/mm/yyyy hh:ii:ss
    const matchDataHora = str.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/);
    if (matchDataHora) {
        const dia = parseInt(matchDataHora[1], 10);
        const mes = parseInt(matchDataHora[2], 10) - 1;
        const ano = parseInt(matchDataHora[3], 10);
        const hora = matchDataHora[4] ? parseInt(matchDataHora[4], 10) : 0;
        const min = matchDataHora[5] ? parseInt(matchDataHora[5], 10) : 0;
        const seg = matchDataHora[6] ? parseInt(matchDataHora[6], 10) : 0;
        return {
            tipo: 'data',
            valor: new Date(ano, mes, dia, hora, min, seg).getTime(),
        };
    }

    // Numero ou ID com prefixo '#'
    const strLimpa = str.replace(/^#/, '').replace(/\./g, '').replace(',', '.').trim();
    if (strLimpa !== '' && !isNaN(Number(strLimpa))) {
        return {
            tipo: 'numero',
            valor: Number(strLimpa),
        };
    }

    // Texto padrao
    return {
        tipo: 'texto',
        valor: str.toLowerCase(),
    };
}

function compararValores(a, b, direcao) {
    const valA = detectarTipoValor(a);
    const valB = detectarTipoValor(b);

    let res = 0;
    if (valA.tipo === 'numero' && valB.tipo === 'numero') {
        res = valA.valor - valB.valor;
    } else if (valA.tipo === 'data' && valB.tipo === 'data') {
        res = valA.valor - valB.valor;
    } else {
        res = String(valA.valor).localeCompare(String(valB.valor), 'pt-BR', { numeric: true });
    }

    return direcao === 'asc' ? res : -res;
}

export function aplicarZebrado(tabela) {
    const tbody = tabela.querySelector('tbody');
    if (!tbody) return;

    const linhas = Array.from(tbody.querySelectorAll('tr'));
    linhas.forEach((linha, idx) => {
        if (linha.classList.contains('Tabelinha-TR1') || linha.classList.contains('Tabelinha-TR2')) {
            linha.classList.remove('Tabelinha-TR1', 'Tabelinha-TR2');
            linha.classList.add(idx % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2');
        } else if (linha.classList.contains('TrZebrada1') || linha.classList.contains('TrZebrada2')) {
            linha.classList.remove('TrZebrada1', 'TrZebrada2');
            linha.classList.add(idx % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2');
        }
    });
}

export function inicializarTabela(tabela) {
    if (tabela.dataset.skinlessInicializada) {
        return;
    }
    tabela.dataset.skinlessInicializada = 'true';

    const ths = tabela.querySelectorAll('thead th');
    const tbody = tabela.querySelector('tbody');
    if (!tbody || ths.length === 0) return;

    ths.forEach((th, indiceColuna) => {
        // Ignora colunas sem texto ou colunas de acao explicitamente desabilitadas
        const tituloOriginal = (th.innerText || th.textContent || '').trim();
        if (tituloOriginal === 'VER' || tituloOriginal === 'AÇÃO' || th.dataset.ordenavel === 'false') {
            return;
        }

        th.style.cursor = 'pointer';
        th.setAttribute('title', 'Clique para ordenar');

        // Cria span de indicador discreto
        const indicador = document.createElement('span');
        indicador.className = 'skinless-sort-indicator';
        indicador.style.fontSize = '9px';
        indicador.style.marginLeft = '4px';
        indicador.style.opacity = '0.6';
        th.appendChild(indicador);

        let direcaoAtual = null; // null -> asc -> desc -> null

        th.addEventListener('click', () => {
            // Limpa indicadores dos outros cabecalhos
            ths.forEach((outroTh) => {
                if (outroTh !== th) {
                    outroTh.removeAttribute('data-sort-direcao');
                    const ind = outroTh.querySelector('.skinless-sort-indicator');
                    if (ind) ind.textContent = '';
                }
            });

            if (direcaoAtual === null) {
                direcaoAtual = 'asc';
                indicador.textContent = ' ▲';
            } else if (direcaoAtual === 'asc') {
                direcaoAtual = 'desc';
                indicador.textContent = ' ▼';
            } else {
                direcaoAtual = null;
                indicador.textContent = '';
            }

            th.setAttribute('data-sort-direcao', direcaoAtual || '');

            const linhas = Array.from(tbody.querySelectorAll('tr'));
            if (direcaoAtual === null) {
                // Restaura ordem original de insercao
                linhas.sort((a, b) => {
                    const idxA = parseInt(a.dataset.ordemOriginal || '0', 10);
                    const idxB = parseInt(b.dataset.ordemOriginal || '0', 10);
                    return idxA - idxB;
                });
            } else {
                linhas.sort((trA, trB) => {
                    const celulaA = trA.children[indiceColuna];
                    const celulaB = trB.children[indiceColuna];
                    const valA = extrairValorCelula(celulaA);
                    const valB = extrairValorCelula(celulaB);
                    return compararValores(valA, valB, direcaoAtual);
                });
            }

            // Remonta linhas no DOM
            linhas.forEach((linha) => tbody.appendChild(linha));
            aplicarZebrado(tabela);
        });
    });

    // Registra ordem original nas linhas
    const linhasIniciais = Array.from(tbody.querySelectorAll('tr'));
    linhasIniciais.forEach((linha, idx) => {
        linha.dataset.ordemOriginal = String(idx);
    });
}

export function inicializarTodasTabelasSkinless() {
    const tabelas = document.querySelectorAll('table[data-tabela-skinless="true"], table.tabela-skinless');
    tabelas.forEach(inicializarTabela);
}

if (typeof window !== 'undefined') {
    window.inicializarTabelasSkinless = inicializarTodasTabelasSkinless;
}
