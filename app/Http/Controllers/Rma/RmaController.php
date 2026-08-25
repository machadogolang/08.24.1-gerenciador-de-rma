<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\BuscarRmas;
use App\Rma\Aplicacao\CriarRma;
use App\Rma\Aplicacao\EditarRma;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Dominio\CriterioDeBusca;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RmaController extends Controller
{
    public function index(Request $request, BuscarRmas $caso): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $tipo = $request->query('tipo', 'texto');
        $valor = (string) $request->query('valor', '');

        $criterio = match ($tipo) {
            'serial' => CriterioDeBusca::porSerial($valor),
            'nota_fiscal' => CriterioDeBusca::porNotaFiscal($valor),
            default => CriterioDeBusca::porTexto($valor),
        };

        return view_do_tema('rma.index', [
            'titulo' => 'RMAs',
            'rmas' => $valor !== '' ? $caso->buscar($criterio) : [],
            'tipo' => $tipo,
            'valor' => $valor,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', RmaEloquent::class);

        return view_do_tema('rma.create', [
            'titulo' => 'Novo RMA',
            'fabricantes' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request, CriarRma $caso): RedirectResponse
    {
        Gate::authorize('create', RmaEloquent::class);

        $dados = $this->validarDados($request);

        $rma = $caso->criar($dados);

        return redirect(rota_tema('rmas.show', ['rma' => $rma->id]))->with('status', 'RMA criado.');
    }

    public function show(int $rma, VerDetalheDoRma $caso): View
    {
        Gate::authorize('view', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return view_do_tema('rma.show', [
            'titulo' => 'RMA #' . $registro->id,
            'registro' => $registro,
            'fabricante' => $registro->fabricanteId ? Fabricante::find($registro->fabricanteId) : null,
            'fornecedor' => $registro->fornecedorId ? Fornecedor::find($registro->fornecedorId) : null,
            'cliente' => $registro->clienteId ? Cliente::find($registro->clienteId) : null,
        ]);
    }

    public function edit(int $rma, VerDetalheDoRma $caso): View
    {
        Gate::authorize('update', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return view_do_tema('rma.edit', [
            'titulo' => 'Editar RMA #' . $registro->id,
            'registro' => $registro,
            'fabricantes' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, int $rma, EditarRma $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);

        $dados = $this->validarDados($request);

        $registro = $caso->editar($rma, $dados);

        return redirect(rota_tema('rmas.show', ['rma' => $registro->id]))->with('status', 'RMA atualizado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validarDados(Request $request): array
    {
        $dados = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'fabricante_id' => ['nullable', 'integer', 'exists:fabricantes,id'],
            'fornecedor_id' => ['nullable', 'integer', 'exists:fornecedores,id'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'sn' => ['nullable', 'string', 'max:255'],
            'os' => ['nullable', 'string', 'max:255'],
            'origem' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'cliente_nome' => ['nullable', 'string', 'max:255'],
            'defeito' => ['required', 'string', 'max:255'],
            'observacao' => ['nullable', 'string'],
        ]);

        return $dados;
    }
}
