{{-- Ações de ciclo de vida (Fase 4) - FRONT-003/UI-02B: partial compartilhado por
V1/V2. Carrega só semântica de papel (`.acao--primaria`/`.acao--operacional`); cada
tema estiliza em seu SCSS. Rotas, CSRF, Gates, regras de status e inputs preservados.

UX-003/P7 - o encaminhamento deixou de pedir "tipo + id cru" no navegador: agora é
UMA seleção `destinatario` com `value="tipo:id"`, populada apenas com entidades do
tenant ativo (listas filtradas por `PertenceATenant`). O servidor revalida tudo em
`OpcoesDeDestinatario::resolver()` antes de encaminhar. --}}

<div class="acoes-de-transicao">
    @if ($registro->status->podeReceber())
        <form method="POST" action="{{ route('rmas.receber', $registro->id) }}">
            @csrf
            <button type="submit" class="acao acao--operacional">Receber</button>
        </form>
    @endif

    @if ($registro->status->podeEncaminhar())
        @php
            $gruposDeDestino = [
                'Assistencias tecnicas' => collect($assistenciasTecnicasLista ?? [])
                    ->map(fn ($registroDestino) => ['valor' => 'assistencia_tecnica:' . $registroDestino->id, 'nome' => $registroDestino->nome]),
                'Fabricantes' => collect($fabricantesLista ?? [])
                    ->map(fn ($registroDestino) => ['valor' => 'fabricante:' . $registroDestino->id, 'nome' => $registroDestino->nome]),
                'Fornecedores' => collect($fornecedoresLista ?? [])
                    ->map(fn ($registroDestino) => ['valor' => 'fornecedor:' . $registroDestino->id, 'nome' => $registroDestino->nome]),
            ];
            $slugDestinoAtual = match (true) {
                str_ends_with((string) $registro->destinatarioType, 'AssistenciaTecnica') => 'assistencia_tecnica',
                str_ends_with((string) $registro->destinatarioType, 'Fabricante') => 'fabricante',
                str_ends_with((string) $registro->destinatarioType, 'Fornecedor') => 'fornecedor',
                default => '',
            };
            $destinoSelecionado = $slugDestinoAtual !== '' && $registro->destinatarioId !== null
                ? $slugDestinoAtual . ':' . $registro->destinatarioId
                : '';
        @endphp
        <form method="POST" action="{{ route('rmas.encaminhar', $registro->id) }}">
            @csrf
            <label>Destinatario
                <select name="destinatario" class="formSelect acao-controle-select" required>
                    <option value="">-</option>
                    @foreach ($gruposDeDestino as $rotuloGrupo => $opcoesGrupo)
                        @if ($opcoesGrupo->isNotEmpty())
                            <optgroup label="{{ $rotuloGrupo }}">
                                @foreach ($opcoesGrupo as $opcaoDestino)
                                    <option value="{{ $opcaoDestino['valor'] }}" @selected($destinoSelecionado === $opcaoDestino['valor'])>{{ $opcaoDestino['nome'] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </label>
            <button type="submit" class="acao acao--operacional">Encaminhar</button>
        </form>
    @endif

    @if ($registro->status->podeConcluir())
        <form method="POST" action="{{ route('rmas.concluir', $registro->id) }}">
            @csrf
            <label>Solução
                <select name="solucao" class="formSelect acao-controle-select">
                    @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                        <option value="{{ $solucao->value }}">{{ $solucao->value }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="acao acao--operacional">Concluir</button>
        </form>
    @endif

    @if ($registro->status->podeArquivar())
        <form method="POST" action="{{ route('rmas.arquivar', $registro->id) }}">
            @csrf
            <button type="submit" class="acao acao--operacional">Arquivar</button>
        </form>
    @endif

    @if ($registro->status->podeReverterParaEntrada())
        <form method="POST" action="{{ route('rmas.reverter', $registro->id) }}">
            @csrf
            <button type="submit" class="acao acao--operacional">Reverter para Entrada</button>
        </form>
    @endif

    <form method="POST" action="{{ route('rmas.solucao', $registro->id) }}">
        @csrf
        <label>Registrar solução (a qualquer momento)
            <select name="solucao" class="formSelect acao-controle-select">
                @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                    <option value="{{ $solucao->value }}" @selected($registro->solucao === $solucao)>{{ $solucao->value }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="acao acao--primaria">Salvar solução</button>
    </form>
</div>
