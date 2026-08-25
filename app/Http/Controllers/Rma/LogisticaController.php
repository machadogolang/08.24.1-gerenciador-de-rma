<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\BoletinsRelacionados;
use App\Rma\Aplicacao\ConsolidarFretePorCidade;
use App\Rma\Aplicacao\VerDetalheDoRma;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * `LEG-RMA-040`/`041` — duas consultas de logística sobre `Rma` (ver `proposal.md`,
 * "reúne LEG-RMA-040/041 porque fazem mais sentido lidas junto com o histórico de
 * modificação do que junto ao fluxo de crédito da Fase 6").
 */
class LogisticaController extends Controller
{
    public function fretePortoAlegre(ConsolidarFretePorCidade $caso): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        return view('rma.logistica.frete-porto-alegre', [
            'titulo' => 'Frete consolidado — Porto Alegre',
            'rmas' => $caso->listar(),
        ]);
    }

    public function boletinsRelacionados(int $rma, VerDetalheDoRma $buscar, BoletinsRelacionados $caso): View
    {
        Gate::authorize('view', RmaEloquent::class);

        $registro = $buscar->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return view('rma.logistica.boletins-relacionados', [
            'titulo' => "Boletins relacionados — RMA #{$registro->id}",
            'registro' => $registro,
            'relacionados' => $caso->listar($registro),
        ]);
    }
}
