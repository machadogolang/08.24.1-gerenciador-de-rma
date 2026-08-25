<?php

namespace App\Http\Controllers\Parceiros;

use App\Compartilhado\Uf;
use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FornecedorController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Fornecedor::class);

        return view_do_tema('parceiros.index', [
            'tipo' => 'fornecedores',
            'titulo' => 'Fornecedores',
            'registros' => Fornecedor::query()->orderBy('nome')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Fornecedor::class);

        return view_do_tema('parceiros._form', [
            'tipo' => 'fornecedores',
            'titulo' => 'Novo fornecedor',
            'registro' => new Fornecedor(),
            'comEnderecoEContato' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Fornecedor::class);

        $dados = $this->validarDados($request);

        Fornecedor::create($dados);

        return redirect(rota_tema('parceiros.fornecedores.index'))->with('status', 'Fornecedor criado.');
    }

    public function edit(Fornecedor $fornecedor): View
    {
        Gate::authorize('update', $fornecedor);

        return view_do_tema('parceiros._form', [
            'tipo' => 'fornecedores',
            'titulo' => 'Editar fornecedor',
            'registro' => $fornecedor,
            'comEnderecoEContato' => true,
        ]);
    }

    public function update(Request $request, Fornecedor $fornecedor): RedirectResponse
    {
        Gate::authorize('update', $fornecedor);

        $dados = $this->validarDados($request);

        $fornecedor->update($dados);

        return redirect(rota_tema('parceiros.fornecedores.index'))->with('status', 'Fornecedor atualizado.');
    }

    public function destroy(Fornecedor $fornecedor): RedirectResponse
    {
        Gate::authorize('delete', $fornecedor);

        $fornecedor->delete();

        return redirect(rota_tema('parceiros.fornecedores.index'))->with('status', 'Fornecedor removido.');
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
            'email_secundario' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:32'],
            'telefone2' => ['nullable', 'string', 'max:32'],
            'cep' => ['nullable', 'string', 'max:16'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:32'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', Rule::in(array_column(Uf::cases(), 'value'))],
            'www' => ['nullable', 'string', 'max:255'],
            'frete' => ['nullable', 'string', 'max:255'],
            'cfop' => ['nullable', 'string', 'max:255'],
            'observacao' => ['nullable', 'string'],
            'politica_de_garantia' => ['nullable', 'string'],
        ]);
    }
}
