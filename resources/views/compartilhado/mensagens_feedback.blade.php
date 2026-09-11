@php
    $mensagemSucesso = session('sucesso') ?? session('success') ?? session('status');
    $mensagemErro = session('erro') ?? session('error');
    $mensagemAviso = session('aviso') ?? session('warning');
    $temaAtual = $temaAtivo?->value ?? 'v2';
@endphp

@if ($mensagemSucesso)
    @if ($temaAtual === 'v3')
        <div class="cartao" role="status" style="border-left: 4px solid #177245; background-color: #f4fbf7; margin-bottom: 20px; padding: 12px 16px; color: #177245;">
            {{ $mensagemSucesso }}
        </div>
    @else
        <p class="centrodeavisos" role="status">{{ $mensagemSucesso }}</p>
    @endif
@endif

@if ($mensagemErro)
    @if ($temaAtual === 'v3')
        <div class="cartao" role="alert" style="border-left: 4px solid #b3261e; background-color: #fff8f7; margin-bottom: 20px; padding: 12px 16px; color: #b3261e;">
            {{ $mensagemErro }}
        </div>
    @else
        <p class="centrodeavisos centrodeavisos--erro" role="alert" style="color: #ff9999;">{{ $mensagemErro }}</p>
    @endif
@endif

@if ($mensagemAviso)
    @if ($temaAtual === 'v3')
        <div class="cartao" role="status" style="border-left: 4px solid #b76e00; background-color: #fffbf0; margin-bottom: 20px; padding: 12px 16px; color: #b76e00;">
            {{ $mensagemAviso }}
        </div>
    @else
        <p class="centrodeavisos centrodeavisos--aviso" role="status" style="color: #f6e3a3;">{{ $mensagemAviso }}</p>
    @endif
@endif

@php
    $todosErros = (isset($errors) && $errors->any()) ? $errors->all() : (session('errors')?->all() ?? []);
@endphp

@if (! empty($todosErros))
    @if ($temaAtual === 'v3')
        <div class="cartao" role="alert" style="border-left: 4px solid #b3261e; background-color: #fff8f7; margin-bottom: 20px; padding: 12px 16px; color: #b3261e;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($todosErros as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="centrodeavisos" role="alert" style="color: #ff9999; margin-bottom: 15px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($todosErros as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
