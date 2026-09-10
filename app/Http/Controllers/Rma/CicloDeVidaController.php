<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Aplicacao\ArquivarRma;
use App\Rma\Aplicacao\ConcluirRma;
use App\Rma\Aplicacao\Destinatarios\OpcoesDeDestinatario;
use App\Rma\Aplicacao\EncaminharRma;
use App\Rma\Aplicacao\ReceberRma;
use App\Rma\Aplicacao\RegistrarSolucao;
use App\Rma\Aplicacao\ReverterRmaParaEntrada;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transições de ciclo de vida do RMA (`LEG-RMA-011` a `015`, `LEG-RMA-017`) - ações que
 * gravam campos além do que `RmaController` (CRUD núcleo, Fase 3) cobre. Mesma
 * convenção de parâmetro (id puro), mesma Policy (`RmaPolicy::update` como âncora de
 * autorização de escrita; a regra fina de papel é checada dentro de cada caso de uso).
 */
class CicloDeVidaController extends Controller
{
    public function receber(int $rma, VerDetalheDoRma $buscar, ReceberRma $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);
        $registro = $this->buscarOuFalhar($rma, $buscar);
        $caso->receber($this->usuario(), $registro);

        return redirect()->route('rmas.show', $rma)->with('status', 'RMA recebido.');
    }

    public function encaminhar(
        Request $request,
        int $rma,
        VerDetalheDoRma $buscar,
        EncaminharRma $caso,
        OpcoesDeDestinatario $opcoesDeDestinatario,
    ): RedirectResponse {
        Gate::authorize('update', RmaEloquent::class);
        $dados = $request->validate([
            'destinatario' => ['required', 'string'],
        ]);
        // UX-003/P7 - selecao validada: o servidor revalida tipo permitido,
        // existencia e tenant; nunca confia no `<select>` do navegador.
        [$destinatarioType, $destinatarioId] = $opcoesDeDestinatario->resolver($dados['destinatario']);
        $registro = $this->buscarOuFalhar($rma, $buscar);
        $caso->encaminhar($this->usuario(), $registro, $destinatarioType, $destinatarioId);

        return redirect()->route('rmas.show', $rma)->with('status', 'RMA encaminhado.');
    }

    public function concluir(Request $request, int $rma, VerDetalheDoRma $buscar, ConcluirRma $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);
        $dados = $request->validate([
            'solucao' => ['required', 'string'],
        ]);
        $registro = $this->buscarOuFalhar($rma, $buscar);
        $caso->concluir($this->usuario(), $registro, Solucao::from($dados['solucao']));

        return redirect()->route('rmas.show', $rma)->with('status', 'RMA concluído.');
    }

    public function arquivar(int $rma, VerDetalheDoRma $buscar, ArquivarRma $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);
        $registro = $this->buscarOuFalhar($rma, $buscar);
        $caso->arquivar($this->usuario(), $registro);

        return redirect()->route('rmas.show', $rma)->with('status', 'RMA arquivado.');
    }

    public function reverter(int $rma, VerDetalheDoRma $buscar, ReverterRmaParaEntrada $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);
        $registro = $this->buscarOuFalhar($rma, $buscar);
        $caso->reverter($this->usuario(), $registro);

        return redirect()->route('rmas.show', $rma)->with('status', 'RMA revertido para Entrada.');
    }

    public function registrarSolucao(Request $request, int $rma, VerDetalheDoRma $buscar, RegistrarSolucao $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);
        $dados = $request->validate([
            'solucao' => ['required', 'string'],
        ]);
        $registro = $this->buscarOuFalhar($rma, $buscar);
        $caso->registrar($this->usuario(), $registro, Solucao::from($dados['solucao']));

        return redirect()->route('rmas.show', $rma)->with('status', 'Solução registrada.');
    }

    private function buscarOuFalhar(int $rma, VerDetalheDoRma $buscar): Rma
    {
        $registro = $buscar->porId($rma);
        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return $registro;
    }

    private function usuario(): User
    {
        /** @var User $usuario */
        $usuario = auth()->user();

        return $usuario;
    }
}
