<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Crédito — CellSystem RMA</title>
</head>
<body>
    <h1>Fluxo de crédito</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). Fluxo único de crédito
    (LEG-RMA-036), não as 3 sub-rotas quebradas do legado (LEG-RMA-048). --}}
    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>Aguardando crédito (solução = PENDENTE CREDITO)</h2>
    <p>Controle manual em duas camadas (sem transição automática): primeiro a solução do
    RMA precisa virar "GERADO CREDITO" (tela de detalhe do RMA), depois o crédito é
    marcado disponível abaixo.</p>
    @if ($aguardandoCredito->isEmpty())
        <p>Nenhum RMA.</p>
    @else
        <ul>
            @foreach ($aguardandoCredito as $registro)
                <li>
                    <a href="{{ route('rmas.show', $registro->id) }}">
                        #{{ $registro->id }} — {{ $registro->descricao }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <h2>Marcar crédito disponível</h2>
    <p>Exige solução = "GERADO CREDITO" no RMA informado.</p>
    <form method="POST" action="{{ route('rmas.credito.marcar') }}">
        @csrf
        <label>RMA (id)
            <input type="number" name="rma_id" required>
        </label>
        <button type="submit">Marcar crédito disponível</button>
    </form>
</body>
</html>
