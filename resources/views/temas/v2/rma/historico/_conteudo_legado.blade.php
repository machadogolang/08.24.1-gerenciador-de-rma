{{-- PAR15-AUD-002/003 - logs de modificacao do TEMA V2 no contrato do Legacy
`15.8.1/subp/logs_de_modificacao.php`: DATA, BD NUMERO, FABRICANTE, DESCRICAO,
MODELO, NAVEGADOR e a acao Ver (que no Legacy abria `info/{numero}`, o detalhe do
RMA). As colunas sao PROJECAO de `modificacoes_de_rma.estado_apos` (snapshot) e
`user_agent`; nada e inventado. A informacao moderna (usuario/acao/IP) continua
disponivel em bloco recolhido, sem trocar o contrato principal. --}}
<ol class="breadcrumb submenutitulo">
    <li class="fl"><img alt="Controle" style="margin-top:-2px;" title="Logs" src="{{ asset('images/rma/notas.png') }}" width="20" height="20"/></li>
    <li class="fl" style="margin-top:0px;">Logs de modificacao</li>
    <li style="clear:both;"></li>
</ol>

<table class="Tabelinha-Table">
    <thead>
        <tr class="SuperTr">
            <th style="width:16%">DATA</th>
            <th style="width:16%">BD NUMERO</th>
            <th style="width:14%">FABRICANTE</th>
            <th style="width:18%">DESCRICAO</th>
            <th style="width:18%">MODELO</th>
            <th style="width:14%">NAVEGADOR</th>
            <th style="width:3%"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($modificacoes as $indice => $modificacao)
            @php
                $estado = $modificacao->estado_apos ?? [];
                $fabricanteLog = $estado['fabricante'] ?? $modificacao->rma?->fabricante?->nome ?? '';
                $descricaoLog = $estado['descricao'] ?? $modificacao->rma?->descricao ?? '';
                $modeloLog = $estado['modelo'] ?? $modificacao->rma?->modelo ?? '';
                $numeroLog = $modificacao->rma?->numero_legado ?? $modificacao->rma_id;
            @endphp
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}">
                <td class="Tabelinha-TD"><div>{{ $modificacao->created_at?->format('d/m/Y H:i:s') }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $numeroLog }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $fabricanteLog }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $descricaoLog }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $modeloLog }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $modificacao->user_agent ?? '' }}</div></td>
                <td class="Tabelinha-TD" style="text-align:center;">
                    <a href="{{ rota_tema('rmas.show', ['rma' => $modificacao->rma_id]) }}" title="Ver">
                        <img src="{{ asset('images/rma/ver.png') }}" alt="Ver" height="25">
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="Tabelinha-TD">Nenhum item foi encontrado</td></tr>
        @endforelse
    </tbody>
</table>

{{ $modificacoes->links() }}

<details class="detalhe-bd-acoes-avancadas">
    <summary>Informacao moderna do log</summary>
    <table class="Tabelinha-Table">
        <thead>
            <tr class="SuperTr">
                <th>DATA</th>
                <th>USUARIO</th>
                <th>ACAO</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($modificacoes as $modificacao)
                <tr>
                    <td class="Tabelinha-TD">{{ $modificacao->created_at }}</td>
                    <td class="Tabelinha-TD">{{ $modificacao->user?->name ?? '-' }}</td>
                    <td class="Tabelinha-TD">{{ $modificacao->acao->name }}</td>
                    <td class="Tabelinha-TD">{{ $modificacao->ip ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</details>
