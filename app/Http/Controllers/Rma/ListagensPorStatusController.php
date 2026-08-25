<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Fabricante;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\ListarRmasDoPainel;
use App\Rma\Dominio\PainelDeStatus;
use App\Rma\Dominio\Rma;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;

/**
 * VIS-V1-001 — as 4 páginas de listagem por status do menu superior do TEMA V1
 * (`Entrada / Encaminhado / Aguardando credito / Concluido`, fonte real
 * `legacy-source/14.6.1/page/{entrada,encaminhados,aguardandocredito,concluidos}.php`).
 * View exclusiva do TEMA V1 (o header do TEMA V2 não tem esses atalhos — ver achado
 * VIS-V1-001 em `docs/produto/checklist-paridade-visual-v1-runtime.md`), por isso
 * renderiza direto `temas.v1.rma.*` em vez de `view_do_tema()`.
 */
class ListagensPorStatusController extends Controller
{
    public function __construct(
        private readonly ListarRmasDoPainel $caso,
    ) {}

    public function entrada(): View
    {
        return $this->render(PainelDeStatus::Entrada, 'Entrada');
    }

    public function encaminhados(): View
    {
        return $this->render(PainelDeStatus::Encaminhados, 'Encaminhado');
    }

    public function aguardandoCredito(): View
    {
        return $this->render(PainelDeStatus::AguardandoCredito, 'Aguardando credito');
    }

    public function concluidos(): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $registros = $this->caso->listar(PainelDeStatus::Concluidos);

        return view('temas.v1.rma.concluidos', [
            'titulo' => 'Concluido',
            'registros' => $registros,
            'descricao' => PainelDeStatus::Concluidos->descricao(),
            'fabricantes' => $this->mapaDeFabricantes($registros),
            'destinatarios' => $this->mapaDeDestinatarios($registros),
            'resumo' => $this->resumoDeConcluidos($registros),
        ]);
    }

    /**
     * VIS-V1-001/CP4 — fonte real `legacy-source/14.6.1/page/concluidos.php:20-27,66-69`:
     * o legado soma `valor` e conta registros durante o mesmo `while` que lista a
     * tabela. Aqui os `$registros` já vêm carregados pelo caso de uso, então a soma é
     * só agregação em memória — sem SQL nem cálculo no Blade.
     *
     * @param  Rma[]  $registros
     * @return array{valorTotal: float, quantidadeTotal: int, quantidadeSemValor: int, dataProcessamento: string}
     */
    private function resumoDeConcluidos(array $registros): array
    {
        return [
            'valorTotal' => array_sum(array_map(fn (Rma $r) => $r->valor, $registros)),
            'quantidadeTotal' => count($registros),
            'quantidadeSemValor' => count(array_filter($registros, fn (Rma $r) => $r->valor == 0)),
            'dataProcessamento' => Date::now()->format('d/m/Y'),
        ];
    }

    private function render(PainelDeStatus $painel, string $titulo): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $registros = $this->caso->listar($painel);

        return view('temas.v1.rma.'.$this->nomeDaView($painel), [
            'titulo' => $titulo,
            'registros' => $registros,
            'descricao' => $painel->descricao(),
            'fabricantes' => $this->mapaDeFabricantes($registros),
            'destinatarios' => $this->mapaDeDestinatarios($registros),
        ]);
    }

    private function nomeDaView(PainelDeStatus $painel): string
    {
        return match ($painel) {
            PainelDeStatus::Entrada => 'entrada',
            PainelDeStatus::Encaminhados => 'encaminhados',
            PainelDeStatus::AguardandoCredito => 'aguardando-credito',
            PainelDeStatus::Concluidos => 'concluidos',
        };
    }

    /**
     * @param  Rma[]  $registros
     * @return array<int, string>
     */
    private function mapaDeFabricantes(array $registros): array
    {
        $ids = array_unique(array_filter(array_map(fn (Rma $r) => $r->fabricanteId, $registros)));

        return $ids === [] ? [] : Fabricante::query()->whereIn('id', $ids)->pluck('nome', 'id')->all();
    }

    /**
     * `destinatarioType`/`destinatarioId` são polimórficos (Fornecedor/AssistenciaTecnica/
     * etc., sem `morphMap` — ver `App\Models\Rma`), então a chave é o par tipo+id.
     *
     * @param  Rma[]  $registros
     * @return array<string, string>
     */
    private function mapaDeDestinatarios(array $registros): array
    {
        $idsPorTipo = [];
        foreach ($registros as $registro) {
            if ($registro->destinatarioType !== null && $registro->destinatarioId !== null) {
                $idsPorTipo[$registro->destinatarioType][] = $registro->destinatarioId;
            }
        }

        $mapa = [];
        foreach ($idsPorTipo as $tipo => $ids) {
            if (! class_exists($tipo)) {
                continue;
            }

            foreach ($tipo::query()->whereIn('id', array_unique($ids))->get(['id', 'nome']) as $model) {
                $mapa[$tipo.'#'.$model->id] = $model->nome;
            }
        }

        return $mapa;
    }
}
