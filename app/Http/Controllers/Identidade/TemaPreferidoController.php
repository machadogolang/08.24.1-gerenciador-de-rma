<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Identidade\Aplicacao\AlternarTemaPreferido;
use App\Identidade\Aplicacao\DefinirTemaPreferido;
use App\Identidade\Dominio\TemaPreferido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TemaPreferidoController extends Controller
{
    public function update(
        Request $request,
        AlternarTemaPreferido $alternarTemaPreferido,
        DefinirTemaPreferido $definirTemaPreferido
    ): RedirectResponse {
        if ($request->filled('tema')) {
            $request->validate([
                'tema' => ['required', 'string', 'in:v1,v2,v3'],
            ]);

            $alvo = TemaPreferido::from((string) $request->input('tema'));
            $tema = $definirTemaPreferido->definir($request->user(), $alvo);
        } else {
            $tema = $alternarTemaPreferido->alternar($request->user());
        }

        $request->session()->put('tema_preferido', $tema->value);

        return $this->redirecionarAposTroca($request, $tema);
    }

    /**
     * T3-17 / PAR-V2-THEME-01 - troca V1 <-> V2 em rotas QA prefixadas ou selecao explicita.
     *
     * `back()` so funciona em rotas canonicas: la o middleware resolve o tema pela
     * preferencia recem-gravada. Em `/v1/...` ou `/v2/...` o middleware forca o tema
     * pelo prefixo, entao voltar para o mesmo endereco faz a troca parecer morta.
     * Aqui trocamos apenas o prefixo do referer (`/v1/x` -> `/v2/x` e vice-versa),
     * preservando caminho, query e fragmento; sem prefixo, mantemos `back()`.
     * Se o referer for `/v3/...`, redireciona para a raiz do tema alvo.
     */
    private function redirecionarAposTroca(Request $request, TemaPreferido $temaDestino): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');
        $caminho = (string) parse_url($referer, PHP_URL_PATH);

        if (str_starts_with($caminho, '/v3')) {
            if ($temaDestino === TemaPreferido::V1) {
                return redirect()->route('rmas.entrada');
            }
            if ($temaDestino === TemaPreferido::V2) {
                return redirect()->route('rmas.index');
            }

            return redirect()->route('v3.dashboard');
        }

        if ($temaDestino === TemaPreferido::V3) {
            return redirect()->route('v3.dashboard');
        }

        $caminhoNovo = null;

        if (str_starts_with($caminho, '/v1/')) {
            if ($temaDestino === TemaPreferido::V2) {
                $caminhoNovo = '/v2/'.substr($caminho, 4);
            }
        } elseif (str_starts_with($caminho, '/v2/')) {
            if ($temaDestino === TemaPreferido::V1) {
                $caminhoNovo = '/v1/'.substr($caminho, 4);
            }
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

