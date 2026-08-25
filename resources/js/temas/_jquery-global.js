// _jquery-global.js — os plugins Bootstrap 3 (`bootstrap/js/tab`, `bootstrap/js/dropdown`)
// são IIFEs que fecham sobre o identificador global `jQuery` no MOMENTO em que o
// módulo é avaliado (`}(jQuery)` no final de cada arquivo) — não fazem `require`
// próprio. Em ESM, toda declaração `import` é IÇADA para o topo do módulo antes de
// qualquer instrução comum, então `window.jQuery = $` escrito depois dos `import`
// dos plugins no mesmo arquivo executa TARDE DEMAIS (os plugins já teriam lançado
// `ReferenceError: jQuery is not defined` ao serem avaliados). Import próprio,
// importado ANTES dos plugins, resolve porque módulos ESM executam na ordem dos
// `import`s entre si — só a ordem *dentro* de um único arquivo é içada.
import $ from 'jquery';

window.$ = window.jQuery = $;

export default $;
