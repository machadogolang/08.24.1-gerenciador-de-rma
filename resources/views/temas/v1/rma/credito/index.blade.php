@extends('temas.v1.layout')

{{-- PAR14-CREDIT-001 - o credito do TEMA V1 NAO e uma tabela: a fonte
`14.6.1/menujs-right/creditos.php` e um painel de Relatorios que aponta para o RCD
(`index.php?page=relatorios&id=RCRD`). Nao compartilhar o markup do credito V2.
A linha de destinatarios fixos do Legacy (`Tatiane, Guilherme`) e dado hardcoded de
uma instalacao antiga - nao e regra e nao foi reproduzida. --}}
@section('conteudo')
    <div>
        <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
            <img src="{{ asset('images/rma/relatorios.png') }}" alt="" width="50" height="50">
        </p>
        <h1 class="title-comicone fl" style="font-size:18px;">Relatorios</h1>
        <hr class="both">
        <div>
            <a href="{{ route('rmas.relatorios.rcd') }}">
                <p class="TitleRel"><strong>RCD</strong> - RELATORIO DE CREDITOS DISPONIVEIS</p>
            </a>
            <p><span style="color:gold;margin-left:10px;">Credito disponivel</span></p>
        </div>
        <hr style="height:1px;background-color:rgba(255,255,255,0.2);margin-top:5px;">
    </div>
@endsection
