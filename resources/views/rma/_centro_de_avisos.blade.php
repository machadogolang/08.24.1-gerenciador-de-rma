{{--
    "CENTRO DE AVISOS E RELATORIOS" — correção de fidelidade Fase 8 (2026-08-25).
    Fonte real confirmada por captura autenticada de `http://localhost:8094/15.8.1/` e
    `http://localhost:8094/14.6.1/` (as 10 regras da Fase 5, mesma composição de
    `PainelDeAlertasController::index()`, `$grupos` no mesmo formato
    `titulo => Collection<Rma>`). Compartilhado pelos DOIS temas (elemento idêntico nos
    dois, só a folha de estilo em volta muda) — por isso vive em `resources/views/rma/`,
    não em `temas/{v1,v2}/`. Ícones vendorizados de `legacy-source/images/` (mesmos
    bytes do legado: `lembrete.png`, `retornou.png`, `separador.png`).
--}}
<div class="centro-de-avisos">
    <img class="centro-de-avisos-icone" src="{{ asset('images/rma/lembrete.png') }}" alt="Lembrete" width="40">
    <h5 class="centro-de-avisos-titulo">CENTRO DE AVISOS E RELATORIOS</h5>
    <div class="both"></div>
    <hr>
</div>

@foreach ($grupos as $titulo => $rmas)
    @php($alvo = 'centro-de-avisos-dados-' . $loop->index)
    <div class="regra-de-alerta">
        <div class="regra-de-alerta-cabecalho">
            <img src="{{ asset('images/rma/retornou.png') }}" alt="" width="20" height="20">
            <span class="regra-de-alerta-titulo">{{ Illuminate\Support\Str::upper($titulo) }}:</span>
        </div>
        <span class="pmo" data-pmo-alvo="#{{ $alvo }}">Mostrar</span>
        <div style="display:none;" id="{{ $alvo }}" class="regra-de-alerta-dados">
            @if ($rmas->isEmpty())
                <p class="nenhumencontrado">Nenhum item foi encontrado</p>
            @else
                <ul>
                    @foreach ($rmas as $registro)
                        <li>
                            <a href="{{ rota_tema('rmas.show', ['rma' => $registro->id]) }}">
                                #{{ $registro->id }} — {{ $registro->descricao }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    <img src="{{ asset('images/rma/separador.png') }}" alt="Separador" class="separador-alerta">
@endforeach
