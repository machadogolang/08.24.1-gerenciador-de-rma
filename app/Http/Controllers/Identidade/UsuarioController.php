<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Identidade\Aplicacao\ResetarSenhaDeUsuario;
use App\Identidade\Aplicacao\SenhaAtualIncorretaException;
use App\Identidade\Aplicacao\TrocarPropriaSenha;
use App\Identidade\Dominio\Papel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Lista usuários. Oculta SuperAdministrador de quem não é SuperAdministrador
     * (LEG-RMA-005) — usa o método nomeado do enum `Papel`, nunca ordinal/inteiro.
     */
    public function index(Request $request): View
    {
        Gate::authorize('gerenciar', User::class);

        $ator = $request->user();

        $usuarios = User::query()->orderBy('name')->get()
            ->when(
                ! $ator->papel->podeGerenciarUsuarios() || $ator->papel !== Papel::SuperAdministrador,
                fn ($usuarios) => $usuarios->reject(fn (User $u) => $u->papel->ocultoDaListagemDeUsuarios())
            );

        return view('identidade.usuarios.index', ['usuarios' => $usuarios]);
    }

    /**
     * Troca o papel de um usuário (LEG-RMA-005).
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);

        $dados = $request->validate([
            // `Rule::enum` exige backing type (tryFrom), que `Papel` deliberadamente não
            // tem (sem número mágico). Validação é contra os nomes reais dos cases.
            'papel' => ['required', Rule::in(array_column(Papel::cases(), 'name'))],
        ]);

        $usuario->update(['papel' => $dados['papel']]);

        return back()->with('status', 'Papel atualizado.');
    }

    /**
     * Reseta a senha de outro usuário (LEG-RMA-003) — exige `podeGerenciarUsuarios()`,
     * validado dentro do próprio caso de uso.
     */
    public function resetarSenha(Request $request, User $usuario, ResetarSenhaDeUsuario $resetarSenhaDeUsuario): RedirectResponse
    {
        $dados = $request->validate([
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetarSenhaDeUsuario->resetar($request->user(), $usuario, $dados['nova_senha']);

        return back()->with('status', 'Senha do usuário redefinida.');
    }

    /**
     * Página de perfil do próprio usuário autenticado (troca de senha + anotação
     * pessoal, LEG-RMA-004 / LEG-RMA-042).
     */
    public function perfil(Request $request): View
    {
        return view('identidade.perfil.senha', ['usuario' => $request->user()]);
    }

    /**
     * Troca a própria senha (LEG-RMA-004) — TEMA V1 como especificação (RN-21).
     */
    public function atualizarSenha(Request $request, TrocarPropriaSenha $trocarPropriaSenha): RedirectResponse
    {
        $dados = $request->validate([
            'senha_atual' => ['required', 'string'],
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $trocarPropriaSenha->trocar($request->user(), $dados['senha_atual'], $dados['nova_senha']);
        } catch (SenhaAtualIncorretaException $e) {
            return back()->withErrors(['senha_atual' => $e->getMessage()]);
        }

        return back()->with('status', 'Senha atualizada com sucesso.');
    }
}
