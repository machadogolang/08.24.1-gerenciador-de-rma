<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * UF-18 (GAP-V2-05 / CAP-AUX-001) - Procedimento Operacional / Ajuda de RMA.
 * Compartilhado entre Tema V1 e Tema V2 com apresentações visuais nativas de cada tema.
 */
class AjudaController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Rma::class);

        return view_do_tema('rma.ajuda', [
            'titulo' => 'Procedimento Operacional de RMA',
        ]);
    }
}
