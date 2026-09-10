{{-- PAR15-AUD-004 - Logs de autenticacao do TEMA V2 no contrato do Legacy
`15.8.1/subp/logs_de_autenticacao.php`: DATA, USUARIO, SISTEMA OPERACIONAL,
NAVEGADOR, IP, APP, RETORNO e "Quantidade retornada". SISTEMA OPERACIONAL e APP vem
dos campos historicos migrados (`sistema_operacional_legado`/`app_legado`); eventos
novos mostram o que e deterministicamente conhecido e vazio no resto. A informacao
moderna (e-mail informado) continua disponivel em bloco recolhido. --}}
<ol class="breadcrumb submenutitulo">
    <li class="fl"><img alt="Controle" style="margin-top:-2px;" title="Logs" src="{{ asset('images/rma/notas.png') }}" width="20" height="20"/></li>
    <li class="fl" style="margin-top:0px;">Logs de autenticacao</li>
    <li style="clear:both;"></li>
</ol>

<table class="Tabelinha-Table">
    <thead>
        <tr class="SuperTr">
            <th style="width:8%">DATA</th>
            <th style="width:30%">USUARIO</th>
            <th style="width:18%">SISTEMA OPERACIONAL</th>
            <th style="width:14%">NAVEGADOR</th>
            <th style="width:14%">IP</th>
            <th style="width:8%">APP</th>
            <th style="width:8%">RETORNO</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tentativas as $indice => $tentativa)
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}">
                <td class="Tabelinha-TD"><div>{{ $tentativa->created_at?->format('d/m/Y') }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $tentativa->user?->name ?? $tentativa->email_informado }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $tentativa->sistema_operacional_legado ?? '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $tentativa->user_agent ?? '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $tentativa->ip ?? '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $tentativa->app_legado ?? '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $tentativa->resultado->name }}</div></td>
            </tr>
        @empty
            <tr><td colspan="7" class="Tabelinha-TD">Nenhum item foi encontrado</td></tr>
        @endforelse
    </tbody>
</table>

<p style="padding-top:15px;text-align:right;padding:15px;">Quantidade retornada: {{ $tentativas->count() }}</p>

{{ $tentativas->links() }}

<details class="detalhe-bd-acoes-avancadas">
    <summary>Informacao moderna do log</summary>
    <table class="Tabelinha-Table">
        <thead>
            <tr class="SuperTr">
                <th>DATA</th>
                <th>E-MAIL INFORMADO</th>
                <th>USUARIO</th>
                <th>IP</th>
                <th>RESULTADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tentativas as $tentativa)
                <tr>
                    <td class="Tabelinha-TD">{{ $tentativa->created_at }}</td>
                    <td class="Tabelinha-TD">{{ $tentativa->email_informado }}</td>
                    <td class="Tabelinha-TD">{{ $tentativa->user?->name ?? '-' }}</td>
                    <td class="Tabelinha-TD">{{ $tentativa->ip ?? '-' }}</td>
                    <td class="Tabelinha-TD">{{ $tentativa->resultado->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</details>
