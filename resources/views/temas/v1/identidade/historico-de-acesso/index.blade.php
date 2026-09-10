@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- UF-10 (GAP-V1-03) - Historico de acesso (autenticacao) sob o TEMA V1 (14.6.1).
    Mesma capacidade do V2 (subp/logs_de_autenticacao.php), expressa com a linguagem visual nativa
    do 14.6.1: cabecalho com icone, Tabelinha-Table, linhas zebradas e contagem. --}}
    <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
        <img src="{{ asset('images/rma/notas.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $titulo }}</p>
    <a href="{{ rota_tema('rmas.controle.index') }}" style="float:right;font-size:12px;margin-top:20px;color:#333;text-decoration:none;">&larr; Voltar ao Controle</a>
    <hr class="both">

    <div class="historico-tabela">
        @if ($tentativas->isEmpty())
            <p class="nenhumencontrado">Nenhuma tentativa registrada.</p>
        @else
            <table class="Tabelinha-Table">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th>DATA</th>
                        <th>USUÁRIO</th>
                        <th>E-MAIL INFORMADO</th>
                        <th>SISTEMA OPERACIONAL</th>
                        <th>IP</th>
                        <th>RESULTADO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tentativas as $indice => $tentativa)
                        <tr class="{{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                            <td class="Tabelinha-TD"><div>{{ $tentativa->created_at?->format('d/m/Y H:i:s') }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $tentativa->user?->name ?? '-' }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $tentativa->email_informado }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $tentativa->sistema_operacional_legado ?? ($tentativa->user_agent ? \Illuminate\Support\Str::limit($tentativa->user_agent, 25) : '-') }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $tentativa->ip ?? '-' }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $tentativa->resultado->name }}</div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="padding-top:10px;text-align:right;">Quantidade exibida: {{ $tentativas->count() }}</p>

            {{ $tentativas->links() }}
        @endif
    </div>
@endsection
