<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Rma\Aplicacao\Relatorios\PainelEstatisticoV2;
use Illuminate\View\View;

/**
 * PAR15-REL-001..007 - pagina "Relatorios" do TEMA V2, equivalente a
 * `15.8.1/page/relatorios.php` (painel estatistico). O V1 mantem os relatorios
 * RCD/RPEC/RMPE proprios; o V2 passa a ter tambem esta superficie historica.
 */
class PainelDeRelatoriosController extends Controller
{
    public function index(PainelEstatisticoV2 $painel): View
    {
        return view('temas.v2.rma.relatorios.index', [
            'titulo' => 'Relatorios',
            'painel' => $painel->montar(),
        ]);
    }
}
