{{-- FRONT-003 (H-013) — conteúdo funcional compartilhado do fluxo de crédito
(LEG-RMA-036). Os wrappers de tema (`temas/v{1,2}/rma/credito/index.blade.php`)
fornecem shell/navegação; este partial não contém regra de negócio. --}}

@if (session('status'))
    <p class="status-v3">{{ session('status') }}</p>
@endif

<h2>Fluxo de crédito</h2>
<p>Controle manual em duas camadas (sem transição automática): primeiro a solução do
RMA precisa virar "GERADO CREDITO" (tela de detalhe do RMA), depois o crédito é
marcado disponível abaixo.</p>

<h2>Aguardando crédito (solução = PENDENTE CREDITO)</h2>
@if ($aguardandoCredito->isEmpty())
    <p>Nenhum RMA.</p>
@else
    <ul>
        @foreach ($aguardandoCredito as $registro)
            <li>
                <a href="{{ route('rmas.show', $registro->id) }}">
                    #{{ $registro->id }} - {{ $registro->descricao }}
                </a>
            </li>
        @endforeach
    </ul>
@endif

<h2>Marcar crédito disponível</h2>
<p>Exige solução = "GERADO CREDITO" no RMA informado.</p>
<form method="POST" action="{{ route('rmas.credito.marcar') }}">
    @csrf
    <label>RMA
        <input type="number" name="rma_id" required>
    </label>
    <button type="submit" class="acao acao--primaria">Marcar crédito disponível</button>
</form>
