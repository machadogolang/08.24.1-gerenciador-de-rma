<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Alertas\GarantiaFornecedorExpirada;
use App\Rma\Aplicacao\Alertas\GarantiaFornecedorExpirandoEm30Dias;
use App\Rma\Aplicacao\Alertas\NaoVaiDarGarantia;
use App\Rma\Aplicacao\Alertas\NfRetornoPendenteDeLancar;
use App\Rma\Aplicacao\Alertas\PrazoDestinatarioEstourado;
use App\Rma\Aplicacao\Alertas\PrioridadeAltaSemEncaminhar;
use App\Rma\Aplicacao\Alertas\ProtocoloAbertoNaoEncaminhado;
use App\Rma\Aplicacao\Alertas\RecebidosSemEncaminhar30Dias;
use App\Rma\Aplicacao\Alertas\SemNotaFiscal;
use App\Rma\Aplicacao\Alertas\SemNumeroDeSerie;
use App\Rma\Aplicacao\Alertas\UrgenciaPorThreshold;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * Painel de alertas (`LEG-RMA-018` a `029`) — view mínima, sem fidelidade visual
 * (cores/CSS por tema fica para a Fase 8). Cada regra é resolvida como classe própria
 * já injetável (nenhuma delas tem dependência de construtor).
 */
class PainelDeAlertasController extends Controller
{
    public function index(
        RecebidosSemEncaminhar30Dias $recebidosSemEncaminhar30Dias,
        NaoVaiDarGarantia $naoVaiDarGarantia,
        NfRetornoPendenteDeLancar $nfRetornoPendenteDeLancar,
        ProtocoloAbertoNaoEncaminhado $protocoloAbertoNaoEncaminhado,
        GarantiaFornecedorExpirada $garantiaFornecedorExpirada,
        GarantiaFornecedorExpirandoEm30Dias $garantiaFornecedorExpirandoEm30Dias,
        PrazoDestinatarioEstourado $prazoDestinatarioEstourado,
        PrioridadeAltaSemEncaminhar $prioridadeAltaSemEncaminhar,
        SemNotaFiscal $semNotaFiscal,
        SemNumeroDeSerie $semNumeroDeSerie,
        UrgenciaPorThreshold $urgenciaPorThreshold,
    ): View {
        Gate::authorize('viewAny', RmaEloquent::class);

        return view('rma._painel_de_alertas', [
            'grupos' => [
                'Recebidos há mais de 30 dias sem encaminhar' => $recebidosSemEncaminhar30Dias->listar(),
                'Não vai dar garantia' => $naoVaiDarGarantia->listar(),
                'NF de retorno pendente de lançar' => $nfRetornoPendenteDeLancar->listar(),
                'Protocolo aberto não encaminhado' => $protocoloAbertoNaoEncaminhado->listar(),
                'Garantia do fornecedor expirada' => $garantiaFornecedorExpirada->listar(),
                'Garantia do fornecedor expirando em até 30 dias' => $garantiaFornecedorExpirandoEm30Dias->listar(),
                'Prazo do destinatário estourado' => $prazoDestinatarioEstourado->listar(),
                'Prioridade alta sem encaminhar' => $prioridadeAltaSemEncaminhar->listar(),
                'Sem nota fiscal' => $semNotaFiscal->listar(),
                'Sem número de série' => $semNumeroDeSerie->listar(),
                'Urgência por valor (threshold R$75)' => $urgenciaPorThreshold->listar(),
            ],
        ]);
    }
}
