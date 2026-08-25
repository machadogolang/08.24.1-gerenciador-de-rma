<?php

namespace App\Http\Controllers\Parceiros;

use App\Compartilhado\Uf;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Cliente::class);

        return view_do_tema('parceiros.index', [
            'tipo' => 'clientes',
            'titulo' => 'Clientes',
            'registros' => Cliente::query()->orderBy('nome')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Cliente::class);

        return view_do_tema('parceiros._form', [
            'tipo' => 'clientes',
            'titulo' => 'Novo cliente',
            'registro' => new Cliente(),
            'comEnderecoEContato' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Cliente::class);

        $dados = $this->validarDados($request);

        Cliente::create($dados);

        return redirect(rota_tema('parceiros.clientes.index'))->with('status', 'Cliente criado.');
    }

    public function edit(Cliente $cliente): View
    {
        Gate::authorize('update', $cliente);

        return view_do_tema('parceiros._form', [
            'tipo' => 'clientes',
            'titulo' => 'Editar cliente',
            'registro' => $cliente,
            'comEnderecoEContato' => false,
        ]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        Gate::authorize('update', $cliente);

        $dados = $this->validarDados($request);

        $cliente->update($dados);

        return redirect(rota_tema('parceiros.clientes.index'))->with('status', 'Cliente atualizado.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        Gate::authorize('delete', $cliente);

        $cliente->delete();

        return redirect(rota_tema('parceiros.clientes.index'))->with('status', 'Cliente removido.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validarDados(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'representante' => ['nullable', 'string', 'max:255'],
            'cpf_cnpj' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:32'],
            'telefone2' => ['nullable', 'string', 'max:32'],
            'cep' => ['nullable', 'string', 'max:16'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:32'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', Rule::in(array_column(Uf::cases(), 'value'))],
            'observacao' => ['nullable', 'string'],
        ]);
    }
}
