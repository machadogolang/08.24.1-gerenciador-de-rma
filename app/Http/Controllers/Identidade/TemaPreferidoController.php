<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Identidade\Aplicacao\AlternarTemaPreferido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TemaPreferidoController extends Controller
{
    public function update(Request $request, AlternarTemaPreferido $alternarTemaPreferido): RedirectResponse
    {
        $tema = $alternarTemaPreferido->alternar($request->user());

        $request->session()->put('tema_preferido', $tema->value);

        return $this->redirecionarAposTroca($request);
    }

    /**
     * PAR-V2-THEME-01 - troca V1 <-> V2 em rotas QA prefixadas.
     *
     * `back()` so funciona em rotas canonicas: la o middleware resolve o tema pela
     * preferencia recem-gravada. Em `/v1/...` ou `/v2/...` o middleware forca o tema
     * pelo prefixo, entao voltar para o mesmo endereco faz a troca parecer morta.
     * Aqui trocamos apenas o prefixo do referer (`/v1/x` -> `/v2/x` e vice-versa),
     * preservando caminho, query e fragmento; sem prefixo, mantemos `back()`.
     * Se o referer estiver ausente, o fallback continua `back()` (comportamento
     * anterior). Nenhuma rota V3 e alvo de alternancia publica.
     */
    private function redirecionarAposTroca(Request $request): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');
        $caminho = (string) parse_url($referer, PHP_URL_PATH);
        $caminhoNovo = null;

        if (str_starts_with($caminho, '/v1/')) {
            $caminhoNovo = '/v2/'.substr($caminho, 4);
        } elseif (str_starts_with($caminho, '/v2/')) {
            $caminhoNovo = '/v1/'.substr($caminho, 4);
        }

        if ($caminhoNovo === null) {
            return back();
        }

        $query = parse_url($referer, PHP_URL_QUERY);
        $fragmento = parse_url($referer, PHP_URL_FRAGMENT);

        return redirect($caminhoNovo
            . ($query !== null ? '?'.$query : '')
            . ($fragmento !== null ? '#'.$fragmento : ''));
    }
}
