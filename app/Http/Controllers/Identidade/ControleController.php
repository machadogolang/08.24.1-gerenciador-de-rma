<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * PAR15-AUD-001/005 - hub "Controle" do TEMA V2, equivalente a
 * `15.8.1/page/controle.php`: sem `subp`, o Legacy renderiza "Logs de autenticacao"
 * sob o menu/breadcrumb de Controle. O V1 mantem o Controle proprio
 * (`rmas.controle.index`), entao aqui o tema V1 e redirecionado.
 */
class ControleController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (($request->attributes->get('temaAtivo') ?? TemaPreferido::V2) !== TemaPreferido::V2) {
            return redirect()->route('rmas.controle.index');
        }

        Gate::authorize('gerenciar', User::class);

        $tentativas = TentativaDeAcesso::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('temas.v2.identidade.controle', [
            'titulo' => 'Controle',
            'subpAtual' => 'logs_de_autenticacao',
            'tentativas' => $tentativas,
        ]);
    }
}
