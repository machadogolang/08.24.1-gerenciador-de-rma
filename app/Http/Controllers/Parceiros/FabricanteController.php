<?php

namespace App\Http\Controllers\Parceiros;

use App\Compartilhado\Uf;
use App\Http\Controllers\Controller;
use App\Models\Fabricante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FabricanteController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Fabricante::class);

        return view_do_tema('parceiros.index', [
            'tipo' => 'fabricantes',
            'titulo' => 'Fabricantes',
            'registros' => Fabricante::query()->orderBy('nome')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Fabricante::class);

        return view_do_tema('parceiros._form', [
            'tipo' => 'fabricantes',
            'titulo' => 'Novo fabricante',
            'registro' => new Fabricante(),
            'comEnderecoEContato' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Fabricante::class);

        $dados = $this->validarDados($request);

        Fabricante::create($dados);

        return redirect(rota_tema('parceiros.fabricantes.index'))->with('status', 'Fabricante criado.');
    }

    public function edit(Fabricante $fabricante): View
    {
        Gate::authorize('update', $fabricante);

        return view_do_tema('parceiros._form', [
            'tipo' => 'fabricantes',
            'titulo' => 'Editar fabricante',
            'registro' => $fabricante,
            'comEnderecoEContato' => true,
        ]);
    }

    public function update(Request $request, Fabricante $fabricante): RedirectResponse
    {
        Gate::authorize('update', $fabricante);

        $dados = $this->validarDados($request);

        $fabricante->update($dados);

        return redirect(rota_tema('parceiros.fabricantes.index'))->with('status', 'Fabricante atualizado.');
    }

    public function destroy(Fabricante $fabricante): RedirectResponse
    {
        Gate::authorize('delete', $fabricante);

        $fabricante->delete();

        return redirect(rota_tema('parceiros.fabricantes.index'))->with('status', 'Fabricante removido.');
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
