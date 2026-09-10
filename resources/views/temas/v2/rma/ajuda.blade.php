@extends('temas.v2.layout')

{{-- UF-18 (GAP-V2-05 / CAP-AUX-001) - Central de Ajuda / Procedimento Operacional V2. --}}
@section('conteudo')
<div class="well well-sm" style="background:#fff;border-radius:4px;color:#333;margin-top:15px;padding:20px;">
    <div style="display:flex;align-items:center;margin-bottom:15px;border-bottom:1px solid #ddd;padding-bottom:10px;">
        <img src="{{ asset('images/tema-v2/lembrete.png') }}" alt="" style="margin-right:12px;" width="32" height="32">
        <h2 style="margin:0;font-size:20px;color:#2c3e50;">Procedimento Operacional de RMA</h2>
    </div>

    <div style="font-size:13px;line-height:1.6;color:#444;">
        <p><strong>FERRAMENTA DE RMA - FLUXO OPERACIONAL</strong></p>
        <p>O ciclo operacional do RMA organiza-se em <strong>3 ETAPAS: Entrada, Processamento e Saída</strong>.</p>

        <div class="panel panel-default" style="margin-top:15px;">
            <div class="panel-heading" style="font-weight:bold;background:#f5f5f5;">1. Entrada (Dados do Produto)</div>
            <div class="panel-body">
                Uma nova solicitação de RMA é cadastrada no sistema. O item permanece na fila de <strong>Entrada</strong> até que o setor responsável identifique fisicamente o produto e assinale como <strong>Recebido</strong>.
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading" style="font-weight:bold;background:#f5f5f5;">2. Processamento (Recebido e Encaminhamento)</div>
            <div class="panel-body">
                Com o produto em mãos e identificado, verifica-se a nota fiscal e o destinatário (fabricante, fornecedor ou assistência técnica). Entra-se em contato com o destinatário para obter autorizações e emitir a nota fiscal de remessa. Após autorizada a remessa, o RMA é assinalado como <strong>Encaminhado</strong> e aguarda o reparo.
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading" style="font-weight:bold;background:#f5f5f5;">3. Saída (Conclusão e Retorno)</div>
            <div class="panel-body">
                Quando o produto reparado retorna ao setor de RMA, a solicitação é marcada como <strong>Concluído</strong>. O produto pode então retornar à sua origem (cliente ou retorno ao estoque).
            </div>
        </div>

        <p style="margin-top:15px;font-style:italic;color:#666;">
            Importante acompanhar o produto no Smallcommerce para conferência de estoque e notas fiscais de remessa e retorno.
        </p>
    </div>
</div>
@endsection
