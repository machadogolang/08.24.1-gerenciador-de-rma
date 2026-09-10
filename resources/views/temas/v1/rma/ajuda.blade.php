@extends('temas.v1.layout')

{{-- UF-18 (GAP-V2-05 / CAP-AUX-001) - Central de Ajuda / Procedimento Operacional V1. --}}
@section('conteudo')
<div>
    <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
        <img src="{{ asset('images/rma/lembrete.png') }}" alt="" width="50" height="50">
    </p>
    <h1 class="title-comicone fl" style="font-size:18px;">Central de Ajuda</h1>
    <hr class="both">

    <div style="font-size:12px;color:#fff;line-height:1.5;">
        <p class="title-comicone" style="font-size:14px;color:#fff;">Olá {{ auth()->user()?->name }}, você está na Central de Ajuda</p>
        <p>Nome da ferramenta: <strong>FERRAMENTA INTRANET DE RMA</strong></p>
        <p><strong>Versão:</strong> 14.6.1</p>
        <br>
        <p><strong>3 ETAPAS: Entrada, Processamento e Saída</strong></p>
        <br>
        <p><strong>Entrada:</strong> Dados do produto</p>
        <p><strong>Processamento:</strong> Encaminhamento do produto</p>
        <p><strong>Saída:</strong> O produto reparado</p>
        <br>
        <p>Todo o processo é feito pelo próprio responsável pelos RMAs, desde
            adicionar uma nova solicitação para ele próprio. Vamos entender então:
            primeiro é adicionada uma nova solicitação de RMA, este vai para a
            ENTRADA e fica lá até que o setor de RMA assinale como RECEBIDO.</p>
        <br>
        <p>Para o RMA ser recebido, precisa estar com o produto em mãos e
            identificado. Agora é identificada a nota fiscal e para quem vai
            ENCAMINHAR (destinatário), assim é inserido os dados necessários para
            fazer o ENCAMINHAMENTO.</p>
        <br>
        <p>Para sair do RECEBIDO é necessário ter entrado em contato com o
            DESTINATÁRIO, recebido formulários e informações do outro lado, para
            então enviar novamente as informações e aguardar receber a autorização,
            para logo fazer a nota fiscal de remessa e ENCAMINHAR ao setor essa NF
            de remessa e aguardar AUTORIZAÇÃO da NF de remessa - se está correta -
            para então encaminhar este produto ao setor de solução.</p>
        <br>
        <p>Quando encaminhado para o DESTINATÁRIO, a solicitação vai para os
            ENCAMINHADOS e só sai de lá quando o produto retornar ao nosso SETOR DE
            RMA.</p>
        <br>
        <p>Então, quando ele retornar ao setor de RMA, ele será assinalado como
            CONCLUIDO, e nessa hora o produto pode retornar para a sua ORIGEM, como
            por exemplo o CLIENTE ou ESTOQUE.</p>
        <br>
        <p>Importante acompanhar o produto no Smallcomerce para que, se retirado -1
            do estoque, seja feito o retorno lançando o produto no Smallcomerce com
            a NF de RETORNO.</p>
    </div>
</div>
@endsection
