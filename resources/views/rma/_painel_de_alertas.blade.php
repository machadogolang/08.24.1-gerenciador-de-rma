<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Painel de alertas — CellSystem RMA</title>
</head>
<body>
    <h1>Painel de alertas</h1>

    {{-- View mínima, sem fidelidade visual (cores/CSS por tema fica para a Fase 8). --}}
    @foreach ($grupos as $titulo => $rmas)
        <section>
            <h2>{{ $titulo }} ({{ $rmas->count() }})</h2>
            @if ($rmas->isEmpty())
                <p>Nenhum RMA.</p>
            @else
                <ul>
                    @foreach ($rmas as $registro)
                        <li>
                            <a href="{{ route('rmas.show', $registro->id) }}">
                                #{{ $registro->id }} — {{ $registro->descricao }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
</body>
</html>
