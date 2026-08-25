<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Identidade\Aplicacao\AutenticarUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessaoController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AutenticarUsuario $autenticarUsuario): RedirectResponse
    {
        $credenciais = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $tema = $autenticarUsuario->autenticar(
            $credenciais['email'],
            $credenciais['password'],
            $request->ip(),
            $request->userAgent(),
        );

        $request->session()->regenerate();
        $request->session()->put('tema_preferido', $tema->value);

        $usuario = Auth::user();
        $destino = $usuario->papel->podeGerenciarUsuarios()
            ? route('identidade.usuarios.index')
            : route('identidade.perfil.show');

        return redirect()->intended($destino);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
