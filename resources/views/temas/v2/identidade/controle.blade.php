@extends('temas.v2.layout')

{{-- PAR15-AUD-001/005 - hub Controle do TEMA V2. Equivale a
`15.8.1/page/controle.php` sem `subp`: menu/breadcrumb historico + conteudo de "Logs
de autenticacao". --}}
@section('conteudo')
    @include('temas.v2.identidade._menu_controle', ['subpAtual' => $subpAtual ?? 'logs_de_autenticacao'])

    <div class="boxtop-subpage">
        <h3 class="box-subpage fl">Controle</h3>
        <div style="clear:both;"></div>
    </div>

    @include('temas.v2.identidade.historico-de-acesso._conteudo_legado')
@endsection
