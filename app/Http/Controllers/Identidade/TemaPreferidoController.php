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

        return back();
    }
}
