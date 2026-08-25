<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Alertas\ListarGruposDeAlertas;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * Painel de alertas (`LEG-RMA-018` a `029`) — view mínima, sem fidelidade visual
 * (cores/CSS por tema fica para a Fase 8). `ListarGruposDeAlertas` (extraída na
 * correção de fidelidade Fase 8, 2026-08-25) compõe as 11 regras de leitura;
 * `RmaController::index` usa a MESMA classe para o "CENTRO DE AVISOS" da aba
 * "Início"/"Pág. Inicial" — não há lógica duplicada entre as duas telas.
 */
class PainelDeAlertasController extends Controller
{
    public function index(ListarGruposDeAlertas $listarGruposDeAlertas): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        return view('rma._painel_de_alertas', [
            'grupos' => $listarGruposDeAlertas->listar(),
        ]);
    }
}
