<?php

namespace App\Http\Controllers\Parceiros;

use App\Compartilhado\Uf;
use App\Http\Controllers\Controller;
use App\Models\AssistenciaTecnica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssistenciaTecnicaController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', AssistenciaTecnica::class);

        return view_do_tema('parceiros.index', [
            'tipo' => 'assistencias-tecnicas',
            'titulo' => 'Assistências técnicas',
            'registros' => AssistenciaTecnica::query()->orderBy('nome')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', AssistenciaTecnica::class);

        return view_do_tema('parceiros._form', [
            'tipo' => 'assistencias-tecnicas',
            'titulo' => 'Nova assistência técnica',
            'registro' => new AssistenciaTecnica(),
            'comEnderecoEContato' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', AssistenciaTecnica::class);

        $dados = $this->validarDados($request);

        AssistenciaTecnica::create($dados);

        return redirect(rota_tema('parceiros.assistencias-tecnicas.index'))->with('status', 'Assistência técnica criada.');
    }

    public function edit(AssistenciaTecnica $assistenciaTecnica): View
    {
        Gate::authorize('update', $assistenciaTecnica);

        return view_do_tema('parceiros._form', [
            'tipo' => 'assistencias-tecnicas',
            'titulo' => 'Editar assistência técnica',
            'registro' => $assistenciaTecnica,
            'comEnderecoEContato' => true,
        ]);
    }

    public function update(Request $request, AssistenciaTecnica $assistenciaTecnica): RedirectResponse
    {
        Gate::authorize('update', $assistenciaTecnica);

        $dados = $this->validarDados($request);

        $assistenciaTecnica->update($dados);

        return redirect(rota_tema('parceiros.assistencias-tecnicas.index'))->with('status', 'Assistência técnica atualizada.');
    }

    public function destroy(AssistenciaTecnica $assistenciaTecnica): RedirectResponse
    {
        Gate::authorize('delete', $assistenciaTecnica);

        $assistenciaTecnica->delete();

        return redirect(rota_tema('parceiros.assistencias-tecnicas.index'))->with('status', 'Assistência técnica removida.');
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
