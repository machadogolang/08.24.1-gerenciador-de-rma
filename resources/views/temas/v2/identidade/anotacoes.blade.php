@extends('temas.v2.layout')

@section('conteudo')
    {{-- PAR-RES-E-04 - "QUADRO DE ANOTACOES" como pagina propria do TEMA V2 (fonte
    Legacy `15.8.1/page/anotacoes.php`). Diferenca consciente de seguranca: o legado
    salvava a cada `onkeyup` via AJAX proprio, sem CSRF; aqui usamos o endpoint
    moderno `identidade.perfil.anotacao.update` (POST + CSRF + validacao + Policy de
    autenticacao), explicito por botao. Nao altera V1 nem V3. --}}
    <div class="boxtop-subpage">
        <h3 class="box-subpage" style="font-size:16px;padding:5px;">QUADRO DE ANOTACOES</h3>
        <div style="clear:both;"></div>
    </div>

    <div class="row">
        <div class="col-md-12" style="margin:0;padding:0;">
            @if (session('status'))
                <p class="centrodeavisos">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <ul class="text-danger">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('identidade.perfil.anotacao.update') }}">
                @csrf
                @method('PUT')
                <textarea class="anotacao" rows="30" id="anotacao" name="anotacao"
                    data-anotacao-autosave
                    data-anotacao-url="{{ route('identidade.perfil.anotacao.update') }}">{{ old('anotacao', $usuario->anotacao) }}</textarea>
                <div style="clear:both; margin-top:8px;"></div>
                <p id="status-autosave" style="float:left;color:#999;font-size:11px;margin:5px 0 0 0;"></p>
                <button type="submit" class="btn btn-default formSubmit" style="float:right;">Salvar anotacao</button>
            </form>
        </div>
    </div>
@endsection
